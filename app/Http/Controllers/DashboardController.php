<?php

namespace App\Http\Controllers;

use App\Models\MasterUserChat;
use App\Models\UserChat;
use App\Models\User;
use App\Models\Pdf;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function getInitials($name = "")
    {
        if (empty($name)) {
            $name = auth()->user()->name;
        }
        $words = explode(" ", $name);
        $initials = "";

        foreach ($words as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return $initials;
    }

    private function getChatHistory($fromDate, $toDate)
    {
        $userId = auth()->user()->id;
        if(MasterUserChat::where("user_id", $userId)->first())
            { $master_user_chat_id = MasterUserChat::where("user_id", $userId)->first()->id;
                return UserChat::where("master_user_chat_id", $master_user_chat_id)->whereNotNull("question")->whereBetween("created_at", [$fromDate, $toDate])->get()->toArray();
            }
        else
            return MasterUserChat::where("user_id", $userId)->whereNotNull("chatgpt_id")->whereBetween("created_at", [$fromDate, $toDate])->get()->toArray();
    }

    private function getDaysHistoryData($days)
    {
        $today = Carbon::now();
        $startDateTime = $today->copy()->subDays($days)->toDateString() . " 00:00:00";
        $endDateTime = $today->copy()->subDays(1)->toDateString() . " 23:59:59";
        $data = $this->getChatHistory($startDateTime, $endDateTime);
        return $data;
    }

    private function getExactDayHistoryData($day)
    {
        $today = Carbon::now();
        $startDateTime = $today->copy()->subDays($day)->toDateString() . " 00:00:00";
        $endDateTime = $today->copy()->subDays($day)->toDateString() . " 23:59:59";
        $data = $this->getChatHistory($startDateTime, $endDateTime);
        return $data;
    }

    private function thisYearMonthWiseHistory()
    {
        $hist = [];
        $now = Carbon::now();
        $year = $now->year;
        $currentYear = $now->year;
        while ($currentYear == $year) {
            $previous = $now->subMonth();
            if ($previous->year != $year) {
                break;
            }
            $startDateTime = $previous->copy()->toDateString() . " 00:00:00";
            $endDateTime = $previous->copy()->endOfMonth()->toDateString() . " 23:59:59";
            $data = $this->getChatHistory($startDateTime, $endDateTime);
            if (!empty($data)) {
                $hist[\strtolower($previous->format('F'))] = $data;
            }
        }
        return $hist;
    }

    private function previousYearHistories()
    {
        $hist = [];
        $now = Carbon::now();
        $year = $now->year - 1;
        $currentYear = $year;
        while ($year >= $currentYear - 4) {
            $startDateTime = $year . "-01-01" . " 00:00:00";
            $endDateTime = $year . "-12-31" . " 23:59:59";
            $data = $this->getChatHistory($startDateTime, $endDateTime);
            if (!empty($data)) {
                $hist[$year] = $data;
            }
            $year--;
        }
        return $hist;
    }

    private function createDashBoardData(Request $request)
    {
        $chatHistory = [];
        $data = $this->getExactDayHistoryData(0);
        if (!empty($data)) {
            $chatHistory['today'] = $data;
        }

        $data = $this->getExactDayHistoryData(1);
        if (!empty($data)) {
            $chatHistory['yesterday'] = $data;
        }

        $data = $this->getDaysHistoryData(7);
        if (!empty($data)) {
            $chatHistory['previous_7_day'] = $data;
        }

        $data = $this->getDaysHistoryData(30);
        if (!empty($data)) {
            $chatHistory['previous_30_day'] = $data;
        }
        $tHist = array_merge($chatHistory, $this->thisYearMonthWiseHistory());
        $chatHistory = $tHist;

        $tHist = array_merge($chatHistory, $this->previousYearHistories());
        $chatHistory = $tHist;

        $sections = Section::with(['pdfs' => function($query) {  $query->where('role', 'primary'); }])->get()->toArray();
        if($request->route('id')) {
            $user_id = MasterUserChat::where('id', $request->route('id'))->first()->user_id; $name = User::where('id', $user_id)->first()->name;
        }
        else $name = auth()->user()->name;

        return ['name' => $name, "initials" => $this->getInitials($name), "chat_history" => $chatHistory, "section_details" => $sections];
    }
    public function index(Request $request)
    {  
        return view('dashboard', $this->createDashBoardData($request));
    }

    public function dashboard(Request $request)
    {
        $response = array('message' => '', 'success' => true, 'data' => $this->createDashBoardData());
        return response()->json($response);
    }

    private function sharedChatFromId($sharable_id)
    {
        $chatHistory = [];
        $master_chat = MasterUserChat::with("user")->where("sharable_link", $sharable_id)->first();
        $sections = Section::with(['pdfs' => function($query) {  $query->where('role', 'primary'); }])->get()->toArray();
        if (empty($master_chat)) {
            return null;
        }
        $master_chat = $master_chat->toArray();
        $name = "Guest";
        if ($master_chat['share_name'] == 'yes') {
            $name = $master_chat['user']['name'];
        }

        return ['name' => $name, "initials" => $this->getInitials($name), "chat_history" => $chatHistory, "section_details" => $sections, "sharable_content" => true, "chat_hash" => $master_chat['sharable_link']];
    }

    public function sharedApi($sharable_id)
    {
        $data = $this->sharedChatFromId($sharable_id);
        $response = array('message' => 'No data', 'success' => false, 'data' => []);
        if (empty($data)) {
            return response()->json($response);
        }
        $response = array('message' => '', 'success' => true, 'data' => $data);
        return response()->json($response);
    }

    public function shared($sharable_id)
    {
        $data = $this->sharedChatFromId($sharable_id);
        if (empty($data)) {
            abort(404);
        }

        return view('dashboard', $data);
    }
}