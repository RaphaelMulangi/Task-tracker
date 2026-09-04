<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around Meta's WhatsApp Cloud API.
 *
 * Setup: create a Meta app with the WhatsApp product at
 * developers.facebook.com, grab the temporary (or permanent, via a system
 * user) access token and phone number ID from "API Setup", and set
 * WHATSAPP_TOKEN / WHATSAPP_PHONE_NUMBER_ID in .env.
 *
 * Outside a 24-hour window since the recipient last messaged your WhatsApp
 * number, Meta requires a pre-approved message template rather than free
 * text. Set WHATSAPP_TEMPLATE_NAME once you have one approved in Meta
 * Business Manager; until then this sends plain text, which only reaches
 * users inside that 24h window (fine for testing with Meta's sandbox test
 * numbers, which can message freely).
 */
class WhatsAppService
{
    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.token'))
            && filled(config('services.whatsapp.phone_number_id'));
    }

    public function send(string $to, string $message): bool
    {
        if (! $this->isConfigured()) {
            Log::debug('WhatsApp not configured; skipping message.', ['to' => $to]);

            return false;
        }

        $version = config('services.whatsapp.api_version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $templateName = config('services.whatsapp.template_name');

        $payload = $templateName
            ? $this->templatePayload($to, $templateName, $message)
            : $this->textPayload($to, $message);

        try {
            $response = Http::withToken(config('services.whatsapp.token'))
                ->baseUrl("https://graph.facebook.com/{$version}")
                ->post("/{$phoneNumberId}/messages", $payload);

            if ($response->failed()) {
                Log::error('WhatsApp message failed to send.', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp message threw an exception.', [
                'to' => $to,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function textPayload(string $to, string $message): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizeNumber($to),
            'type' => 'text',
            'text' => ['body' => $message],
        ];
    }

    /**
     * Assumes a template with a single body placeholder, e.g.
     * "Reminder: {{1}}". Adjust the components structure to match
     * whatever template you get approved.
     *
     * @return array<string, mixed>
     */
    private function templatePayload(string $to, string $templateName, string $message): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizeNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => config('services.whatsapp.template_language')],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $message],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function normalizeNumber(string $number): string
    {
        return preg_replace('/[^0-9]/', '', $number);
    }
}
