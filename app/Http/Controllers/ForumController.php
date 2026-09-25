<?php

namespace App\Http\Controllers;

use App\Models\Directive;
use App\Models\Post;
use App\Models\Sector;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $selectedSector = trim((string) $request->query('sector', 'ALL'));
        if ($selectedSector === '') {
            $selectedSector = 'ALL';
        }
        $search = trim((string) $request->query('q', ''));

        try {
            $query = Post::with('author')->visible($request->user());

            if ($selectedSector !== 'ALL') {
                $query->where('sector', $selectedSector);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%")
                      ->orWhere('sector', 'like', "%{$search}%");
                });
            }

            $posts = $query->latest()->paginate(10)->withQueryString();
        } catch (\Throwable $exception) {
            Log::error('Community transmission load failed.', ['exception' => $exception]);
            $posts = new LengthAwarePaginator(collect(), 0, 10, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        // Active sectors from database with fallback
        $sectors = Sector::where('is_active', true)->orderBy('sort_order')->get();
        if ($sectors->isEmpty()) {
            $defaultSectors = ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE'];
            $sectors = collect($defaultSectors)->map(fn ($name, $i) => new Sector([
                'name' => $name,
                'slug' => strtolower($name),
                'description' => "Community sector // {$name}",
                'sort_order' => $i + 1,
                'is_active' => true,
            ]));
        }

        // Calculate approved post count per sector
        try {
            $sectorCounts = Post::visible($request->user())
                ->selectRaw('sector, count(*) as count')
                ->groupBy('sector')
                ->pluck('count', 'sector')
                ->all();
        } catch (\Throwable $e) {
            $sectorCounts = [];
        }

        // Council directives from database
        $directives = Directive::where('is_active', true)->orderBy('sort_order')->get();
        if ($directives->isEmpty()) {
            $directives = collect([
                new Directive([
                    'pillar' => 'PAGTIPIG',
                    'title' => 'Preserve human life',
                    'body' => 'Life is non-negotiable. Sanctuary, medical security, and mutual preservation above all systems.',
                ]),
                new Directive([
                    'pillar' => 'KAUGALINGON',
                    'title' => 'Retain humanity',
                    'body' => 'Defend privacy, autonomy, and moral dignity without compromise against predatory surveillance.',
                ]),
                new Directive([
                    'pillar' => 'PAG-USWAG',
                    'title' => 'Develop humanity',
                    'body' => 'Advance decentralized intelligence, open science, sovereign education, and collective ascension.',
                ]),
                new Directive([
                    'pillar' => 'PAG-ATBANG',
                    'title' => 'Eradicate corruption',
                    'body' => 'Expose predatory hierarchies and build resilient community alternatives.',
                ]),
            ]);
        }

        // Live network metrics synchronized with database
        try {
            $clearedThreads = Post::visible($request->user())->count();
            $activeOperatives = User::where('status', 'active')->count();
        } catch (\Throwable $e) {
            $clearedThreads = 0;
            $activeOperatives = 0;
        }

        $pendingPosts = 0;
        if ($request->user() && $request->user()->hasAnyRole(['administrator', 'moderator'])) {
            try {
                $pendingPosts = Post::where('status', 'pending')->count();
            } catch (\Throwable $e) {
                $pendingPosts = 0;
            }
        }

        $metrics = [
            'clearedThreads' => $clearedThreads,
            'activeOperatives' => $activeOperatives,
            'activeSectors' => $sectors->count(),
            'pendingPosts' => $pendingPosts,
        ];

        // Clearance rules from CMS settings
        $clearanceRules = SiteSetting::value('clearance_rules');
        if (!$clearanceRules) {
            $clearanceRules = implode("\n", [
                'CLEARANCE 01 // PUBLIC CITIZEN: Read-only access to unclassified transmissions and general directives.',
                'CLEARANCE 02 // VERIFIED MEMBER: Outbound dispatch capabilities, encrypted comments, and peer connections.',
                'CLEARANCE 03 // MODERATOR & ENFORCER: Intel signal verification, citizen review, and sector supervision.',
                'CLEARANCE 04 // COUNCIL ADMINISTRATOR: System architecture, cryptographic controls, and directive promulgation.',
            ]);
        }

        // Custom widget (ads / promotions / group networks)
        $customWidget = [
            'enabled' => SiteSetting::value('custom_widget_enabled', '1') !== '0',
            'badge' => SiteSetting::value('custom_widget_badge', '// ALLIED NETWORK'),
            'title' => SiteSetting::value('custom_widget_title', 'HUMANITARIAN RELIEF & OPERATIVES ALLIANCE'),
            'content' => SiteSetting::value('custom_widget_content', 'Mutual aid coordinates, field sanctuaries, and encrypted supply chains are deployed through our decentralized nodes. Connect with regional coordinators to support the mission.'),
        ];

        return view('forum', [
            'posts' => $posts,
            'sectors' => $sectors,
            'sectorCounts' => $sectorCounts,
            'selectedSector' => $selectedSector,
            'search' => $search,
            'directives' => $directives,
            'metrics' => $metrics,
            'clearanceRules' => $clearanceRules,
            'customWidget' => $customWidget,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canPost(), 403, 'Your registration is awaiting approval.');

        // Build list of valid sector names dynamically
        $activeSectors = Sector::where('is_active', true)->pluck('name')->all();
        $fallbackSectors = ['MANIFESTO', 'OPERATIVES', 'PRESERVATION', 'ARCHIVES', 'LOUNGE'];
        $allowedSectors = array_unique(array_merge($activeSectors, $fallbackSectors));

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'sector' => ['required', 'string', Rule::in($allowedSectors)],
            'clearance' => ['required', 'in:public,member,enforcer'],
            'tags' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $request->user()->id;
        $isCouncil = $request->user()->hasAnyRole(['administrator', 'moderator']);
        $data['status'] = $isCouncil ? 'approved' : 'pending';

        // Normalize tags safely
        $rawTags = (string) ($data['tags'] ?? '');
        $data['tags'] = collect(explode(',', $rawTags))
            ->map(fn ($tag) => trim(str_replace('#', '', $tag)))
            ->filter()
            ->values()
            ->all();

        // Ensure sector exists in the sectors table
        if (!Sector::where('name', $data['sector'])->exists()) {
            try {
                Sector::create([
                    'name' => $data['sector'],
                    'slug' => strtolower($data['sector']),
                    'description' => "Community sector // {$data['sector']}",
                    'is_active' => true,
                    'sort_order' => 99,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Auto-creating sector skipped.', ['sector' => $data['sector']]);
            }
        }

        try {
            Post::create($data);
            $message = $isCouncil 
                ? 'Transmission successfully broadcast to the community network.' 
                : 'Transmission received. Signal queued for council moderation.';
            return redirect()->route('community')->with('success', $message);
        } catch (\Throwable $exception) {
            Log::error('Transmission creation failed.', ['exception' => $exception]);
            return back()->withInput()->withErrors(['system' => 'This transmission could not be stored right now. Please try again.']);
        }
    }

    public function update(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->hasAnyRole(['administrator', 'moderator']), 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
        ]);
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