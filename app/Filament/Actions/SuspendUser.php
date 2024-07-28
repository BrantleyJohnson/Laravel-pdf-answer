<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class SuspendUser extends Action
{
    public function handle($userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'is_suspended' => true,
        ]);

        // Optionally, you can add some feedback message here
    }
}