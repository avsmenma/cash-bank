<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required|max:8',
        ]);

        if(Auth::attempt($request->only('username','password'), $request->remember)){
            if(Auth::user()->role == 'vendor') return redirect('/userVendor');

          return redirect('/dashboard-cash-bank');
        }

        return back()->with('failed','Username atau password salah');
    }

    public function logout(){
        Auth::logout(Auth::user());
        return redirect('/login');
    }
}
