<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLoginLog;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    /**
     * Tampilkan halaman setup 2FA (QR Code + Secret Key).
     */
    public function showSetup(Request $request)
    {
        $userId = $request->session()->get('2fa_setup_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        // Jika belum ada secret, generate baru
        if (empty($user->two_factor_secret)) {
            $user->two_factor_secret = TotpService::generateSecret();
            $user->save();
        }

        $qrCodeUrl = TotpService::getQrCodeUrl($user->username, $user->two_factor_secret);
        $secret = $user->two_factor_secret;

        return view('auth.2fa-setup', compact('user', 'secret', 'qrCodeUrl'));
    }

    /**
     * Konfirmasi dan aktifkan 2FA setelah user scan & memasukkan kode valid pertama kali.
     */
    public function confirmSetup(Request $request)
    {
        $userId = $request->session()->get('2fa_setup_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Kode 6 digit wajib diisi.',
            'code.size' => 'Kode harus terdiri dari tepat 6 digit angka.',
        ]);

        $user = User::findOrFail($userId);

        if (!TotpService::verifyCode($user->two_factor_secret, $request->code)) {
            return back()->with('error', 'Kode verifikasi salah atau telah kadaluarsa. Pastikan jam pada smartphone Anda sinkron.');
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('2fa_setup_user_id');

        Auth::login($user);
        $this->recordLogin($request, $user);

        return redirect('/programmer')->with('success', 'Keamanan 2FA Google Authenticator berhasil diaktifkan untuk akun Anda!');
    }

    /**
     * Tampilkan halaman challenge 2FA saat user login.
     */
    public function showChallenge(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        return view('auth.2fa-challenge', compact('user'));
    }

    /**
     * Verifikasi kode 2FA saat login.
     */
    public function verifyChallenge(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Kode 2FA wajib diisi.',
            'code.size' => 'Kode 2FA harus terdiri dari tepat 6 digit angka.',
        ]);

        $user = User::findOrFail($userId);

        if (!TotpService::verifyCode($user->two_factor_secret, $request->code)) {
            return back()->with('error', 'Kode 2FA salah atau telah kadaluarsa. Silakan periksa aplikasi Google Authenticator Anda.');
        }

        $remember = $request->session()->get('2fa_remember', false);
        $request->session()->forget(['2fa_user_id', '2fa_remember']);

        Auth::login($user, $remember);
        $this->recordLogin($request, $user);

        if ($user->role === 'programmer') {
            return redirect('/programmer');
        }

        if ($user->role === 'va') {
            return redirect('/va/dashboard');
        }

        if ($user->role === 'vendor') {
            return redirect('/userVendor');
        }

        return redirect('/dashboard-cash-bank');
    }

    /**
     * Batal verifikasi 2FA dan kembali ke login.
     */
    public function cancel(Request $request)
    {
        $request->session()->forget(['2fa_user_id', '2fa_setup_user_id', '2fa_remember']);
        return redirect()->route('login');
    }

    /**
     * Catat histori login user ke user_login_logs.
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
}
