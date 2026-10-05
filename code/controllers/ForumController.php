<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        try {
            $posts = Post::with('author')->visible($request->user())->latest()->get();
            $sectors = Sector::where('is_active', true)->orderBy('sort_order')->pluck('name')->all();
            return view('forum', ['posts' => $posts, 'sectors' => $sectors ?: ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE']]);
        } catch (\Throwable $exception) {
            Log::error('Community transmission load failed.', ['exception' => $exception]);
            return view('forum', ['posts' => collect(), 'sectors' => ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE']])->withErrors(['system' => 'The community signal is temporarily unavailable. Please try again after the council restores the database connection.']);
        }
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canPost(), 403, 'Your registration is awaiting approval.');
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string'], 'sector' => ['required', Rule::exists('sectors', 'name')], 'clearance' => ['required', 'in:public,member,enforcer'], 'tags' => ['nullable', 'string']]);
        $data['user_id'] = $request->user()->id;
        $data['status'] = $request->user()->hasAnyRole(['administrator', 'moderator']) ? 'approved' : 'pending';
        $data['tags'] = collect(explode(',', $data['tags'] ?? ''))->map(fn ($tag) => trim($tag))->filter()->values()->all();
        try {
            Post::create($data);
            return back()->with('success', 'Transmission received for moderation.');
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