<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $username = $this->input('username');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        Log::debug('LoginRequest: Attempting authentication', [
            'username' => $username,
            'remember' => $remember,
        ]);

        if (! Auth::attempt(['username' => $username, 'password' => $password], $remember)) {
            RateLimiter::hit($this->throttleKey());

            Log::warning('LoginRequest: Authentication failed', [
                'username' => $username,
                'reason' => 'Invalid credentials',
            ]);

            throw ValidationException::withMessages([
                'username' => 'Username atau password yang Anda masukkan salah. Silakan coba lagi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Log::info('LoginRequest: Authentication successful', [
            'user_id' => Auth::id(),
            'username' => $username,
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        Log::warning('LoginRequest: Rate limited', [
            'username' => $this->input('username'),
            'seconds_remaining' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'username' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $username = (string) $this->input('username');

        return Str::transliterate(Str::lower($username) . '|' . $this->ip());
    }
}
