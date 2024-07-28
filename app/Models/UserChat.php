<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserChat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'master_user_chat_id',
        'question',
        'answer',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'master_user_chat_id' => 'integer',
    ];

    public function masterUserChat(): BelongsTo
    {
        return $this->belongsTo(MasterUserChat::class);
    }

    public function pdfs(): BelongsToMany
    {
        return $this->belongsToMany(Pdf::class);
    }
    
    public function chatUserDislike(): BelongsTo
    {
        return $this->hasone(ChatUserDislike::class);
    }

    public function pdfUserChats(): BelongsToMany
    {
        return $this->belongsToMany(PdfUserChat::class);
    }
}
