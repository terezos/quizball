<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            if ($request->isMethod('post') && $request->has('credential')) {
                return $this->handleOneTapLogin($request->input('credential'));
            }

            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
//                $user->update([
//                    'avatar' => $googleUser->getAvatar(),
//                ]);
            } else {
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                        'role' => UserRole::User,
                        'is_active' => true,
                        'password' => \Hash::make(rand(100000,999999)),
                        'email_verified_at' => now(),
                    ]);
                }
            }

            Auth::login($user, true);

            return redirect()->intended(route('game.lobby'));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Unable to login with Google. Please try again.');
        }
    }

    protected function handleOneTapLogin(string $credential)
    {
        try {
            // Decode the JWT token without verification first to get the payload
            $parts = explode('.', $credential);
            if (count($parts) !== 3) {
                throw new \Exception('Invalid token format');
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            // Verify the token with Google
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $credential,
            ]);

            if ($response->failed() || $response->json('aud') !== config('services.google.client_id')) {
                throw new \Exception('Invalid token');
            }

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $avatar = $payload['picture'] ?? null;

            $user = User::where('google_id', $googleId)->first();

            if ($user) {
                // User exists with this Google ID
            } else {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                        'role' => UserRole::User,
                        'is_active' => true,
                        'password' => \Hash::make(rand(100000, 999999)),
                        'email_verified_at' => now(),
                    ]);
                }
            }

            Auth::login($user, true);

            return redirect()->intended(route('game.lobby'));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Unable to login with Google. Please try again.');
        }
    }
}
