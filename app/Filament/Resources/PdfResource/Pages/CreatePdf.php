<?php

namespace App\Filament\Resources\PdfResource\Pages;

use App\Filament\Resources\PdfResource;
use App\Models\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Http;
use App\Jobs\UploadFileJob;

class CreatePdf extends CreateRecord
{
    protected static string $resource = PdfResource::class;
    protected function afterCreate(): void
    {
	    try {
		    $data = $this->record;
            $pdfId = $data->id; // Assuming 'id' is the primary key of Pdf model
            $role = $data->role;
            // Dispatch the job to upload the file asynchronously
            $gptId = 0;
            UploadFileJob::dispatch($pdfId, $gptId, $role);
	    } catch (\Throwable $e) {
		    \Log::error($e->getMessage());
	    }
    }
}
