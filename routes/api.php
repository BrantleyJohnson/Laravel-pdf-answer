<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(["status" => "ERROR", "message" => "Missing required params ", "data" => []]);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(["status" => "ERROR", "message" => "Invalid credentials", "data" => []]);
    }
    
    $token = $user->createToken("api")->plainTextToken;
    $data = $user->toArray();
    $data['token'] = $token;
    return response()->json(["status" => "SUCCESS", "message" => "Success", "data" => $data]);
});

Route::post('/user-create', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
        "name" => "required"
    ]);

    if ($validator->fails()) {
        return response()->json(["status" => "ERROR", "message" => "Missing required params ", "data" => []]);
    }

    $user = User::where('email', $request->email)->first();

    if (!empty ($user)) {
        return response()->json(["status" => "ERROR", "message" => "Email already taken", "data" => []]);
    }

    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->save();
    $data = $user->toArray();
    return response()->json(["status" => "SUCCESS", "message" => "Success", "data" => $data]);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', function (Request $request) {
        $retJson = ["error" => false, "message" => "Success"];
        $request->user()->currentAccessToken()->delete();
        return response()->json($retJson);
    });

    Route::post('/update-profile', function (Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            "name" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "ERROR", "message" => "Missing required params ", "data" => []]);
        }

        $email = $request->email;
        $name = $request->name;
        $userId = auth()->user()->id;

        $alreadyEmail = User::where("email")->whereNot("id", $userId)->first();
        if (!empty ($alreadyEmail)) {
            return response()->json(["status" => "ERROR", "message" => "Email already taken", "data" => []]);
        }

        $user = User::where('id', $userId)->first();
        $user->name = $name;
        $user->email = $email;
        $user->save();
        $user->tokens()->delete();
        return response()->json(["status" => "SUCCESS", "message" => "Success", "data" => $user->toArray()]);
    });

    Route::post('/update-password', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "ERROR", "message" => "Missing required params ", "data" => []]);
        }

        $current_password = $request->current_password;
        $new_password = $request->new_password;
        $confirm_password = $request->confirm_password;
        $userId = auth()->user()->id;

        if ($confirm_password != $new_password) {
            return response()->json(["status" => "ERROR", "message" => "Confirm password and new password are not same", "data" => []]);
        }

        $user = User::where('id', $userId)->first();
        if(!Hash::check($current_password, $user->password)) {
            return response()->json(["status" => "ERROR", "message" => "Incorrect old password", "data" => []]);
        }

        $user->password = Hash::make($new_password);
        $user->save();
        $user->tokens()->delete();
        
        return response()->json(["status" => "SUCCESS", "message" => "Success", "data" => $user->toArray()]);
    });

    Route::get("/start-chat", [ChatController::class, "createMasterChat"])->name("start_chat");
    Route::post("/send-message", [ChatController::class, "sendMessage"])->name("ask_question");
    Route::get("/get-chat/{chat_id}", [ChatController::class, "getChatById"])->name("chat_by_id");
    Route::get("/sharable/{sharable_id}", [DashboardController::class, "sharedApi"]);
    Route::get("/get-chat-by-hash/{hash_id}", [ChatController::class, "getChatByHash"])->name("chat_by_hash");
    Route::post("/mark-name-sharable", [ChatController::class, "markNameSharable"]);
    Route::post("/rename-chat", [ChatController::class, "rename"]);
    Route::post("/delete-chat", [ChatController::class, "delete"]);
    Route::get('/dashboard', [DashboardController::class, "dashboard"]);
    Route::post("/resend-message", [ChatController::class, "resendMessage"])->name("reask_question");
    Route::get('/sections', [ChatController::class, "getAllSections"]);
    Route::post("/dislike", [ChatController::class, "dislikeMessage"])->name("dislike");
});