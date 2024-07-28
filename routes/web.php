<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/register');
});

Route::get("/start-chat", [ChatController::class, "createMasterChat"])->middleware(['auth', 'verified'])->name("start_chat");
Route::post("/send-message", [ChatController::class, "sendMessage"])->middleware(['auth', 'verified'])->name("ask_question");
Route::get("/get-chat/{chat_id}", [ChatController::class, "getChatById"])->middleware(['auth', 'verified'])->name("chat_by_id");
Route::get("/sharable/{sharable_id}", [DashboardController::class, "shared"]);
Route::get("/get-chat-by-hash/{hash_id}", [ChatController::class, "getChatByHash"])->name("chat_by_hash");
Route::post("/mark-name-sharable", [ChatController::class, "markNameSharable"])->middleware(['auth', 'verified']);
Route::post("/rename-chat", [ChatController::class, "rename"])->middleware(['auth', 'verified']);
Route::post("/delete-chat", [ChatController::class, "delete"])->middleware(['auth', 'verified']);
Route::post("/dislike", [ChatController::class, "dislikeMessage"])->name("dislike");
Route::post("/retrain", [ChatController::class, "reTrain"])->name("retrain");
Route::post("/reject", [ChatController::class, "reject"])->name("reject");

Route::get("/dashboard", [DashboardController::class, "index"])->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get("/adminview/{id}", [DashboardController::class, "index"])->middleware(['auth', 'verified'])
    ->name('adminview');
    Route::get("/dislike/{id}", [DashboardController::class, "index"])->middleware(['auth', 'verified'])
    ->name('dislike');
/* Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard'); */

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';