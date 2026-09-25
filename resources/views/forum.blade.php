@extends('layouts.app')

@section('content')
<main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[250px_1fr_280px]">

    {{-- ========================================== --}}
    {{-- COLUMN 1: LEFT COLUMN                     --}}
    {{-- - Forum Sectors                            --}}
    {{-- - Network Status                           --}}
    {{-- ========================================== --}}
    <aside class="space-y-6">

        {{-- Widget 1.1: Forum Sectors --}}
        <div class="border border-silver-800 bg-pitch-900 p-4">
            <div class="mb-3 flex items-center justify-between border-b border-silver-800 pb-2">
                <p class="text-[10px] font-mono tracking-widest text-silver-500">// FORUM SECTORS</p>
                @if(auth()->user()->hasRole('administrator'))
                    <a href="{{ route('admin.settings') }}" class="text-[9px] text-silver-500 underline hover:text-white">MANAGE</a>
                @endif
            </div>

            <div class="space-y-1.5 text-xs">
                <a href="{{ route('community', ['sector' => 'ALL']) }}"
                   class="sector-btn flex items-center justify-between border-l-2 p-2.5 text-left transition-colors {{ ($selectedSector === 'ALL' || !$selectedSector) ? 'border-white bg-pitch-800 text-white font-bold' : 'border-transparent text-silver-400 hover:border-silver-500 hover:bg-pitch-800 hover:text-white' }}"
                   data-sector="ALL">
                    <span>00. ALL SECTORS</span>
                    <span class="rounded border border-silver-800 bg-pitch-950 px-1.5 py-0.5 font-mono text-[9px] text-silver-400">
                        {{ $metrics['clearedThreads'] }}
                    </span>
                </a>

                @foreach($sectors as $sec)
                    @php($secName = is_string($sec) ? $sec : $sec->name)
                    @php($isActive = strtoupper($selectedSector) === strtoupper($secName))
                    @php($count = $sectorCounts[$secName] ?? 0)
                    <a href="{{ route('community', ['sector' => $secName]) }}"
                       class="sector-btn flex items-center justify-between border-l-2 p-2.5 text-left transition-colors {{ $isActive ? 'border-white bg-pitch-800 text-white font-bold' : 'border-transparent text-silver-400 hover:border-silver-500 hover:bg-pitch-800 hover:text-white' }}"
                       data-sector="{{ $secName }}">
                        <span class="truncate">{{ sprintf('%02d', $loop->iteration) }}. {{ $secName }}</span>
                        <span class="rounded border border-silver-800 bg-pitch-950 px-1.5 py-0.5 font-mono text-[9px] text-silver-400">
                            {{ $count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Widget 1.2: Network Status --}}
        <div class="border border-silver-800 bg-pitch-900 p-4 text-xs text-silver-400">
            <div class="mb-3 flex items-center justify-between border-b border-silver-800 pb-2">
                <p class="text-[10px] font-mono tracking-widest text-silver-500">// NETWORK STATUS</p>
                <span class="inline-flex items-center gap-1.5 text-[9px] text-emerald-400 font-mono">
                    <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span> ONLINE
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <p class="flex justify-between border-b border-silver-800/60 py-1.5">
                    <span class="text-silver-500">CLEARED THREADS</span>
                    <strong class="font-mono text-white">{{ $metrics['clearedThreads'] }}</strong>
                </p>
                <p class="flex justify-between border-b border-silver-800/60 py-1.5">
                    <span class="text-silver-500">VERIFIED OPERATIVES</span>
                    <strong class="font-mono text-white">{{ $metrics['activeOperatives'] }}</strong>
                </p>
                <p class="flex justify-between border-b border-silver-800/60 py-1.5">
                    <span class="text-silver-500">ACTIVE SECTORS</span>
                    <strong class="font-mono text-white">{{ $metrics['activeSectors'] }}</strong>
                </p>
                @if($metrics['pendingPosts'] > 0)
                    <p class="flex justify-between border-b border-silver-800/60 py-1.5 text-amber-300">
                        <span>PENDING REVIEW</span>
                        <a href="{{ route('admin') }}" class="font-mono underline">{{ $metrics['pendingPosts'] }}</a>
                    </p>
                @endif
            </div>

            {{-- Operative Intel Search --}}
            <div class="mt-4 border-t border-silver-800 pt-3">
                <label class="block text-[10px] font-mono tracking-wider text-silver-500">
                    SEARCH OPERATIVE INTEL
                    <input id="memberSearch" 
                           autocomplete="off"
                           class="mt-1.5 w-full border border-silver-700 bg-pitch-950 p-2 text-xs text-white placeholder-silver-600 outline-none focus:border-white" 
                           placeholder="Name, title, citizen #">
                </label>
                <div id="memberResults" class="mt-2 space-y-1"></div>
            </div>
        </div>

    </aside>

    {{-- ========================================== --}}
    {{-- COLUMN 2: MIDDLE COLUMN                   --}}
    {{-- - New Transmission (swapped to appear above) --}}
    {{-- - Community Transmissions (Intel search/feed) --}}
    {{-- ========================================== --}}
    <section class="space-y-6">

        {{-- Widget 2.1: NEW TRANSMISSION (Swapped above community transmissions) --}}
        <div id="new-transmission" class="border border-silver-700 bg-pitch-900 p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-silver-800 pb-3">
                <div>
                    <p class="text-[10px] font-mono tracking-widest text-silver-500">// OUTBOUND TRANSMISSION</p>
                    <h2 class="font-oswald text-2xl uppercase tracking-wider text-white">+ New transmission</h2>
                </div>
                <span class="border border-silver-700 bg-pitch-950 px-2 py-1 text-[10px] font-mono text-silver-400">
                    CLEARANCE: {{ strtoupper(auth()->user()->roles->first()?->label ?? 'Public Citizen') }}
                </span>
            </div>

            @if(session('success'))
                <div class="mb-4 border border-emerald-500/50 bg-emerald-950/30 p-3 text-xs text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 border border-red-500/60 bg-red-950/40 p-3 text-xs text-red-200 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>&bull; {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(auth()->user()->canPost())
                <form method="post" action="{{ route('posts.store') }}" class="grid gap-3.5 text-xs">
                    @csrf
                    <div>
                        <input name="title" 
                               value="{{ old('title') }}" 
                               required 
                               class="w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white placeholder-silver-600 outline-none focus:border-white" 
                               placeholder="Transmission subject / Intel title...">
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[10px] text-silver-500">ASSIGN SECTOR</label>
                            <select name="sector" class="w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white outline-none focus:border-white">
                                @foreach($sectors as $sec)
                                    @php($secName = is_string($sec) ? $sec : $sec->name)
                                    <option value="{{ $secName }}" {{ old('sector', ($selectedSector === 'ALL' ? '' : $selectedSector)) === $secName ? 'selected' : '' }}>
                                        {{ $secName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] text-silver-500">CLEARANCE CLASSIFICATION</label>
                            <select name="clearance" class="w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white outline-none focus:border-white">
                                <option value="public" {{ old('clearance') === 'public' ? 'selected' : '' }}>PUBLIC CITIZEN</option>
                                <option value="member" {{ old('clearance', 'member') === 'member' ? 'selected' : '' }}>MEMBER ONLY</option>
                                <option value="enforcer" {{ old('clearance') === 'enforcer' ? 'selected' : '' }}>ENFORCER / COUNCIL</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <input name="tags" 
                               value="{{ old('tags') }}" 
                               class="w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white placeholder-silver-600 outline-none focus:border-white" 
                               placeholder="Tags (e.g. Whistleblower, GlobalOps, FieldReport)">
                    </div>

                    <div>
                        <textarea name="body" 
                                  required 
                                  rows="4" 
                                  class="w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white placeholder-silver-600 outline-none focus:border-white" 
                                  placeholder="Provide verifiable intel details, dispatches, or community reports...">{{ old('body') }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <span class="text-[10px] font-mono text-silver-500">// AES-256 ENCRYPTED COVENANT PROTOCOL</span>
                        <button type="submit" class="btn border-2 border-white bg-white px-5 py-2 text-xs font-bold tracking-widest text-black hover:bg-silver-200">
                            TRANSMIT SIGNAL
                        </button>
                    </div>
                </form>
            @else
                <div class="border border-silver-800 bg-pitch-950 p-4 text-xs text-silver-400">
                    <p class="font-bold text-silver-300">// BROADCAST STATUS: PENDING VERIFICATION</p>
                    <p class="mt-1 leading-5 text-silver-500">
                        Your citizen registration is currently undergoing council verification. Outbound transmission capabilities will activate immediately upon council clearance.
                    </p>
                </div>
            @endif
        </div>

        {{-- Widget 2.2: COMMUNITY TRANSMISSIONS (SEARCH INTEL / CONTENT) --}}
        <div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border border-silver-800 bg-pitch-900 p-3.5">
                <div class="flex items-center gap-3">
                    <h1 class="font-oswald text-2xl uppercase tracking-wider text-white">Community transmissions</h1>
                    <span class="border border-silver-700 px-2 py-0.5 text-[10px] font-mono text-silver-300">
                        SECTOR // {{ $selectedSector }}
                    </span>
                </div>
                <div>
                    <input id="postSearch" 
                           value="{{ $search }}"
                           class="border border-silver-700 bg-pitch-950 px-3 py-1.5 text-xs text-white placeholder-silver-600 outline-none focus:border-white" 
                           placeholder="SEARCH INTEL...">
                </div>
            </div>

            @if($errors->has('system'))
                <div class="mb-4 border border-red-500/60 bg-red-950/30 p-4 text-xs text-red-200 font-mono">
                    {{ $errors->first('system') }}
                </div>
            @endif

            <div id="postGrid" class="space-y-4">
                @forelse($posts as $post)
                    @php($postTags = is_array($post->tags) ? $post->tags : (json_decode($post->tags ?? '', true) ?: []))
                    @php($authorName = $post->author?->name ?? 'Unknown Operative')
                    @php($authorId = $post->author?->id)
                    @php($timeAgo = $post->created_at ? $post->created_at->diffForHumans() : 'RECENT')
                    <article class="post-card border {{ $post->is_pinned ? 'border-white hard shadow-lg' : 'border-silver-800' }} bg-pitch-900 p-5 transition-colors" 
                             data-sector="{{ $post->sector }}" 
                             data-search="{{ strtolower($post->title.' '.$post->body.' '.implode(' ', (array)$postTags).' '.$authorName) }}">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-[10px] text-silver-500">
                            <div class="flex items-center gap-2">
                                <span class="border border-silver-700 px-2 py-0.5 font-mono text-silver-300">
                                    SECTOR // {{ $post->sector }}
                                </span>
                                @if($post->is_pinned)
                                    <span class="border border-white bg-white px-1.5 py-0.5 font-bold text-black font-mono">
                                        PINNED INTEL
                                    </span>
                                @endif
                                <span class="border border-silver-800 px-1.5 py-0.5 font-mono text-silver-400">
                                    {{ strtoupper($post->clearance) }}
                                </span>
                            </div>
                            <span class="font-mono">
                                {{ strtoupper($post->status) }} // {{ $timeAgo }}
                            </span>
                        </div>

                        <h2 class="mb-2 font-oswald text-2xl uppercase tracking-wide text-white">
                            {{ $post->title }}
                        </h2>

                        <p class="mb-4 text-xs leading-6 text-silver-300">
                            {{ Str::limit($post->body, 280) }}
                        </p>

                        @if(!empty($postTags))
                            <div class="mb-4 flex flex-wrap gap-1.5">
                                @foreach($postTags as $tag)
                                    <span class="border border-silver-800 bg-pitch-950 px-2 py-0.5 text-[10px] font-mono text-silver-400">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center justify-between border-t border-silver-800 pt-3 text-[10px]">
                            <span class="text-silver-500">
                                BY 
                                @if($authorId)
                                    <a href="{{ route('members.profile', $authorId) }}" class="font-bold text-white hover:underline">
                                        {{ strtoupper($authorName) }}
                                    </a>
                                @else
                                    <strong class="text-white">{{ strtoupper($authorName) }}</strong>
                                @endif
                            </span>
                            <button type="button" 
                                    class="read-post border border-silver-700 px-3 py-1 text-white hover:border-white hover:bg-pitch-800" 
                                    data-title="{{ htmlspecialchars($post->title, ENT_QUOTES) }}" 
                                    data-body="{{ htmlspecialchars($post->body, ENT_QUOTES) }}"
                                    data-sector="{{ $post->sector }}"
                                    data-author="{{ htmlspecialchars($authorName, ENT_QUOTES) }}"
                                    data-time="{{ $timeAgo }}">
                                READ INTEL &rarr;
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="border border-silver-800 bg-pitch-900 p-12 text-center text-silver-500">
                        <p class="font-mono text-xs">// NO CLEARED TRANSMISSIONS FOUND FOR SECTOR: {{ $selectedSector }}</p>
                        <p class="mt-2 text-[10px] text-silver-600">Transmit a new dispatch or switch sectors from the left column.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        </div>

    </section>

    {{-- ========================================== --}}
    {{-- COLUMN 3: RIGHT COLUMN                    --}}
    {{-- - Council Directive (managed in admin)     --}}
    {{-- - Clearance Rules (managed in admin)       --}}
    {{-- - Custom Widgets (ads, promotions, groups) --}}
    {{-- ========================================== --}}
    <aside class="space-y-6">

        {{-- Widget 3.1: Council Directives (Managed in admin) --}}
        <div class="border-2 border-white bg-pitch-900 p-5 hard">
            <div class="mb-3 flex items-center justify-between border-b border-silver-800 pb-2">
                <p class="text-[10px] font-mono tracking-widest text-silver-400">// COUNCIL DIRECTIVE</p>
                @if(auth()->user()->hasRole('administrator'))
                    <a href="{{ route('admin.settings') }}" class="text-[9px] text-silver-500 underline hover:text-white">MANAGE</a>
                @endif
            </div>

            <div class="space-y-4">
                @foreach($directives as $dir)
                    <div class="{{ !$loop->first ? 'border-t border-silver-800/80 pt-3' : '' }}">
                        @if($dir->pillar)
                            <span class="text-[9px] font-mono uppercase tracking-widest text-silver-400">
                                PILLAR // {{ $dir->pillar }}
                            </span>
                        @endif
                        <h2 class="mt-1 font-oswald text-xl uppercase tracking-wide text-white">
                            {{ $dir->title }}
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-silver-400">
                            {{ $dir->body }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Widget 3.2: Clearance Rules (Managed in admin) --}}
        <div class="border border-silver-800 bg-pitch-900 p-5">
            <div class="mb-3 flex items-center justify-between border-b border-silver-800 pb-2">
                <p class="text-[10px] font-mono tracking-widest text-silver-500">// CLEARANCE RULES</p>
                @if(auth()->user()->hasRole('administrator'))
                    <a href="{{ route('admin.settings') }}" class="text-[9px] text-silver-500 underline hover:text-white">MANAGE</a>
                @endif
            </div>
            <div class="space-y-2.5 text-xs text-silver-300">
                @foreach(explode("\n", $clearanceRules) as $ruleLine)
                    @if(trim($ruleLine))
                        <div class="rounded border-l-2 border-silver-600 bg-pitch-950 p-2">
                            <p class="text-[11px] leading-relaxed text-silver-300">{{ trim($ruleLine) }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Widget 3.3: Custom Widget (Ads, Promotions, Alliances, Groups - Managed in admin) --}}
        @if(!empty($customWidget['enabled']))
            <div class="border border-silver-700 bg-pitch-900 p-5">
                <div class="mb-3 flex items-center justify-between border-b border-silver-800 pb-2">
                    <p class="text-[10px] font-mono tracking-widest text-silver-500">
                        {{ $customWidget['badge'] ?: '// ALLIED NETWORK' }}
                    </p>
                    @if(auth()->user()->hasRole('administrator'))
                        <a href="{{ route('admin.settings') }}" class="text-[9px] text-silver-500 underline hover:text-white">MANAGE</a>
                    @endif
                </div>
                <h3 class="font-oswald text-lg uppercase tracking-wide text-white">
                    {{ $customWidget['title'] }}
                </h3>
                <p class="mt-2 text-xs leading-5 text-silver-400 whitespace-pre-line">
                    {{ $customWidget['content'] }}
                </p>
            </div>
        @endif

    </aside>

</main>

{{-- Thread Reading Modal --}}
<div id="threadModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
    <div class="w-full max-w-2xl border-2 border-white bg-pitch-950 p-6 hard">
        <div class="mb-4 flex items-center justify-between border-b border-silver-800 pb-3">
            <span id="modalMeta" class="text-[10px] font-mono text-silver-400"></span>
            <button type="button" onclick="closeThreadModal()" class="bg-white px-2.5 py-1 text-xs font-bold text-black hover:bg-silver-200">
                &times; CLOSE
            </button>
        </div>
        <h2 id="modalTitle" class="mb-4 font-oswald text-3xl uppercase tracking-wide text-white"></h2>
        <div id="modalBody" class="max-h-[60vh] overflow-y-auto whitespace-pre-line text-sm leading-7 text-silver-300"></div>
    </div>
</div>

@push('scripts')
<script>
// Search intel client-side filter
const cards = [...document.querySelectorAll('.post-card')];
const postSearch = document.getElementById('postSearch');

if (postSearch) {
    postSearch.addEventListener('input', () => {
        const q = postSearch.value.toLowerCase().trim();
        cards.forEach(card => {
            const matches = !q || (card.dataset.search && card.dataset.search.includes(q));
            card.classList.toggle('hidden', !matches);
        });
    });
}

// Thread modal interactions
const threadModal = document.getElementById('threadModal');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const modalMeta = document.getElementById('modalMeta');

function closeThreadModal() {
    if (threadModal) {
        threadModal.classList.add('hidden');
        threadModal.classList.remove('flex');
    }
}

document.querySelectorAll('.read-post').forEach(button => {
    button.onclick = () => {
        modalTitle.textContent = button.dataset.title || '';
        modalBody.textContent = button.dataset.body || '';
        modalMeta.textContent = (button.dataset.sector ? 'SECTOR // ' + button.dataset.sector : '') + 
                                (button.dataset.author ? ' | BY ' + button.dataset.author : '') +
                                (button.dataset.time ? ' | ' + button.dataset.time : '');
        threadModal.classList.remove('hidden');
        threadModal.classList.add('flex');
    };
});

// Operative Search AJAX
const memberSearch = document.getElementById('memberSearch');
const memberResults = document.getElementById('memberResults');
let searchTimer;

if (memberSearch && memberResults) {
    memberSearch.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = memberSearch.value.trim();
        if (q.length < 2) {
            memberResults.innerHTML = '';
            return;
        }
        searchTimer = setTimeout(() => {
            fetch('{{ route('members.search') }}?q=' + encodeURIComponent(q), {
                headers: { Accept: 'application/json' }
            })
            .then(res => res.json())
            .then(users => {
                if (users.length === 0) {
                    memberResults.innerHTML = '<p class="text-[10px] text-silver-600">// NO OPERATIVES MATCHED</p>';
                    return;
                }
                memberResults.innerHTML = users.map(user => `
                    <a href="{{ url('/members') }}/${user.id}" class="block border border-silver-800 bg-pitch-950 p-2 text-[10px] hover:border-white">
                        <strong class="text-white">${user.name}</strong><br>
                        <span class="text-silver-400">${user.designation || 'Ginhawa Citizen'}</span> // 
                        <span class="text-silver-500 font-mono">${user.citizen_number || ''}</span>
                    </a>
                `).join('');
            })
            .catch(() => {
                memberResults.innerHTML = '<p class="text-[10px] text-red-300">// SEARCH UNAVAILABLE</p>';
            });
        }, 250);
    });
}
</script>
@endpush
@endsection
