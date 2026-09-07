<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:promote-admin')]
#[Description('Promote the user matching ADMIN_EMAIL to admin, if that env var is set')]
class PromoteAdmin extends Command
{
    public function handle(): void
    {
        $email = env('ADMIN_EMAIL');

        if (! $email) {
            return;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->info("No user found for {$email} yet; skipping.");

            return;
        }

        if ($user->role !== 'admin') {
            $user->update(['role' => 'admin']);
            $this->info("Promoted {$email} to admin.");
        }
    }
}
