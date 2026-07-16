<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleSwitchController extends Controller
{
    /**
     * Verify PIN, then impersonate the target user's role.
     * We use session-based role override (not true impersonation).
     */
    public function switch(Request $request)
    {
        $request->validate([
            'role'  => 'required|string|exists:roles,name',
            'pin'   => 'required|string|size:6',
        ]);

        $admin = Auth::user();

        // Ensure current user is admin
        if (! $admin->hasRole('Admin')) {
            return back()->with('error', 'Hanya Admin yang bisa melakukan role switch.');
        }

        // Verify PIN
        if (! $admin->pin || ! Hash::check($request->pin, $admin->pin)) {
            return back()->withErrors(['pin' => 'PIN salah. Silakan coba lagi.']);
        }

        // Store original role in session if not already switching
        if (! session()->has('original_role')) {
            session(['original_role' => $admin->roles->first()?->name]);
        }

        // Override role in session
        session(['switched_role' => $request->role]);

        return back()->with('success', "Tampilan beralih ke role: {$request->role}");
    }

    /**
     * Restore original admin role.
     */
    public function restore(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:6']);

        $admin = Auth::user();

        if (! $admin->pin || ! Hash::check($request->pin, $admin->pin)) {
            return back()->withErrors(['pin' => 'PIN salah. Silakan coba lagi.']);
        }

        session()->forget(['switched_role', 'original_role']);

        return back()->with('success', 'Kembali ke tampilan Admin.');
    }

    /**
     * Set or reset the admin PIN.
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin'              => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'pin_confirmation' => 'required|same:pin',
            'password'         => 'required|string',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        $user->update(['pin' => Hash::make($request->pin)]);

        return back()->with('success', 'PIN berhasil diatur.');
    }
}
