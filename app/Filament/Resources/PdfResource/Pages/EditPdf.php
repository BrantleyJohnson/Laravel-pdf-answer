<?php

namespace App\Filament\Resources\PdfResource\Pages;

use App\Filament\Resources\PdfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPdf extends EditRecord
{
    protected static string $resource = PdfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
