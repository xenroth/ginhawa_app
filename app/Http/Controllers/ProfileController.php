<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request, ?\App\Models\User $user = null) { $profileUser = $user ?: $request->user(); abort_if($profileUser->profile_visibility === 'private' && $profileUser->id !== $request->user()->id, 403); abort_if($profileUser->profile_visibility === 'members' && !$request->user(), 403); return view($user ? 'member' : 'profile', ['user' => $profileUser, 'verification' => $profileUser->verificationDocuments()->latest()->first()]); }
    public function searchMembers(Request $request) { $query = trim((string) $request->query('q', '')); return response()->json(\App\Models\User::query()->select(['id', 'name', 'citizen_number', 'designation', 'jurisdiction'])->where('id', '!=', $request->user()->id)->whereIn('profile_visibility', ['members', 'public'])->when($query, fn ($builder) => $builder->where(fn ($nested) => $nested->where('name', 'like', "%{$query}%")->orWhere('citizen_number', 'like', "%{$query}%")->orWhere('designation', 'like', "%{$query}%")))->orderBy('name')->limit(10)->get()); }
    public function media(Request $request, string $type)
    {
        abort_unless(in_array($type, ['avatar', 'cover'], true), 404);
        $path = $request->user()->{$type.'_path'};
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return response()->file(Storage::disk('public')->path($path));
    }
    public function memberMedia(Request $request, \App\Models\User $user, string $type)
    {
        abort_if($user->profile_visibility === 'private', 403);
        abort_unless(in_array($type, ['avatar', 'cover'], true), 404);
        $path = $user->{$type.'_path'};
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return response()->file(Storage::disk('public')->path($path));
    }
    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'phone' => ['nullable', 'string', 'max:30'], 'social_handle' => ['nullable', 'string', 'max:120'], 'profile_visibility' => ['required', 'in:private,members,public'], 'hide_contact' => ['nullable', 'boolean'], 'avatar' => ['nullable', 'image', 'max:2048'], 'cover' => ['nullable', 'image', 'max:5120']]);
        foreach (['avatar' => 'avatar_path', 'cover' => 'cover_path'] as $input => $column) { if ($request->hasFile($input)) { if ($user->{$column}) Storage::disk('public')->delete($user->{$column}); $data[$column] = $request->file($input)->store('profiles', 'public'); } unset($data[$input]); }
        $data['hide_contact'] = $request->boolean('hide_contact'); $user->update($data);
        return back()->with('success', 'Profile updated.');
    }
    public function submitVerification(Request $request)
    {
        $data = $request->validate(['valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'], 'social_handle' => ['required', 'string', 'max:120'], 'mobile_number' => ['required', 'string', 'max:30']]);
        $request->user()->verificationDocuments()->create(['valid_id_path' => $request->file('valid_id')->store('verification'), 'social_handle' => $data['social_handle'], 'mobile_number' => $data['mobile_number'], 'status' => 'pending']);
        return back()->with('success', 'Verification documents submitted for council review.');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        if (!Hash::check($data['current_password'], $request->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        $request->user()->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function downloadVerification(Request $request, \App\Models\VerificationDocument $verification)
    {
        abort_unless($request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        abort_unless($verification->valid_id_path && Storage::disk('local')->exists($verification->valid_id_path), 404);
        return Storage::disk('local')->download($verification->valid_id_path);
    }
}