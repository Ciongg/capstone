<?php

use App\Services\TrustScoreService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('calculates correct percentage for reported responses', function () {
    $trustScoreService = new TrustScoreService();
    
    // Test 30% reported response rate (3 out of 10)
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 3, 10);
    expect($result['percentage'])->toBe(30.0);
    
    // Test 4% reported response rate (4 out of 100)
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 4, 100);
    expect($result['percentage'])->toBe(4.0);
    
    // Test 50% reported response rate (5 out of 10)
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 5, 10);
    expect($result['percentage'])->toBe(50.0);
    
    // Test 0% reported response rate (0 out of 5)
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 0, 5);
    expect($result['percentage'])->toBe(0.0);
});

it('applies correct modifier based on reported response percentage', function () {
    $trustScoreService = new TrustScoreService();
    
    // Test low percentage (< 5%) - should get 0.5 modifier
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 4, 100); // 4%
    expect($result['modifier'])->toBe(0.5);
    expect($result['penalty_amount'])->toBe(-2.5); // -5.0 * 0.5
    
    // Test medium percentage (5-20%) - should get 1.0 modifier
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 10, 100); // 10%
    expect($result['modifier'])->toBe(1.0);
    expect($result['penalty_amount'])->toBe(-5.0); // -5.0 * 1.0
    
    // Test high percentage (> 20%) - should get 1.5 modifier
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 25, 100); // 25%
    expect($result['modifier'])->toBe(1.5);
    expect($result['penalty_amount'])->toBe(-7.5); // -5.0 * 1.5
});

it('respects threshold for reported response penalty application', function () {
    $trustScoreService = new TrustScoreService();
    
    // Below threshold (0, 1, 2) - no penalty
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 0, 5);
    expect($result['threshold_met'])->toBeFalse();
    expect($result['penalty_amount'])->toBe(0);
    
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 1, 5);
    expect($result['threshold_met'])->toBeFalse();
    expect($result['penalty_amount'])->toBe(0);
    
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 2, 5);
    expect($result['threshold_met'])->toBeFalse();
    expect($result['penalty_amount'])->toBe(0);
    
    // Above threshold (3+) - penalty applied
    $result = $trustScoreService->calculateReportedResponseDeduction(1, 3, 10);
    expect($result['threshold_met'])->toBeTrue();
    expect($result['penalty_amount'])->toBeLessThan(0);
});
