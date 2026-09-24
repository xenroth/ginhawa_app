<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'email' => ['required', 'email', 'unique:users'], 'password' => ['required', 'confirmed', 'min:8']]); $user = User::create($data); $user->roles()->attach(Role::where('name', 'citizen')->first()); return redirect()->route('login')->with('success', 'Registration received. Council approval is required before posting.'); }
    public function login(Request $request) { $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]); if (!Auth::attempt($credentials)) return back()->withErrors(['email' => 'Credentials rejected.']); $request->session()->regenerate(); return redirect()->intended('/forum'); }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/'); }
}