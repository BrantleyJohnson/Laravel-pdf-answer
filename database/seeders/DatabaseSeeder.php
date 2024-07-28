<?php

namespace Database\Seeders;

use App\Models\Pdf;
use App\Models\Section;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        try {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@vipulsinghthakur.xyz',
                'password' => 'admin@123'
            ]);
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n\n";
        }

        try {
            $sec = new Section();
            $sec->name = "SBC";
            $sec->save();
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n\n";
        }

        try {
            $pdf = new Pdf();
            $pdf->file = "Sample";
            $pdf->name = "sample.pdf";
            $pdf->section_id = "1";
            $pdf->chatgpt_file_id = "vs_fiMyxXVt6Z1weEdcjGh5P2Y7";
            $pdf->save();
        } catch (\Throwable $e) {
            echo $e->getMessage() . "\n\n";
        }
    }
}