<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Action::make('suspend')
                ->label('Suspend')
                ->action(function (User $record): void {
                    $record->active = 0;
                    $record->save();
                })
                ->requiresConfirmation()
                ->modalHeading('Suspend User')
                ->modalDescription('Are you sure you\'d like to suspend this user?')
                ->modalSubmitActionLabel('Yes, suspend it'),
            Action::make('active')
                ->label('Active')
                ->action(function (User $record): void {
                    $record->active = 1;
                    $record->save();
                })
                ->requiresConfirmation()
                ->modalHeading('Active User')
                ->modalDescription('Are you sure you\'d like to Active this user?')
                ->modalSubmitActionLabel('Yes, Active it'),
            
        ];
    }
}
