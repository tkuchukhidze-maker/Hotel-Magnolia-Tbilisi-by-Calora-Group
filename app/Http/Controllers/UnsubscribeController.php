<?php

namespace App\Http\Controllers;

use App\Support\GuestOffers;

/** Unsubscribe from offer emails. GET asks, POST does it (so link scanners don't unsubscribe people). */
class UnsubscribeController extends Controller
{
    public function show(string $token)
    {
        $email = GuestOffers::emailFromToken($token);
        abort_unless($email, 404);

        return view('pages.unsubscribe', ['email' => $email, 'token' => $token, 'done' => false]);
    }

    public function store(string $token)
    {
        $email = GuestOffers::emailFromToken($token);
        abort_unless($email, 404);
        GuestOffers::unsubscribe($email);

        return view('pages.unsubscribe', ['email' => $email, 'token' => $token, 'done' => true]);
    }
}
