<?php

namespace App\Filament\Resources\MasterUserChatResource\Pages;

use App\Filament\Resources\MasterUserChatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterUserChat extends EditRecord
{
    protected static string $resource = MasterUserChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
