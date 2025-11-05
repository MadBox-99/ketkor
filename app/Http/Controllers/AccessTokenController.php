<?php

namespace App\Http\Controllers;

use App\Mail\AccessGrantMail;
use App\Models\AccessToken;
use App\Models\Product;
use App\Models\User;
use App\Models\Visible;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccessTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createAccessToken(Product $product)
    {
        config('mail.from.address');
        $admin = User::whereEmail(env('ADMIN_EMAIL'))->first();
        $token = Str::random(40); // Generate a unique token
        $user_id = Auth::user()->id;
        // Store the token in the database
        $accessToken = AccessToken::firstOrCreate([
            'user_id' => $user_id,
            'product_id' => $product->id,
            // Mark the token as not used
        ]);

        $accessToken->update(['token' => $token, 'used' => false]);

        $user = Auth::user();

        Mail::to($admin)->cc($user->email)->send(new AccessGrantMail($token, $user->name));
        Notification::make()
            ->title(__('Succesfuly send an email to administrator who will grant an access to private datas, please wait until is access in grant.'))
            ->success()
            ->send();

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function activateAccessToken($token)
    {
        $accessToken = AccessToken::where('token', $token)->first();
        if (! $accessToken) {
            Notification::make()
                ->title('Access token not found')
                ->danger()
                ->send();

            return redirect()->route('filament.admin.resources.access-tokens.index');
        }
        if (! $accessToken?->used) {
            $accessToken->update(['used' => 1]);
            $visibility = Visible::firstOrNew([
                'product_id' => $accessToken->product_id,
                'user_id' => $accessToken->user_id,
            ]);
            $visibility->update(['isVisible' => 1]);
            Notification::make()
                ->title('access grated to '.$accessToken->user->name)
                ->success()
                ->send();

            return redirect()->route('filament.admin.resources.access-tokens.index');
        }

        return redirect()->route('filament.admin.resources.access-tokens.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccessToken $accessToken): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccessToken $accessToken): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessToken $accessToken): void
    {
        //
    }
}
