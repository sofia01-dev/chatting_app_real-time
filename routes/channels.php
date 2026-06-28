<?php

use Illuminate\Support\Facades\Broadcast;

// Channel bawaan Laravel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel chat per user — hanya user yang login dengan id yang sama yang boleh subscribe
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
