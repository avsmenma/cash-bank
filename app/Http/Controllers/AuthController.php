<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BankTujuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:6|max:8',
        ]);

        $inputUsername = $request->username;
        $password = $request->password;

        // 1. Try standard login with the input as username (works for admin, vendor, and VA bank number)
        if (Auth::attempt(['username' => $inputUsername, 'password' => $password], $request->remember)) {
            return $this->redirectByRole();
        }

        // 2. Fallback: Try login by VA name (e.g. "GUMAS")
        //    Search bank_tujuan where nama_tujuan contains the input name
        $bankTujuan = BankTujuan::where('nama_tujuan', 'LIKE', '%- ' . $inputUsername)
            ->orWhere('nama_tujuan', 'LIKE', '%- ' . strtoupper($inputUsername))
            ->orWhere('nama_tujuan', 'LIKE', '% ' . $inputUsername)
            ->orWhere('nama_tujuan', 'LIKE', '% ' . strtoupper($inputUsername))
            ->first();

        if ($bankTujuan) {
            // Find the VA user linked to this bank_tujuan
            $vaUser = User::where('id_bank_tujuan', $bankTujuan->id_bank_tujuan)
                ->where('role', 'va')
                ->first();

            if ($vaUser && Auth::attempt(['username' => $vaUser->username, 'password' => $password], $request->remember)) {
                return $this->redirectByRole();
            }
        }

        return back()->with('failed', 'Username atau password salah');
    }

    /**
     * Redirect user based on their role after successful login.
     */
    private function redirectByRole()
    {
        $role = Auth::user()->role;

        if ($role === 'va') {
            return redirect('/va/dashboard');
        }

        if ($role === 'vendor') {
            return redirect('/userVendor');
        }

        return redirect('/dashboard-cash-bank');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
