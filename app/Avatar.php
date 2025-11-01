<?php

namespace App;

use Illuminate\Support\Str;

class Avatar
{
    public static function showAvatar($user = null): string
    {
        if (!$user) {
            return 'https://ui-avatars.com/api/?name=G&size=128&background=6366f1&color=fff';
        }

        if ($user->avatar && Str::startsWith($user->avatar, 'http')) {
            $avatarUrl = $user->avatar;
        } elseif ($user->avatar) {
            $avatarUrl = asset('storage/' . $user->avatar);
        } else {
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=128&background=6366f1&color=fff';
        }

        return $avatarUrl;
    }
}
