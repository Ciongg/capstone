<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key');
    }

    /**
     * Send an OTP email using Brevo API
     *
     * @param string $toEmail
     * @param string $otpCode
     * @return bool
     */
        public function sendOtpEmail(string $toEmail, string $otpCode): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('services.brevo.sender_name', 'Formigo'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => 'Your Email Verification Code',
                'htmlContent' => $this->getOtpEmailTemplate($otpCode),
            ]);

            if (!$response->successful()) {
                Log::error('Brevo API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $toEmail
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Brevo Service Error', [
                'message' => $e->getMessage(),
                'email' => $toEmail
            ]);
            return false;
        }
    }

    /**
     * Send a password reset email using Brevo API
     *
     * @param string $toEmail
     * @param string $otpCode
     * @return bool
     */
    public function sendPasswordResetEmail(string $toEmail, string $otpCode): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('services.brevo.sender_name', 'Formigo'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => 'Password Reset Code',
                'htmlContent' => $this->getPasswordResetEmailTemplate($otpCode),
            ]);

            if (!$response->successful()) {
                Log::error('Brevo API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $toEmail
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Brevo Service Error', [
                'message' => $e->getMessage(),
                'email' => $toEmail
            ]);
            return false;
        }
    }

    /**
     * Send a generic email using Brevo API
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $body (HTML content)
     * @return bool
     */
    public function sendEmail(string $toEmail, string $subject, string $body): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('services.brevo.sender_name', 'Formigo'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => $subject,
                'htmlContent' => $body,
            ]);

            if (!$response->successful()) {
                Log::error('Brevo API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $toEmail
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Brevo Service Error', [
                'message' => $e->getMessage(),
                'email' => $toEmail
            ]);
            return false;
        }
    }

    /**
     * Send a new IP address warning email using Brevo API
     *
     * @param string $toEmail
     * @param string $ipAddress
     * @param array $geoData
     * @return bool
     */
    public function sendNewIpWarning(string $toEmail, string $ipAddress, array $geoData = []): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('services.brevo.sender_name', 'Formigo'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => 'New Login Location Detected',
                'htmlContent' => $this->getNewIpWarningTemplate($ipAddress, $geoData),
            ]);

            if (!$response->successful()) {
                Log::error('Brevo API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $toEmail
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Brevo Service Error', [
                'message' => $e->getMessage(),
                'email' => $toEmail
            ]);
            return false;
        }
    }

    /**
     * Send a lockout warning email using Brevo API
     *
     * @param string $toEmail
     * @param string $ipAddress
     * @param array $geoData
     * @param int $cooldownMinutes
     * @return bool
     */
    public function sendLockoutWarning(string $toEmail, string $ipAddress, array $geoData = [], int $cooldownMinutes = 30): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('services.brevo.sender_name', 'Formigo'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => 'Warning: Failed Login Attempts Detected',
                'htmlContent' => $this->getLockoutWarningTemplate($ipAddress, $geoData, $cooldownMinutes),
            ]);

            if (!$response->successful()) {
                Log::error('Brevo API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'email' => $toEmail
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Brevo Service Error', [
                'message' => $e->getMessage(),
                'email' => $toEmail
            ]);
            return false;
        }
    }

    /**
     * Get the HTML template for OTP email
     */
    private function getOtpEmailTemplate(string $otpCode): string
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 30px; border-radius: 10px; text-align: center;'>
                <h2 style='color: #333; margin-bottom: 20px;'>Email Verification</h2>
                <p style='color: #666; margin-bottom: 30px;'>Please enter the following verification code to complete your registration:</p>
                
                <div style='background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h1 style='color: #1976d2; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 0;'>$otpCode</h1>
                </div>
                
                <p style='color: #666; font-size: 14px;'>This code will expire in 10 minutes.</p>
                <p style='color: #666; font-size: 14px;'>If you didn't request this code, please ignore this email.</p>
            </div>
        </div>
        ";
    }

    /**
     * Get the HTML template for password reset email
     */
    private function getPasswordResetEmailTemplate(string $otpCode): string
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 30px; border-radius: 10px; text-align: center;'>
                <h2 style='color: #333; margin-bottom: 20px;'>Password Reset</h2>
                <p style='color: #666; margin-bottom: 30px;'>Please enter the following verification code to reset your password:</p>
                
                <div style='background-color: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h1 style='color: #1976d2; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 0;'>$otpCode</h1>
                </div>
                
                <p style='color: #666; font-size: 14px;'>This code will expire in 10 minutes.</p>
                <p style='color: #666; font-size: 14px;'>If you didn't request a password reset, please ignore this email.</p>
            </div>
        </div>
        ";
    }

    /**
     * Get the HTML template for new IP warning email
     */
    private function getNewIpWarningTemplate(string $ipAddress, array $geoData): string
    {
        $location = 'Unknown Location';
        if (!empty($geoData)) {
            $parts = [];
            if (!empty($geoData['city'])) $parts[] = $geoData['city'];
            if (!empty($geoData['region'])) $parts[] = $geoData['region'];
            if (!empty($geoData['country'])) $parts[] = $geoData['country'];
            $location = !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 30px; border-radius: 10px;'>
                <h2 style='color: #856404; margin-bottom: 20px;'>⚠️ New Login Location Detected</h2>
                <p style='color: #856404; margin-bottom: 20px;'>We detected a login to your account from a new location:</p>
                
                <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ffc107;'>
                    <p style='margin: 10px 0; color: #333;'><strong>IP Address:</strong> $ipAddress</p>
                    <p style='margin: 10px 0; color: #333;'><strong>Location:</strong> $location</p>
                    <p style='margin: 10px 0; color: #333;'><strong>Time:</strong> " . now()->format('F d, Y h:i A') . "</p>
                </div>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='color: #721c24; margin: 0; font-weight: bold;'>⚠️ If this wasn't you:</p>
                    <p style='color: #721c24; margin: 10px 0 0 0;'>Please change your password immediately to secure your account.</p>
                </div>
                
                <p style='color: #856404; font-size: 14px; margin-top: 20px;'>If this was you, you can safely ignore this email.</p>
                <p style='color: #856404; font-size: 14px;'>For security reasons, we recommend using a strong, unique password and enabling two-factor authentication if available.</p>
            </div>
        </div>
        ";
    }

    /**
     * Get the HTML template for lockout warning email
     */
    private function getLockoutWarningTemplate(string $ipAddress, array $geoData, int $cooldownMinutes): string
    {
        $location = 'Unknown Location';
        if (!empty($geoData)) {
            $parts = [];
            if (!empty($geoData['city'])) $parts[] = $geoData['city'];
            if (!empty($geoData['region'])) $parts[] = $geoData['region'];
            if (!empty($geoData['country'])) $parts[] = $geoData['country'];
            $location = !empty($parts) ? implode(', ', $parts) : 'Unknown Location';
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 30px; border-radius: 10px;'>
                <h2 style='color: #856404; margin-bottom: 20px;'>⚠️ Failed Login Attempts Detected</h2>
                <p style='color: #856404; margin-bottom: 20px; font-weight: bold;'>Someone is attempting to access your account with incorrect credentials.</p>
                
                <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ffc107;'>
                    <p style='margin: 10px 0; color: #333;'><strong>IP Address:</strong> $ipAddress</p>
                    <p style='margin: 10px 0; color: #333;'><strong>Location:</strong> $location</p>
                    <p style='margin: 10px 0; color: #333;'><strong>Time:</strong> " . now()->format('F d, Y h:i A') . "</p>
                    <p style='margin: 10px 0; color: #333;'><strong>Failed Attempts:</strong> Multiple attempts detected</p>
                </div>
                
                <div style='background-color: #e3f2fd; border: 1px solid #2196f3; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='color: #1565c0; margin: 0; font-weight: bold;'>What does this mean?</p>
                    <p style='color: #1565c0; margin: 10px 0 0 0;'>Someone from the above location attempted to log in to your account multiple times with wrong passwords. Your account is still secure and accessible.</p>
                </div>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='color: #721c24; margin: 0; font-weight: bold;'>If this wasn't you:</p>
                    <ul style='color: #721c24; margin: 10px 0 0 20px; padding: 0;'>
                        <li style='margin: 5px 0;'>Change your password immediately</li>
                        <li style='margin: 5px 0;'>Review your account security settings</li>
                        <li style='margin: 5px 0;'>Enable two-factor authentication if available</li>
                        <li style='margin: 5px 0;'>Contact support if you notice any suspicious activity</li>
                    </ul>
                </div>
                
                <p style='color: #856404; font-size: 14px; margin-top: 20px;'>If this was you trying to log in, you can safely ignore this email. Please ensure you're using the correct password.</p>
                <p style='color: #856404; font-size: 14px;'>Your account remains secure and you can continue to log in normally with the correct credentials.</p>
            </div>
        </div>
        ";
    }
}