<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Services\GitHubUpdater;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index() { return view('admin', ['users' => User::with('roles')->latest()->get(), 'posts' => Post::with('author')->latest()->get(), 'metrics' => ['users' => User::count(), 'pendingUsers' => User::where('status', 'pending')->count(), 'pendingPosts' => Post::where('status', 'pending')->count()], 'update' => session('update')]); }
    public function updateUser(Request $request, User $user) { $data = $request->validate(['status' => ['required', 'in:active,suspended,pending'], 'role' => ['required', 'exists:roles,name']]); $user->update(['status' => $data['status'], 'approved_at' => $data['status'] === 'active' ? now() : null]); $user->roles()->sync([Role::where('name', $data['role'])->value('id')]); return back()->with('success', 'Citizen record updated.'); }
    public function updatePost(Request $request, Post $post) { $data = $request->validate(['status' => ['required', 'in:approved,rejected,pending'], 'is_pinned' => ['nullable', 'boolean']]); $post->update(['status' => $data['status'], 'is_pinned' => (bool) ($data['is_pinned'] ?? false)]); return back()->with('success', 'Transmission moderation state updated.'); }
    public function checkUpdate(GitHubUpdater $updater) { try { $release = $updater->latest(); return back()->with('update', $release)->with('success', $release['is_newer'] ? 'A newer Ginhawa release is available.' : 'This installation is up to date.'); } catch (\Throwable $exception) { return back()->with('update_error', $exception->getMessage()); } }
    public function installUpdate(Request $request, GitHubUpdater $updater) { abort_unless($request->user()->hasRole('administrator'), 403); try { $release = $updater->latest(); abort_unless($release['is_newer'], 422, 'No newer release is available.'); $updater->install($release); return back()->with('success', 'Release '.$release['version'].' installed. Clear caches and review migrations before continuing.'); } catch (\Throwable $exception) { return back()->with('update_error', $exception->getMessage()); } }
}