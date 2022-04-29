<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Common channel for live bets/chat/etc. Doesn't require authorization.
 */
Broadcast::channel('Everyone', function() {
    return true;
});

/**
 * Workaround for bi-directional sockets, since Laravel Echo doesn't support it.
 */
Broadcast::channel('Whisper', function() {
    return true;
});

Broadcast::channel('App.User.{id}', function ($user, $id) {
    if($id === 'Guest') return true;
    return auth('sanctum')->guest() ? false : $user->_id === $id;
});

Broadcast::channel('Support.{id}', function ($user, $id) {
    return !auth('sanctum')->guest() && ($user->_id === $id || ($user->access === 'moderator' || $user->access === 'admin'));
});

Broadcast::channel('Support', function($user) {
    return !auth('sanctum')->guest() && ($user->access === 'moderator' || $user->access === 'admin');
});
