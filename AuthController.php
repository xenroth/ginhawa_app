<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SiteSetting;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'email' => ['required', 'email', 'unique:users'], 'password' => ['required', 'confirmed', 'min:8'], 'phone' => ['nullable', 'string', 'max:30'], 'social_handle' => ['nullable', 'string', 'max:120']]);
        DB::transaction(function () use ($data) {
            do {
                $citizenNumber = 'GHW-'.strtoupper(bin2hex(random_bytes(2))).'-'.strtoupper(bin2hex(random_bytes(2))).'-'.now()->year;
            } while (User::where('citizen_number', $citizenNumber)->exists());
            $user = User::create(array_merge($data, ['citizen_number' => $citizenNumber, 'status' => 'pending', 'jurisdiction' => SiteSetting::value('default_jurisdiction', 'Local Community / Own Country'), 'designation' => SiteSetting::value('default_designation', 'Ginhawa Citizen')]));
            $user->roles()->attach(Role::where('name', 'citizen')->firstOrFail());
        });
        return redirect()->route('login')->with('success', 'Registration received. Your citizen number has been reserved and council approval is required before posting.');
    }
    public function login(Request $request) { $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]); if (!Auth::attempt($credentials)) return back()->withErrors(['email' => 'Credentials rejected.']); $request->session()->regenerate(); return redirect()->intended('/community'); }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/'); }
}