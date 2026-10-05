<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::with('actor')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'icon' => $n->icon(),
                'label' => $n->label(),
                'body' => $n->body ? \Illuminate\Support\Str::limit($n->body, 120) : null,
                'unread' => $n->read_at === null,
                'ago' => $n->created_at?->diffForHumans(),
                'subject_url' => $this->subjectUrl($n),
            ]);

        return response()->json([
            'unread' => Notification::where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            'notifications' => $notifications,
        ]);
    }

    public function readAll(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    protected function subjectUrl(Notification $n): ?string
    {
        $subject = $n->subject;
        if ($subject instanceof \App\Models\Post) {
            return route('community').'?open_post='.$subject->id;
        }
        if ($subject instanceof \App\Models\Comment) {
            return route('community').'?open_post='.$subject->post_id;
        }
        return route('community');
    }
}
