<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    /**
     * Send SMS notification to a mobile number
     *
     * @param string $mobileNumber
     * @param string $message
     * @return bool
     */
    public function sendSms(string $mobileNumber, string $message): bool
    {
        try {
            // Get SMS provider configuration
            $provider = config('sms.provider', 'twilio');
            $enabled = config('sms.enabled', true);

            // If SMS is disabled, just log and return true (for development)
            if (!$enabled) {
                Log::info('SMS Notification (Disabled):', [
                    'to' => $mobileNumber,
                    'message' => $message
                ]);
                return true;
            }

            // Clean mobile number (remove spaces, dashes, etc.)
            $mobileNumber = preg_replace('/[^0-9+]/', '', $mobileNumber);

            // Send SMS based on provider
            switch ($provider) {
                case 'twilio':
                    return $this->sendViaTwilio($mobileNumber, $message);
                case 'nexmo':
                    return $this->sendViaNexmo($mobileNumber, $message);
                case 'custom':
                    return $this->sendViaCustom($mobileNumber, $message);
                case 'log':
                    // Log mode: Only log, don't actually send (for development/testing)
                    Log::info('SMS Notification (Log Mode - Not Actually Sent):', [
                        'to' => $mobileNumber,
                        'message' => $message,
                        'provider' => $provider,
                        'note' => 'SMS is in LOG mode. Configure a real SMS provider (Twilio/Nexmo/Custom) to actually send messages.'
                    ]);
                    return true;
                default:
                    // Unknown provider: Log and return false
                    Log::warning('SMS Notification Failed - Unknown Provider:', [
                        'to' => $mobileNumber,
                        'message' => $message,
                        'provider' => $provider,
                        'note' => 'Provider not recognized. Use: twilio, nexmo, custom, or log'
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS Send Error: ' . $e->getMessage(), [
                'mobile_number' => $mobileNumber,
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $mobileNumber, string $message): bool
    {
        $accountSid = config('sms.twilio.account_sid');
        $authToken = config('sms.twilio.auth_token');
        $fromNumber = config('sms.twilio.from_number');

        if (!$accountSid || !$authToken || !$fromNumber) {
            Log::warning('Twilio credentials not configured');
            return false;
        }

        try {
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $fromNumber,
                    'To' => $mobileNumber,
                    'Body' => $message
                ]);

            if ($response->successful()) {
                Log::info('SMS sent via Twilio', ['to' => $mobileNumber]);
                return true;
            }

            Log::error('Twilio API Error', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Twilio Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS via Nexmo/Vonage
     */
    private function sendViaNexmo(string $mobileNumber, string $message): bool
    {
        $apiKey = config('sms.nexmo.api_key');
        $apiSecret = config('sms.nexmo.api_secret');
        $fromNumber = config('sms.nexmo.from_number');

        if (!$apiKey || !$apiSecret || !$fromNumber) {
            Log::warning('Nexmo credentials not configured');
            return false;
        }

        try {
            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'from' => $fromNumber,
                'to' => $mobileNumber,
                'text' => $message
            ]);

            if ($response->successful() && $response->json('messages.0.status') === '0') {
                Log::info('SMS sent via Nexmo', ['to' => $mobileNumber]);
                return true;
            }

            Log::error('Nexmo API Error', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Nexmo Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS via Custom API
     */
    private function sendViaCustom(string $mobileNumber, string $message): bool
    {
        $apiUrl = config('sms.custom.api_url');
        $apiKey = config('sms.custom.api_key');

        if (!$apiUrl || !$apiKey) {
            Log::warning('Custom SMS API not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post($apiUrl, [
                'to' => $mobileNumber,
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('SMS sent via Custom API', ['to' => $mobileNumber]);
                return true;
            }

            Log::error('Custom SMS API Error', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Custom SMS Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send login notification SMS
     *
     * @param string $mobileNumber
     * @param string $userName
     * @param string $loginTime
     * @return bool
     */
    public function sendLoginNotification(string $mobileNumber, string $userName, string $loginTime = null): bool
    {
        $loginTime = $loginTime ?? now()->format('Y-m-d H:i:s');
        $message = "Hello {$userName}, you have successfully logged in to the Hotel Management System at {$loginTime}. If this wasn't you, please contact support immediately.";

        return $this->sendSms($mobileNumber, $message);
    }
}
