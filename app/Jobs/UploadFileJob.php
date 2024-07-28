<?php

namespace App\Jobs;

use App\Models\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UploadFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $pdfId;
    protected $gptId;
    protected $role;

    public function __construct($pdfId, $gptId, $role)
    {
        $this->pdfId = $pdfId;
        $this->gptId = $gptId;
        $this->role = $role;
    }

    public function handle()
    {
        $gptId = $this->gptId;
        // Retrieve the PDF record
        $pdf = Pdf::find($this->pdfId);

        $role = $this->role;
        
        // Construct the file path
        $filePath = storage_path("app/public/" . $pdf->file);;

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception("File not exists " . $filePath);
        }

        // Make HTTP request to upload the file
        $response = \Http::timeout(100000)->acceptJson()->withHeaders([
            'Content-Type' => 'application/json'
        ])->post(env('CHATGPT_API_GATEWAY') . "upload_file", [
            "file_path" => $filePath, "gpt_id" => $gptId, "role" =>$role
        ])->throw()->json();

        // Update the PDF record with the returned file_id
        $pdf->chatgpt_file_id = $response['file_id'];
        $pdf->save();
    }
}
