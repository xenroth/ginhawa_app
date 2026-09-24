<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Connection;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommunityInteractionController extends Controller
{
    public function comment(Request $request, Post $post) { abort_unless($request->user()->canPost(), 403); $data = $request->validate(['body' => ['required', 'string', 'max:5000']]); try { $post->comments()->create(['user_id' => $request->user()->id, 'body' => $data['body'], 'status' => 'approved']); return back()->with('success', 'Reply transmitted.'); } catch (\Throwable $exception) { Log::error('Community reply failed.', ['exception' => $exception, 'post_id' => $post->id]); return back()->withErrors(['system' => 'This reply could not be transmitted right now.']); } }
    public function connect(Request $request, User $user) { abort_unless($request->user()->id !== $user->id, 422); Connection::firstOrCreate(['requester_id' => $request->user()->id, 'recipient_id' => $user->id], ['status' => 'pending']); return back()->with('success', 'Connection request sent.'); }
    public function message(Request $request, User $user) { $data = $request->validate(['body' => ['required', 'string', 'max:5000']]); Message::create(['sender_id' => $request->user()->id, 'recipient_id' => $user->id, 'body' => $data['body']]); return back()->with('success', 'Encrypted message sent.'); }
}