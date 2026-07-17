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

    /**
     * Test Database Connection (TiDB Cloud).
     */
    public function testDatabase()
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke TiDB Cloud Database berhasil terhubung!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test S3 / RustFS Connection.
     */
    public function testS3()
    {
        try {
            $disk = \Illuminate\Support\Facades\Storage::disk('s3');
            // Try to write and delete a small temporary file to test read/write permissions
            $filename = 'connection_test_' . uniqid() . '.txt';
            $disk->put($filename, 'test');
            $disk->delete($filename);
            
            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke S3/RustFS Storage berhasil terhubung!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke S3/RustFS: ' . $e->getMessage()
            ], 500);
        }
    }
}
