<?php

namespace App\Filament\Resources\ChatUserDislikeResource\Pages;

use App\Filament\Resources\ChatUserDislikeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatUserDislike extends EditRecord
{
    protected static string $resource = ChatUserDislikeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
