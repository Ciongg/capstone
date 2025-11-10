<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Illuminate\Support\Facades\Auth;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_redirects_to_google_oauth_provider()
    {
        // Mock Socialite to return a redirect
        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturnSelf();
        
        Socialite::shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/oauth'));

        $response = $this->get(route('google.redirect'));
        
        // Should redirect to Google
        $response->assertRedirect();
    }

    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_in_existing_user_via_google()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('123456789');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $abstractUser->shouldReceive('getName')->andReturn('Existing User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver->stateless->user')->andReturn($abstractUser);

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('feed.index'));
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_new_user_on_consent_submission()
    {
        $response = $this->post(route('google.consent'), [
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'given_name' => 'New',
            'family_name' => 'User',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'New',
            'last_name' => 'User',
        ]);

        $response->assertRedirect(route('feed.index'));
        $this->assertTrue(Auth::check());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_edu_domain_for_researchers()
    {
        Institution::factory()->create([
            'domain' => 'university.edu.ph',
        ]);

        $response = $this->post(route('google.consent'), [
            'email' => 'researcher@university.edu.ph',
            'name' => 'Researcher User',
            'given_name' => 'Researcher',
            'family_name' => 'User',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'researcher@university.edu.ph',
            'type' => 'researcher',
        ]);

        $user = User::where('email', 'researcher@university.edu.ph')->first();
        $this->assertNotNull($user->institution_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_respondent_for_non_edu_email()
    {
        $response = $this->post(route('google.consent'), [
            'email' => 'user@gmail.com',
            'name' => 'Regular User',
            'given_name' => 'Regular',
            'family_name' => 'User',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@gmail.com',
            'type' => 'respondent',
            'institution_id' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_redirects_existing_user_to_feed_when_posting_consent()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('123456789');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $abstractUser->shouldReceive('getName')->andReturn('Existing User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver->stateless->user')->andReturn($abstractUser);

        $response = $this->post(route('google.consent'), [
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'given_name' => 'Existing',
            'family_name' => 'User',
        ]);

        $response->assertRedirect(route('feed.index'));
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_register_page_with_livewire_component()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertViewIs('auth.register');
        $response->assertSeeLivewire('auth.register');
    }
}
