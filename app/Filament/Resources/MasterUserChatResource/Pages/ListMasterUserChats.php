<?php

namespace App\Filament\Resources\MasterUserChatResource\Pages;

use App\Filament\Resources\MasterUserChatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterUserChats extends ListRecords
{
    protected static string $resource = MasterUserChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
