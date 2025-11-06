<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityLogs;
use App\Models\AuditLogs;
use Illuminate\Support\Str;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Security Logs
        $securityEvents = ['login_attempt', 'login_success', 'login_failure', 'access_denied', 'rate_limit_triggered'];
        $outcomes = ['success', 'failure', 'denied'];

        for ($i = 1; $i <= 5; $i++) {
            SecurityLogs::create([
                'uuid'        => Str::uuid(),
                'created_at'  => now()->subMinutes($i * 7),
                'event_type'  => $securityEvents[$i % count($securityEvents)],
                'outcome'     => $outcomes[$i % count($outcomes)],
                'user_id'     => $i % 2 === 0 ? $i : null,
                'actor_role'  => $i % 2 === 0 ? 'super_admin' : 'guest',
                'ip'          => "192.168.1." . ($i + 10),
                'user_agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'route'       => $i % 2 === 0 ? '/admin/login' : '/login',
                'http_method' => $i % 2 === 0 ? 'POST' : 'GET',
                'http_status' => [200, 401, 403, 429][($i - 1) % 4],
                'resource_type' => 'User',
                'resource_id'   => $i,
                'message'     => $i % 2 === 0 ? 'Login successful' : 'Failed login attempt',
                'meta'        => ['attempt_count' => $i, 'mfa_required' => $i % 2 === 0],
                'geo'         => ['country' => 'PH', 'city' => 'Manila'],
                'email'       => $i % 2 === 0 ? "admin{$i}@formigo.com" : "guest{$i}@example.com",
            ]);
        }

        // Seed Audit Logs
        $auditEvents = ['admin_edit_user', 'lock_survey', 'unlock_survey', 'grant_admin_role', 'delete_institution'];

        for ($i = 1; $i <= 5; $i++) {
            AuditLogs::create([
                'created_at'     => now()->subMinutes($i * 11),
                'email'          => "admin{$i}@formigo.com",
                'performed_by'   => $i,
                'performed_role' => $i % 2 === 0 ? 'super_admin' : 'institution_admin',
                'event_type'     => $auditEvents[$i - 1],
                'resource_type'  => $i % 2 === 0 ? 'User' : 'Survey',
                'resource_id'    => $i * 10,
                'before'         => ['status' => $i % 2 === 0 ? 'active' : 'locked'],
                'after'          => ['status' => $i % 2 === 0 ? 'archived' : 'unlocked'],
                'changed_fields' => ['status'],
                'ip'             => "203.0.113." . ($i + 20),
                'user_agent'     => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'message'        => 'Automated audit entry #' . $i,
                'meta'           => ['reason' => 'Seeder sample'],
            ]);
        }
    }
}
