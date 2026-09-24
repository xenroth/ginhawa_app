<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('author')->visible($request->user())->latest()->get();
        return view('forum', ['posts' => $posts, 'sectors' => Sector::where('is_active', true)->orderBy('sort_order')->pluck('name')->all() ?: ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE']]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canPost(), 403, 'Your registration is awaiting approval.');
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string'], 'sector' => ['required', Rule::exists('sectors', 'name')], 'clearance' => ['required', 'in:public,member,enforcer'], 'tags' => ['nullable', 'string']]);
        $data['user_id'] = $request->user()->id;
        $data['status'] = $request->user()->hasAnyRole(['administrator', 'moderator']) ? 'approved' : 'pending';
        $data['tags'] = collect(explode(',', $data['tags'] ?? ''))->map(fn ($tag) => trim($tag))->filter()->values()->all();
        Post::create($data);
        return back()->with('success', 'Transmission received for moderation.');
    }

    public function update(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string']]);
        $post->update($data);
        return back()->with('success', 'Transmission updated.');
    }

    public function destroy(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        $post->delete();
        return back()->with('success', 'Transmission deleted.');
    }
}