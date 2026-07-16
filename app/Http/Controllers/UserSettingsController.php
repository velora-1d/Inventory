<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserSettingsController extends Controller
{
    /**
     * Show user settings page (profile, password, PIN).
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        return Inertia::render('Settings/UserSettings', [
            'user'          => $user,
            'isAdmin'       => $user->hasRole('Admin'),
            'hasPin'        => (bool) $user->pin,
            'switchedRole'  => session('switched_role'),
        ]);
    }

    /**
     * Update basic profile (name, email).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
        ]);
        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Set or reset PIN (Admin only — requires current password).
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'password'         => 'required|string',
            'pin'              => 'required|regex:/^[0-9]{6}$/|confirmed',
            'pin_confirmation' => 'required',
        ]);

        $user = Auth::user();

        if (! $user->hasRole('Admin')) {
            return back()->with('error', 'Fitur PIN hanya untuk Admin.');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        return back()->with('success', 'PIN berhasil diperbarui.');
    }
}
