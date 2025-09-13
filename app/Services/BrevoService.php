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
}