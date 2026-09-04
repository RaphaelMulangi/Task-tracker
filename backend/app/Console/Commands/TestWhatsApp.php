<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:test-whatsapp {to} {message=Test message from Task Tracker}')]
#[Description('Send a one-off WhatsApp message to verify your Cloud API credentials')]
class TestWhatsApp extends Command
{
    public function handle(WhatsAppService $whatsApp): int
    {
        if (! $whatsApp->isConfigured()) {
            $this->error('WHATSAPP_TOKEN / WHATSAPP_PHONE_NUMBER_ID are not set in .env yet.');

            return self::FAILURE;
        }

        $to = $this->argument('to');
        $message = $this->argument('message');

        $sent = $whatsApp->send($to, $message);

        if ($sent) {
            $this->info("Sent to {$to}. Check the phone, and storage/logs/laravel.log if it didn't arrive.");

            return self::SUCCESS;
        }

        $this->error('Failed to send — check storage/logs/laravel.log for the API response.');

        return self::FAILURE;
    }
}
