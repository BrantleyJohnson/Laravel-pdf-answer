<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class ChatUserDislike extends Model
{
    use Notifiable;
    protected $guarded = [];

    protected $fillable = [
        'comment',
        'user_chat_id',
        'disliked_by',
    ];

    public function userChat(): BelongsTo
    {
        return $this->belongsTo(UserChat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}