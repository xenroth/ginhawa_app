<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Connection;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommunityInteractionController extends Controller
{
    public function comment(Request $request, Post $post) { abort_unless($request->user()->canPost(), 403, 'Your registration is awaiting approval.'); $data = $request->validate(['body' => ['required', 'string', 'max:5000'], 'parent_id' => ['nullable', 'integer', 'exists:comments,id']]); try { $parentId = null; if (!empty($data['parent_id'])) { $parent = Comment::find($data['parent_id']); abort_unless($parent && $parent->post_id === $post->id, 422, 'Invalid parent comment.'); $parentId = $parent->id; } $post->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body'], 'status' => 'approved', 'parent_id' => $parentId]); return back()->with('success', 'Reply transmitted.'); } catch (\Throwable $exception) { Log::error('Community reply failed.', ['exception' => $exception, 'post_id' => $post->id]); return back()->withErrors(['system' => 'This reply could not be transmitted right now.']); } }

    public function commentsJson(Request $request, Post $post)
    {
        try {
            $comments = $post->comments()->with('author', 'replies.author', 'replies.replies.author')->whereNull('parent_id')->orderBy('created_at')->get();
            $myReactions = DB::table('comment_reactions')->where('user_id', $request->user()->id)->pluck('value', 'comment_id');
            $map = function ($list) use (&$map, $myReactions) {
                return $list->map(function ($c) use (&$map, $myReactions) {
                    return [
                        'id' => $c->id,
                        'author' => $c->author->name,
                        'body' => $c->body,
                        'time' => $c->created_at->diffForHumans(),
                        'up' => $c->reactions->where('value', 'up')->count(),
                        'down' => $c->reactions->where('value', 'down')->count(),
                        'mine' => $myReactions[$c->id] ?? null,
                        'replies' => $map($c->replies),
                    ];
                })->values()->all();
            };
            return response()->json(['ok' => true, 'comments' => $map($comments)]);
        } catch (\Throwable $exception) {
            Log::error('Comment tree load failed.', ['exception' => $exception, 'post_id' => $post->id]);
            return response()->json(['ok' => false, 'comments' => []], 500);
        }
    }

    public function reactComment(Request $request, Comment $comment)
    {
        $data = $request->validate(['value' => ['required', 'in:up,down']]);
        try {
            $existing = $comment->reactions()->where('user_id', $request->user()->id)->first();
            if ($existing && $existing->value === $data['value']) {
                $existing->delete();
                $mine = null;
            } elseif ($existing) {
                $existing->update(['value' => $data['value']]);
                $mine = $data['value'];
            } else {
                $comment->reactions()->create(['user_id' => $request->user()->id, 'value' => $data['value']]);
                $mine = $data['value'];
            }
            return response()->json([
                'ok' => true,
                'mine' => $mine,
                'up' => $comment->reactions()->where('value', 'up')->count(),
                'down' => $comment->reactions()->where('value', 'down')->count(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Comment reaction failed.', ['exception' => $exception, 'comment_id' => $comment->id]);
            return response()->json(['ok' => false, 'error' => 'Reaction could not be stored.'], 500);
        }
    }

    public function connect(Request $request, User $user) { abort_unless($request->user()->id !== $user->id, 422); Connection::firstOrCreate(['requester_id' => $request->user()->id, 'recipient_id' => $user->id], ['status' => 'pending']); return back()->with('success', 'Connection request sent.'); }
    public function message(Request $request, User $user) { $data = $request->validate(['body' => ['required', 'string', 'max:5000']]); Message::create(['sender_id' => $request->user()->id, 'recipient_id' => $user->id, 'body' => $data['body']]); return back()->with('success', 'Encrypted message sent.'); }

    public function react(Request $request, Post $post)
    {
        $this->ensureSchema();
        $data = $request->validate(['value' => ['required', 'in:up,down']]);
        try {
            $existing = $post->reactions()->where('user_id', $request->user()->id)->first();
            if ($existing && $existing->value === $data['value']) {
                $existing->delete();
                $mine = null;
            } elseif ($existing) {
                $existing->update(['value' => $data['value']]);
                $mine = $data['value'];
            } else {
                $post->reactions()->create(['user_id' => $request->user()->id, 'value' => $data['value']]);
                $mine = $data['value'];
            }
            return response()->json([
                'ok' => true,
                'mine' => $mine,
                'up' => $post->reactions()->where('value', 'up')->count(),
                'down' => $post->reactions()->where('value', 'down')->count(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Reaction failed.', ['exception' => $exception, 'post_id' => $post->id]);
            return response()->json(['ok' => false, 'error' => 'Reaction could not be stored.'], 500);
        }
    }

    protected function ensureSchema(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('posts') && !\Illuminate\Support\Facades\Schema::hasColumn('posts', 'media')) {
            \Illuminate\Support\Facades\Schema::table('posts', function ($table) { $table->json('media')->nullable(); });
        }
    }
}
