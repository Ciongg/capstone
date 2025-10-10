<?php

use App\Services\BrevoService;


it('can send a real email', function () {
    $brevo = new BrevoService();
    $to = 'karl.rj.miguel.inciong@adamson.edu.ph';
    $subject = 'Test Email from BrevoService';
    $body = 'This is a test email sent by the BrevoService integration test.';

    $result = $brevo->sendEmail($to, $subject, $body);

    expect($result)->toBeTrue(); // Or check for a successful response structure
    
})->group('integration');


