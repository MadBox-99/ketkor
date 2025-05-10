<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('index'));
        }

        User::whereEmail('sarkozi.viktor@ketkorkft.hu')->first()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
