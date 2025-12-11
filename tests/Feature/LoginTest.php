<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;

class LoginTest extends TestCase
{
    use WithFaker;

    /**
     * Prepare the test environment.
     *
     * We configure the DB to use sqlite in-memory and run the repository
     * migrations so the tests use the actual `database/migrations` files.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Configure in-memory sqlite for tests
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        // Run migrations from the repository so the schema matches the app
        $this->artisan('migrate:fresh');
    }

    public function test_post_login_with_valid_credentials_redirects_to_dashboard_and_authenticates()
    {
        // Create a user with a known password using the app's factory
        $password = 'password123';

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'username' => 'testuser',
            'password' => Hash::make($password),
        ]);

        // Perform login POST (Inertia/Axios or form should behave the same)
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => $password,
        ]);

        // Assert redirected to dashboard (admin path used by the app)
        $response->assertRedirect('/admin/dashboard');

        // Assert the user is authenticated and matches the created user
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);

        // Session contents are driver/guard specific; `assertAuthenticatedAs`
        // above is sufficient to verify the user is authenticated.
    }

    public function test_post_login_with_invalid_credentials_returns_error_and_does_not_authenticate()
    {
        // Create a user with a known password
        $user = User::factory()->create([
            'email' => 'otheruser@example.com',
            'username' => 'otheruser',
            'password' => Hash::make('correct-password'),
        ]);

        // Attempt login with wrong password, expect to be redirected back to /login
        $response = $this->from('/login')->post('/login', [
            'username' => 'otheruser',
            'password' => 'wrong-password',
        ]);

        // Application should redirect back to the login page on failure
        $response->assertRedirect('/login');

        // Expect validation/auth error in session
        $this->assertTrue(session()->has('errors'), 'Session does not have errors for invalid login');

        // Ensure no user is authenticated
        $this->assertGuest();
    }
}
