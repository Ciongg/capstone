<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Response;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Target user (Miguel Inciong with ID 1)
        $respondentId = 1;
        
        // Get responses by this user
        $responses = Response::where('user_id', $respondentId)->get();
        
        // We'll report about 20% of the responses (10 reports for 50 responses)
        $reportCount = (int)ceil($responses->count() * 0.2);
        
        // Get a reporter (different from the respondent)
        $reporterId = User::where('id', '!=', $respondentId)->value('id') ?? 2;
        
        $reportReasons = [
            'inappropriate_content',
            'spam',
            'offensive',
            'suspicious',
            'duplicate',
            'other'
        ];
        
        // Report random responses
        $reportedResponses = $responses->random($reportCount);
        $reportedCount = 0;
        
        foreach ($reportedResponses as $response) {
            // Calculate trust score deduction (-5 is base)
            $baseDeduction = -5.0;
            $reportPercentage = (++$reportedCount / $responses->count()) * 100;
            
            // Determine modifier based on current percentage
            $modifier = 1.0;
            if ($reportPercentage < 5) {
                $modifier = 0.5;
            } elseif ($reportPercentage > 20) {
                $modifier = 1.5;
            }
            
            $deduction = round($baseDeduction * $modifier, 2);
            
            // Create the report
            Report::create([
                'survey_id' => $response->survey_id,
                'response_id' => $response->id,
                'reporter_id' => $reporterId,
                'respondent_id' => $respondentId,
                'reason' => $reportReasons[array_rand($reportReasons)],
                'details' => 'Test report details for testing trust score deduction calculation',
                'status' => 'unappealed',
                'trust_score_deduction' => $deduction,
                'deduction_reversed' => false,
            ]);
            
            // Mark the response as reported
            $response->update(['reported' => true]);
        }
        
        $this->command->info("Created {$reportedCount} test reports for user ID {$respondentId} (Miguel Inciong)");
    }
}
