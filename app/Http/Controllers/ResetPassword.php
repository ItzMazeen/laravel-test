<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Notifications\ForgotPassword;

class ResetPassword extends Controller
{
    use Notifiable;

    public function show()
    {
        return view('auth.reset-password');
    }

    public function routeNotificationForMail() {
        return request()->email;
    }

public function send(Request $request)
{
    // Validate the email input
    $validatedData = $request->validate([
        'email' => ['required', 'email'] // Ensure the input is a valid email
    ]);

    // Check if the user exists with the given email
    $user = User::where('email', $validatedData['email'])->first();

    if ($user) {
        // Send the password reset notification if the user exists
        $this->notify(new ForgotPassword($user->id));
        return back()->with('succes', 'An email has been sent to your email address');
    }
    // If the user does not exist, return an error message
    return back()->withErrors(['email' => 'No user found with this email address.']);
}

}