<?php
namespace App\Http\Controllers\authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginGoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::error('InvalidStateException: ' . $e->getMessage());
            return redirect()->route('home')->withErrors(['oauth' => 'Invalid state. Please try again.']);
        }

        if (! $googleUser || ! $googleUser->getEmail()) {
            return redirect()->route('home')->withErrors(['oauth' => 'Unable to get Google account.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname(),
                'email' => $googleUser->getEmail(),
                // set a random password — user can reset later
                'password' => bcrypt(uniqid('g_', true)),
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('mswd.dashboard')->with('success', 'Logged in successfully with Google.');
    }
}
