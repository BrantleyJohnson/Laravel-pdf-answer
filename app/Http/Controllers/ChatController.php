<?php

namespace App\Http\Controllers;

use App\Models\ChatUserDislike;
use App\Models\MasterUserChat;
use App\Models\Pdf;
use App\Models\Section;
use App\Models\UserChat;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use App\Jobs\UploadFileJob;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    private function getInitials()
    {
        $words = explode(" ", auth()->user()->name);
        $initials = "";

        foreach ($words as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return $initials;
    }

    private function createChatgptThread()
    {
        // $gptResponse = \Http::acceptJson()->withHeaders([
        //     'X-API-KEY' => env('GATEWAY_API_KEY')
        // ])->post(env('CHATGPT_API_GATEWAY') . env('CREATE_THREAD_API'))->throw()->json();
        return 'thread_id';
    }

    private function chatgptAskQuestion($question, $pdfs)
    {
        $fileIds = "";
        $fileNames = "";
        if (!empty($pdfs)) {
            foreach ($pdfs as $p) {
                $pd = Pdf::find($p);
                $fileIds .= $pd->chatgpt_file_id . ",";
                $fileNames .= $pd->name . ",";
            }
            $sec_pdf = pdf::where("role", "secondary")->get()->toArray();
            foreach ($sec_pdf as $p) {
                $fileNames .= $p['name'] . ",";
            }

            $fileNames = rtrim($fileNames, ",");
            $fileIds = rtrim($fileIds, ",");
        }

        $gptResponse = \Http::timeout(60)->post(env('CHATGPT_API_GATEWAY') . env('ASK_QUESTION_API'), [
                "file_names" => $fileNames,
                "question" => $question,
                "file_ids" => $fileIds
            ])->throw()->json();

        return $gptResponse['answer'];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createMasterChat(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $user_id = auth()->user()->id;
        try {
            $ch = new MasterUserChat();
            $ch->name = $this->getInitials() . time();
            $ch->user_id = $user_id;
            $ch->sharable_link = uniqid($user_id, true);
            $ch->save();
            $response['success'] = true;
            $response['message'] = "Created";
            $response['data'] = $ch->toArray();
        } catch (\Throwable $e) {
            $response["message"] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function markNameSharable(Request $request)
    {
        $response = array('message' => '', 'success' => true, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "chat_id" => "required|exists:master_user_chats,id"
        ]);
        $chat_id = $request->chat_id;
        $markAsShared = $request->share_name;

        $masterChat = MasterUserChat::find($chat_id);
        if ($masterChat->user_id != auth()->user()->id) {
            return;
        }
        $masterChat->share_name = $markAsShared;
        $masterChat->save();
        return response()->json($response);
    }

    public function rename(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "chat_id" => "required|exists:master_user_chats,id",
            "new_name" => "required"
        ]);

        if ($validator->fails()) {
            $response['data'] = $validator->messages();
            $response['message'] = "The chat does not exist. Please initiate new chat.";
            return $response;
        }

        $chat_id = $request->chat_id;
        $new_name = $request->new_name;

        $masterChat = MasterUserChat::find($chat_id);
        if ($masterChat->user_id != auth()->user()->id) {
            return;
        }
        $masterChat->name = $new_name;
        $masterChat->save();
        $response['success'] = true;
        return response()->json($response);
    }

    public function delete(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "chat_id" => "required|exists:master_user_chats,id"
        ]);
        if ($validator->fails()) {
            $response['data'] = $validator->messages();
            $response['message'] = "The chat does not exist. Please initiate new chat.";
            return $response;
        }

        $chat_id = $request->chat_id;
        $archive = $request->archive;
        $masterChat = MasterUserChat::find($chat_id);
        if ($masterChat->user_id != auth()->user()->id) {
            return;
        }
        if (!empty($archive)) {
            $masterChat->is_archive = "yes";
            $masterChat->save();
        }
        $masterChat->delete();
        $response['success'] = true;
        return response()->json($response);
    }


    public function sendMessage(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "master_chat_id" => "required|exists:master_user_chats,id",
            "question" => "required",
            "selected_pdf" => "required"
        ]);

        if ($validator->fails()) {
            $response['data'] = $validator->messages();
            $response['message'] = "The chat does not exist. Please initiate new chat.";
            return $response;
        }
        $master_chat_id = $request->master_chat_id;
        $question = $request->question;
        $pdfs = $request->selected_pdf;

        try {
            $masterChat = MasterUserChat::find($master_chat_id);
            if (empty($masterChat->chatgpt_id)) {
               $masterChat->chatgpt_id = 'thread_'.$master_chat_id;
               $masterChat->save();
            }
            $resp = $this->chatgptAskQuestion($question, $pdfs);

            $userChat = new UserChat();
            $userChat->question = $question;
            $userChat->answer = $resp;
            $userChat->master_user_chat_id = $masterChat->id;
            $userChat->save();

            foreach ($pdfs as $pd) {
                $pd_data = Pdf::find($pd);
                $userChat->pdfs()->save($pd_data);
            }

            $pdf_data = $this->getUserChatById($userChat->id);
            $send_data = new \stdClass;
            if (!empty($pdf_data)) {
                foreach ($pdf_data->pdfs as $v) {
                    if (!isset($send_data->{$v->section->name})) {
                        $send_data->{$v->section->name} = array();
                    }
                    $send_data->{$v->section->name}[] = $v->name;
                }
            }
            $response['success'] = true;
            $response['data'] = $send_data;
            $response['message'] = $resp;
            $response['user_chat_id'] = $userChat->id;
        } catch (\Throwable $e) {
            $response["message"] = $e->getMessage();
            if(strpos($e->getMessage(), "cURL error 7") == 0) 
            $response["message"] = "Service is currently unavailable, please try again later or contact support";
            if(strpos($e->getMessage(), "cURL error 28") == 0) 
            $response["message"] = "Unable to retrieve the information due to extended search time, please retry or provide more details." ;
        }

        return response()->json($response);
    }


    public function resendMessage(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "chat_id" => "required|exists:user_chats,id"
        ]);


        if ($validator->fails()) {
            $response['data'] = $validator->messages();
            $response['message'] = "The chat does not exist. Please initiate new chat.";
            return $response;
        }
        $chat_id = $request->chat_id;
        $userChat = UserChat::with("pdfs")->with("masterUserChat")->find($chat_id);

        try {
            $question = $userChat->question;
            $pdfs = $userChat->pdfs;
            $m_chat_id = $userChat->masterUserChat;
            $resp = $this->chatgptAskQuestion($m_chat_id, $question, $pdfs);


            $pdf_data = $this->getUserChatById($userChat->id);
            $send_data = new \stdClass;
            if (!empty($pdf_data)) {
                foreach ($pdf_data->pdfs as $v) {
                    if (!isset($send_data->{$v->section->name})) {
                        $send_data->{$v->section->name} = array();
                    }
                    $send_data->{$v->section->name}[] = $v->name;
                }
            }
            $response['success'] = true;
            $response['data'] = $send_data;
            $response['message'] = $resp;
        } catch (\Throwable $e) {
            $response["message"] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function getAllSections()
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        try {
            $sections = Section::with("pdfs")->get()->toArray();
            $response['success'] = true;
            $response['data'] = $sections;
            $response['message'] = "Fetched";
        } catch (\Throwable $e) {
            $response['message'] = "Something went wrong";
        }
        return response()->json($response);
    }

    public function dislikeMessage(Request $request)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        $validator = \Validator::make($request->all(), [
            "chat_id" => "required|exists:user_chats,id",
            "comment" => 'required'
        ]);

        if ($validator->fails()) {
            $response['data'] = $validator->messages();
            $response['message'] = "Invalid parameters.";
            return $response;
        }
        $chat_id = $request->chat_id;
        $comment = $request->comment;
        $usr_id = auth()->user()->id;

        try {
            $chatDet = ChatUserDislike::create([
                "user_chat_id" => $chat_id,
                "comment" => $comment,
                "disliked_by" => $usr_id
            ]);
            $response['success'] = true;
            $response['data'] = $chatDet;
            $response['message'] = "Response added";
        } catch (\Throwable $e) {
            $response["message"] = $e->getMessage();
        }

        return response()->json($response);
    }
    /**
     * Display the specified resource.
     */
    public function getChatById(string $chat_id)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        try {
            $userChat = MasterUserChat::with("userChats")->with("userChats.pdfs")->with("userChats.pdfs.section")->where("id", $chat_id)->orderBy("id")->get();
            $response['success'] = true;
            $response['data'] = $userChat->toArray();
            $response['message'] = "Fetched";
        } catch (\Throwable $e) {
            $response['message'] = "Something went wrong";
        }
        return response()->json($response);
    }

    public function getChatByHash(string $hash_id)
    {
        $response = array('message' => '', 'success' => false, 'data' => []);
        try {
            $userChat = MasterUserChat::with("userChats")->with("userChats.pdfs")->with("userChats.pdfs.section")->where("sharable_link", $hash_id)->orderBy("id")->get();
            $response['success'] = true;
            $response['data'] = $userChat->toArray();
            $response['message'] = "Fetched";
        } catch (\Throwable $e) {
            $response['message'] = "Something went wrong";
        }
        return response()->json($response);
    }

    private function getUserChatById(string $chat_id)
    {
        $userChat = new \stdClass;
        try {
            $userChat = UserChat::with("pdfs")->with("pdfs.section")->where("id", $chat_id)->orderBy("id")->first();
        } catch (\Throwable $e) {
        }
        return $userChat;
    }

    public function reTrain(Request $request)
    {
        $pdfs_str = $request->input('pdfs');
        $ss = "";
        $pdfs_array = explode(",", $pdfs_str);
        //return $pdfs_array;
        for($pdf = 0; $pdf < count($pdfs_array); $pdf++)
        {
            if(Pdf::where('name', $pdfs_array[$pdf])->first()){
                $pdfId = Pdf::where('name', $pdfs_array[$pdf])->first()->id;
             //return $pdfId;
             $gptId = Pdf::where('name', $pdfs_array[$pdf])->first()->chatgpt_file_id;
             $role = Pdf::where('name', $pdfs_array[$pdf])->first()->role;
             UploadFileJob::dispatch($pdfId, $gptId, $role);
            }
             
        }
        return $ss;
      
    }

    public function reject(Request $request)
    {
        $id = $request->input('masterId');
        $user_chats = UserChat::where('master_user_chat_id', $id)->get()->toArray();
        for($i = 0; $i < count($user_chats); $i++)
            ChatUserDislike::where('user_chat_id',$user_chats[$i]['id'])->delete();
        
    }


}