<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Models\Directive;
use App\Models\Sector;
use App\Models\SiteSetting;
use App\Models\VerificationDocument;
use App\Services\GitHubUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index(Request $request) { try { $userSearch = trim((string) $request->query('user_search', '')); $postSearch = trim((string) $request->query('post_search', '')); $users = User::with('roles')->when($userSearch, fn ($query) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$userSearch}%")->orWhere('email', 'like', "%{$userSearch}%")->orWhere('citizen_number', 'like', "%{$userSearch}%")))->latest()->paginate(10, ['*'], 'users_page')->withQueryString(); $posts = Post::with('author')->when($postSearch, fn ($query) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$postSearch}%")->orWhere('body', 'like', "%{$postSearch}%")->orWhere('sector', 'like', "%{$postSearch}%")))->latest()->paginate(10, ['*'], 'posts_page')->withQueryString(); return view('admin', ['users' => $users, 'posts' => $posts, 'userSearch' => $userSearch, 'postSearch' => $postSearch, 'metrics' => ['users' => User::count(), 'pendingUsers' => User::where('status', 'pending')->count(), 'pendingPosts' => Post::where('status', 'pending')->count()], 'update' => session('update'), 'verifications' => VerificationDocument::with('user')->where('status', 'pending')->latest()->get()]); } catch (\Throwable $exception) { Log::error('Admin dashboard load failed.', ['exception' => $exception]); return back()->withErrors(['system' => 'The command node is temporarily unavailable. Check migrations and database connectivity.']); } }
    public function updateUser(Request $request, User $user) { $data = $request->validate(['status' => ['required', 'in:active,suspended,pending'], 'role' => ['required', 'exists:roles,name'], 'jurisdiction' => ['nullable', 'string', 'max:120'], 'designation' => ['nullable', 'string', 'max:120']]); $user->update(['status' => $data['status'], 'approved_at' => $data['status'] === 'active' ? now() : null, 'jurisdiction' => $data['jurisdiction'], 'designation' => $data['designation']]); $user->roles()->sync([Role::where('name', $data['role'])->value('id')]); return back()->with('success', 'Citizen record updated.'); }
    public function updatePost(Request $request, Post $post) { $data = $request->validate(['status' => ['required', 'in:approved,rejected,pending'], 'is_pinned' => ['nullable', 'boolean']]); try { $post->update(['status' => $data['status'], 'is_pinned' => (bool) ($data['is_pinned'] ?? false)]); return back()->with('success', 'Transmission moderation state updated.'); } catch (\Throwable $exception) { Log::error('Transmission moderation failed.', ['exception' => $exception, 'post_id' => $post->id]); return back()->withErrors(['system' => 'This moderation action could not be completed.']); } }
    public function destroyPost(Post $post) { $post->delete(); return back()->with('success', 'Transmission permanently deleted.'); }
    public function checkUpdate(GitHubUpdater $updater) { try { $release = $updater->latest(); return back()->with('update', $release)->with('success', $release['is_newer'] ? 'A newer Ginhawa release is available.' : 'This installation is up to date.'); } catch (\Throwable $exception) { return back()->with('update_error', $exception->getMessage()); } }
    public function installUpdate(Request $request, GitHubUpdater $updater) { abort_unless($request->user()->hasRole('administrator'), 403); try { $release = $updater->latest(); abort_unless($release['is_newer'], 422, 'No newer release is available.'); $updater->install($release); return back()->with('success', 'Release '.$release['version'].' installed. Clear caches and review migrations before continuing.'); } catch (\Throwable $exception) { return back()->with('update_error', $exception->getMessage()); } }
    public function settings() { return view('admin.settings', ['roles' => Role::latest()->get(), 'sectors' => Sector::orderBy('sort_order')->get(), 'directives' => Directive::orderBy('sort_order')->get(), 'settings' => SiteSetting::pluck('value', 'key'), 'users' => User::orderBy('name')->get()]); }
    public function verifications() { return view('admin.verifications', ['verifications' => VerificationDocument::with('user')->latest()->get()]); }
    public function updateSettings(Request $request) {
        $data = $request->validate([
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:320'],
            'site_name' => ['required', 'string', 'max:100'],
            'site_tagline' => ['required', 'string', 'max:160'],
            'default_jurisdiction' => ['required', 'string', 'max:120'],
            'default_designation' => ['required', 'string', 'max:120'],
            'xrp_address' => ['nullable', 'string', 'max:100'],
            'xrp_memo' => ['nullable', 'string', 'max:100'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg', 'max:1024'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'clearance_rules' => ['nullable', 'string'],
            'custom_widget_enabled' => ['nullable'],
            'custom_widget_badge' => ['nullable', 'string', 'max:60'],
            'custom_widget_title' => ['nullable', 'string', 'max:120'],
            'custom_widget_content' => ['nullable', 'string'],
        ]);
        $data['custom_widget_enabled'] = $request->has('custom_widget_enabled') ? '1' : '0';
        foreach (['favicon' => 'site_favicon', 'logo' => 'site_logo'] as $input => $key) {
            if ($request->hasFile($input)) {
                SiteSetting::updateOrCreate(['key' => $key], ['value' => $request->file($input)->store('branding', 'public'), 'type' => 'file']);
            }
        }
        unset($data['favicon'], $data['logo']);
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) ($value ?? ''), 'type' => 'text']);
        }
        return back()->with('success', 'Global CMS settings updated.');
    }
    public function storeRole(Request $request) { $data = $request->validate(['name' => ['required', 'alpha_dash', 'unique:roles,name'], 'label' => ['required', 'string', 'max:80'], 'clearance' => ['required', 'integer', 'between:1,5'], 'permissions' => ['nullable', 'string']]); $data['permissions'] = collect(explode(',', $data['permissions'] ?? ''))->map(fn ($permission) => trim($permission))->filter()->values()->all(); Role::create($data); return back()->with('success', 'Role created.'); }
    public function storeSector(Request $request) { $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:sectors,name'], 'slug' => ['required', 'alpha_dash', 'unique:sectors,slug'], 'description' => ['nullable', 'string']]); Sector::create($data); return back()->with('success', 'Sector created and available to community transmissions.'); }
    public function destroySector(Sector $sector) { $sector->delete(); return back()->with('success', 'Sector removed from community index.'); }
    public function storeDirective(Request $request) { $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'body' => ['required', 'string'], 'pillar' => ['nullable', 'string', 'max:80']]); Directive::create($data); return back()->with('success', 'Directive created.'); }
    public function destroyDirective(Directive $directive) { $directive->delete(); return back()->with('success', 'Council directive removed.'); }
    public function assignIdentity(Request $request, User $user) { $data = $request->validate(['jurisdiction' => ['required', 'string', 'max:120'], 'designation' => ['required', 'string', 'max:120']]); $user->update($data); return back()->with('success', 'Member identity assignment updated.'); }
    public function searchUsers(Request $request) { return response()->json(User::query()->select(['id', 'name', 'email', 'citizen_number'])->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')->orWhere('citizen_number', 'like', '%'.$request->string('q').'%'))->orderBy('name')->limit(10)->get()); }
    public function reviewVerification(Request $request, VerificationDocument $verification) { $data = $request->validate(['status' => ['required', 'in:approved,rejected'], 'admin_notes' => ['nullable', 'string']]); $verification->update($data); if ($data['status'] === 'approved') $verification->user->update(['status' => 'active', 'approved_at' => now()]); return back()->with('success', 'Verification review saved.'); }
}