<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Sector;
use App\Models\User;
use App\Models\Directive;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSchema();
        try {
            $posts = Post::with('author', 'reactions')->visible($request->user())->orderByDesc('is_pinned')->orderByDesc('created_at')->get();
            $sectors = Sector::where('is_active', true)->orderBy('sort_order')->pluck('name')->all();
            $sectors = $sectors ?: ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE'];
            $citizensCount = User::count();
            $onlineCount = 0;
            $sessionDir = storage_path('framework/sessions');
            if (is_dir($sessionDir)) {
                foreach (glob($sessionDir.'/*') ?: [] as $sessionFile) {
                    if (is_file($sessionFile) && (time() - filemtime($sessionFile)) < 900) {
                        $onlineCount++;
                    }
                }
            }
            $directive = Directive::where('is_active', true)->orderBy('sort_order')->latest('id')->first();
            $myReactions = $request->user() ? DB::table('post_reactions')->where('user_id', $request->user()->id)->pluck('value', 'post_id') : collect();
            return view('forum', [
                'posts' => $posts,
                'sectors' => $sectors,
                'citizensCount' => $citizensCount,
                'onlineCount' => $onlineCount,
                'directive' => $directive,
                'myReactions' => $myReactions,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Community transmission load failed.', ['exception' => $exception]);
            return view('forum', ['posts' => collect(), 'sectors' => ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE'], 'citizensCount' => 0, 'onlineCount' => 0, 'directive' => null, 'myReactions' => collect()])->withErrors(['system' => 'The community signal is temporarily unavailable. Please try again after the council restores the database connection.']);
        }
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canPost(), 403, 'Your registration is awaiting approval.');
        $this->ensureSchema();
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string'], 'sector' => ['required', Rule::exists('sectors', 'name')], 'clearance' => ['required', 'in:public,member,enforcer'], 'tags' => ['nullable', 'string'], 'media' => ['nullable', 'array', 'max:6'], 'media.*' => ['file', 'mimes:jpg,jpeg,png,gif', 'max:25600']]);
        $data['user_id'] = $request->user()->id;
        $sectorModel = Sector::where('name', $data['sector'])->first();
        $sectorNeedsApproval = $sectorModel ? (bool) $sectorModel->requires_approval : true;
        $data['status'] = ($request->user()->hasAnyRole(['administrator', 'moderator']) || !$sectorNeedsApproval) ? 'approved' : 'pending';
        $data['tags'] = collect(preg_split('/[\s,]+/', $data['tags'] ?? ''))->map(fn ($tag) => trim($tag))->filter()->unique()->values()->all();
        try {
            $paths = [];
            $dir = public_path('posts');
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            foreach ($request->file('media', []) as $file) {
                $name = uniqid().'.'.$file->getClientOriginalExtension();
                $file->move($dir, $name);
                $paths[] = 'posts/'.$name;
            }
            $data['media'] = $paths;
            Post::create($data);
            return back()->with('success', $data['status'] === 'approved' ? 'Transmission transmitted.' : 'Transmission received for moderation.');
        } catch (\Throwable $exception) {
            Log::error('Transmission creation failed.', ['exception' => $exception]);
            return back()->withInput()->withErrors(['system' => 'This transmission could not be stored right now. No changes were made.']);
        }
    }

    public function update(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string']]);
        try {
            $post->update($data);
            return back()->with('success', 'Transmission updated.');
        } catch (\Throwable $exception) {
            Log::error('Transmission update failed.', ['exception' => $exception, 'post_id' => $post->id]);
            return back()->withInput()->withErrors(['system' => 'This transmission could not be updated right now.']);
        }
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        try {
            $post->delete();
            return back()->with('success', 'Transmission deleted.');
        } catch (\Throwable $exception) {
            Log::error('Transmission deletion failed.', ['exception' => $exception, 'post_id' => $post->id]);
            return back()->withErrors(['system' => 'This transmission could not be deleted right now.']);
        }
    }
}
