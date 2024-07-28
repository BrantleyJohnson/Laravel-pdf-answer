<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfUserChat extends Model
{
    use HasFactory;

    protected $table = 'pdf_user_chat';

    public function pdfs(): BelongsToMany
    {
        return $this->belongsToMany(Pdf::class);
    }

    public function userChats(): HasMany
    {
        return $this->hasMany(UserChat::class);
    }

}
