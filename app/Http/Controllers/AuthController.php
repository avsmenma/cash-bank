<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BankTujuan;
use App\Models\UserLoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $inputUsername = $request->username;
        $password = $request->password;

        // 1. Try standard login with the input as username (works for admin, vendor, and VA bank number)
        if (Auth::attempt(['username' => $inputUsername, 'password' => $password], $request->remember)) {
            return $this->handleAuthenticatedUser($request, Auth::user());
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
                return $this->handleAuthenticatedUser($request, Auth::user());
            }
        }

        return back()->with('failed', 'Username atau password salah');
    }

    /**
     * Tangani flow autentikasi, mengarahkan ke 2FA jika akun programmer atau 2FA aktif.
     */
    private function handleAuthenticatedUser(Request $request, User $user)
    {
        $requires2Fa = ($user->role === 'programmer') || $user->hasTwoFactorEnabled();

        if ($requires2Fa) {
            // Jika 2FA sudah aktif, arahkan ke challenge input kode 6 digit
            if ($user->hasTwoFactorEnabled()) {
                Auth::logout();
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_remember', $request->boolean('remember'));
                return redirect()->route('2fa.challenge');
            }

            // Jika akun programmer belum setup 2FA, wajibkan setup pertama kali
            if ($user->role === 'programmer') {
                Auth::logout();
                $request->session()->put('2fa_setup_user_id', $user->id);
                return redirect()->route('2fa.setup');
            }
        }

        $this->recordLogin($request, $user);
        return $this->redirectByRole();
    }

    /**
     * Catat histori login user untuk pemantauan traffic harian.
     */
    private function recordLogin(Request $request, User $user): void
    {
        try {
            UserLoginLog::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'login_at' => now(),
                'login_date' => now()->toDateString(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Gagal mencatat login log: ' . $e->getMessage());
        }
    }

    /**
     * Redirect user based on their role after successful login.
     */
    private function redirectByRole()
    {
        $role = Auth::user()->role;

        if ($role === 'programmer') {
            return redirect('/programmer');
        }

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
