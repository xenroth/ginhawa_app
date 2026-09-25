@extends('layouts.app')

@section('content')
<main class="mx-auto max-w-7xl px-4 py-10">
    <header class="mb-8 flex flex-wrap items-end justify-between gap-4 border-b border-silver-800 pb-5">
        <div>
            <p class="font-mono text-xs tracking-[.3em] text-silver-500">// CMS CONTROL NODE</p>
            <h1 class="font-oswald text-5xl uppercase text-white">System settings</h1>
        </div>
        <a href="{{ route('admin') }}" class="border border-silver-600 px-3 py-2 text-xs text-silver-300 hover:border-white hover:text-white">
            &larr; BACK TO ADMIN
        </a>
    </header>

    @if(session('success'))
        <div class="mb-6 border border-emerald-500/50 bg-emerald-950/30 p-4 text-xs text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 border border-red-500/60 bg-red-950/40 p-4 text-xs text-red-200 space-y-1">
            @foreach($errors->all() as $err)
                <p>&bull; {{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        {{-- Left Column: Global Identity, Clearance Rules & Custom Widget --}}
        <div class="space-y-6">
            <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="border border-silver-700 bg-pitch-900 p-6 text-xs">
                @csrf
                @method('PATCH')
                <h2 class="mb-5 font-oswald text-2xl uppercase text-white">Website identity & SEO</h2>

                @foreach([
                    'site_name' => 'WEBSITE NAME',
                    'site_tagline' => 'HEADER TAGLINE / SLOGAN',
                    'seo_title' => 'SEO TITLE',
                    'seo_description' => 'META DESCRIPTION',
                    'seo_keywords' => 'KEYWORDS',
                    'default_jurisdiction' => 'DEFAULT JURISDICTION',
                    'default_designation' => 'DEFAULT DESIGNATION',
                    'xrp_address' => 'XRP DEPOSIT ADDRESS',
                    'xrp_memo' => 'XRP MEMO / DESTINATION TAG'
                ] as $key => $label)
                    <label class="mb-3 block text-silver-400">
                        {{ $label }}
                        <input name="{{ $key }}" value="{{ $settings[$key] ?? '' }}" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2.5 text-white outline-none focus:border-white">
                    </label>
                @endforeach

                <div class="my-6 border-t border-silver-800 pt-4">
                    <h3 class="mb-3 font-oswald text-xl uppercase text-white">Branding Media</h3>
                    <label class="mb-3 block text-silver-400">
                        FAVICON (.ico, .png, .svg)
                        <input type="file" name="favicon" accept=".ico,.png,.svg" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2 text-white">
                    </label>
                    <label class="mb-5 block text-silver-400">
                        LOGO (image)
                        <input type="file" name="logo" accept="image/*" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2 text-white">
                    </label>
                </div>

                {{-- Community Widgets Management --}}
                <div class="my-6 border-t border-silver-800 pt-4">
                    <h3 class="mb-3 font-oswald text-xl uppercase text-white">Community Clearance Rules (Column 3)</h3>
                    <p class="mb-2 text-silver-500">Each line represents a clearance rule item displayed in the /community widget.</p>
                    <textarea name="clearance_rules" rows="5" class="w-full border border-silver-700 bg-pitch-950 p-2.5 font-mono text-xs text-white outline-none focus:border-white">{{ $settings['clearance_rules'] ?? '' }}</textarea>
                </div>

                <div class="my-6 border-t border-silver-800 pt-4">
                    <h3 class="mb-3 font-oswald text-xl uppercase text-white">Custom Widget (Column 3 - Ads / Promotions / Groups)</h3>
                    <label class="mb-3 flex items-center gap-2 text-white">
                        <input type="checkbox" name="custom_widget_enabled" value="1" {{ (!empty($settings['custom_widget_enabled']) && $settings['custom_widget_enabled'] !== '0') ? 'checked' : '' }}>
                        ENABLE CUSTOM WIDGET IN COMMUNITY COLUMN 3
                    </label>
                    <label class="mb-3 block text-silver-400">
                        WIDGET BADGE / SUBTITLE
                        <input name="custom_widget_badge" value="{{ $settings['custom_widget_badge'] ?? '// ALLIED NETWORK' }}" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white" placeholder="// ALLIED NETWORK">
                    </label>
                    <label class="mb-3 block text-silver-400">
                        WIDGET TITLE
                        <input name="custom_widget_title" value="{{ $settings['custom_widget_title'] ?? 'HUMANITARIAN RELIEF & ALLIANCE' }}" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white" placeholder="HUMANITARIAN RELIEF & ALLIANCE">
                    </label>
                    <label class="mb-5 block text-silver-400">
                        WIDGET CONTENT
                        <textarea name="custom_widget_content" rows="4" class="mt-1 w-full border border-silver-700 bg-pitch-950 p-2.5 text-xs text-white outline-none focus:border-white" placeholder="Promotional information, mutual aid coordination, or external partner links...">{{ $settings['custom_widget_content'] ?? '' }}</textarea>
                    </label>
                </div>

                <button class="w-full bg-white p-3 text-xs font-bold tracking-widest text-black hover:bg-silver-200">
                    SAVE WEBSITE & CMS SETTINGS
                </button>
            </form>
        </div>

        {{-- Right Column: Dynamic Sectors, Directives, Roles & Member Assignment --}}
        <div class="space-y-6">

            {{-- Assign Member Identity --}}
            <section class="border border-silver-700 bg-pitch-900 p-6 text-xs">
                <h2 class="mb-2 font-oswald text-2xl uppercase text-white">Assign member identity</h2>
                <p class="mb-4 text-silver-500">Type a name, email, or citizen number. Results are limited to 10 members.</p>
                <form method="post" action="{{ route('admin.users.identity', ['user' => 0]) }}" id="identityAssignment" class="grid gap-2">
                    @csrf
                    @method('PATCH')
                    <input id="memberSearch" autocomplete="off" placeholder="Search member..." class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <div id="memberResults" class="space-y-1"></div>
                    <input type="hidden" name="jurisdiction" value="{{ $settings['default_jurisdiction'] ?? 'Local Community / Own Country' }}">
                    <input type="hidden" name="designation" value="{{ $settings['default_designation'] ?? 'Ginhawa Citizen' }}">
                    <p id="selectedMember" class="text-silver-500 font-mono">No member selected.</p>
                    <button class="border border-silver-500 p-2 text-silver-200 hover:border-white hover:text-white">ASSIGN IDENTITY</button>
                </form>
            </section>

            {{-- Sectors Management --}}
            <section class="border border-silver-700 bg-pitch-900 p-6 text-xs">
                <div class="mb-4 flex items-center justify-between border-b border-silver-800 pb-2">
                    <h2 class="font-oswald text-2xl uppercase text-white">Community sectors</h2>
                    <span class="text-silver-500 font-mono text-[10px]">TOTAL: {{ $sectors->count() }}</span>
                </div>

                <div class="mb-4 space-y-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($sectors as $sec)
                        <div class="flex items-center justify-between border border-silver-800 bg-pitch-950 p-2.5">
                            <div>
                                <strong class="text-white font-mono">{{ $sec->name }}</strong>
                                <span class="text-[10px] text-silver-500">({{ $sec->slug }})</span>
                                @if($sec->description)
                                    <p class="text-[10px] text-silver-400">{{ $sec->description }}</p>
                                @endif
                            </div>
                            <form method="post" action="{{ route('admin.sectors.destroy', $sec) }}" onsubmit="return confirm('Remove sector {{ $sec->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="border border-red-500/40 px-2 py-1 text-[10px] text-red-300 hover:border-red-400 hover:bg-red-950/40">
                                    DELETE
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-silver-500 text-center py-2">No sectors configured.</p>
                    @endforelse
                </div>

                <form method="post" action="{{ route('admin.sectors.store') }}" class="grid gap-2 border-t border-silver-800 pt-3">
                    @csrf
                    <p class="text-[10px] font-mono text-silver-400">+ ADD NEW SECTOR</p>
                    <input name="name" required placeholder="Sector name (e.g. INTELLIGENCE)" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <input name="slug" required placeholder="sector-slug (e.g. intelligence)" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <textarea name="description" placeholder="Sector description" rows="2" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white"></textarea>
                    <button class="border border-silver-500 p-2 text-silver-200 hover:border-white hover:text-white">ADD SECTOR</button>
                </form>
            </section>

            {{-- Directives Management --}}
            <section class="border border-silver-700 bg-pitch-900 p-6 text-xs">
                <div class="mb-4 flex items-center justify-between border-b border-silver-800 pb-2">
                    <h2 class="font-oswald text-2xl uppercase text-white">Council directives</h2>
                    <span class="text-silver-500 font-mono text-[10px]">TOTAL: {{ $directives->count() }}</span>
                </div>

                <div class="mb-4 space-y-2 max-h-48 overflow-y-auto pr-1">
                    @forelse($directives as $dir)
                        <div class="flex items-center justify-between border border-silver-800 bg-pitch-950 p-2.5">
                            <div>
                                @if($dir->pillar)
                                    <span class="text-[9px] font-mono uppercase text-silver-400">{{ $dir->pillar }} // </span>
                                @endif
                                <strong class="text-white font-oswald tracking-wide text-sm">{{ $dir->title }}</strong>
                                <p class="text-[10px] text-silver-400 mt-0.5">{{ Str::limit($dir->body, 90) }}</p>
                            </div>
                            <form method="post" action="{{ route('admin.directives.destroy', $dir) }}" onsubmit="return confirm('Remove directive {{ $dir->title }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="border border-red-500/40 px-2 py-1 text-[10px] text-red-300 hover:border-red-400 hover:bg-red-950/40">
                                    DELETE
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-silver-500 text-center py-2">No directives configured.</p>
                    @endforelse
                </div>

                <form method="post" action="{{ route('admin.directives.store') }}" class="grid gap-2 border-t border-silver-800 pt-3">
                    @csrf
                    <p class="text-[10px] font-mono text-silver-400">+ ADD NEW DIRECTIVE</p>
                    <input name="pillar" placeholder="Pillar name (e.g. PAGTIPIG)" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <input name="title" required placeholder="Directive title" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <textarea name="body" required placeholder="Directive text..." rows="2" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white"></textarea>
                    <button class="border border-silver-500 p-2 text-silver-200 hover:border-white hover:text-white">ADD DIRECTIVE</button>
                </form>
            </section>

            {{-- Dynamic Roles --}}
            <section class="border border-silver-700 bg-pitch-900 p-6 text-xs">
                <h2 class="mb-4 font-oswald text-2xl uppercase text-white">Dynamic roles</h2>
                <form method="post" action="{{ route('admin.roles.store') }}" class="grid gap-2">
                    @csrf
                    <input name="name" required placeholder="internal-name (e.g. analyst)" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <input name="label" required placeholder="Display title (e.g. Intel Analyst)" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <input name="clearance" type="number" min="1" max="5" value="1" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <input name="permissions" placeholder="posts.approve,users.verify" class="border border-silver-700 bg-pitch-950 p-2 text-white outline-none focus:border-white">
                    <button class="border border-silver-500 p-2 text-silver-200 hover:border-white hover:text-white">CREATE ROLE</button>
                </form>
                <div class="mt-4 space-y-1 text-silver-400">
                    @foreach($roles as $role)
                        <p class="font-mono">{{ $role->label }} <span class="text-silver-600">({{ $role->name }} - Tier {{ $role->clearance }})</span></p>
                    @endforeach
                </div>
            </section>

        </div>
    </div>
</main>

@push('scripts')
<script>
const memberSearch = document.getElementById('memberSearch');
const memberResults = document.getElementById('memberResults');
const identityAssignment = document.getElementById('identityAssignment');
const selectedMember = document.getElementById('selectedMember');
let searchTimer;

if (memberSearch) {
    memberSearch.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = memberSearch.value.trim();
        memberResults.innerHTML = '';
        if (query.length < 2) return;
        searchTimer = setTimeout(() => {
            fetch('{{ route('admin.users.search') }}?q=' + encodeURIComponent(query), {
                headers: { Accept: 'application/json' }
            })
            .then(response => response.json())
            .then(users => {
                memberResults.innerHTML = users.map(user => `
                    <button type="button" class="block w-full border border-silver-800 bg-pitch-950 p-2 text-left text-xs hover:border-white text-silver-300" data-id="${user.id}" data-name="${user.name}" data-citizen="${user.citizen_number||''}">
                        <strong class="text-white">${user.name}</strong> // ${user.citizen_number || user.email}
                    </button>
                `).join('');
                memberResults.querySelectorAll('button').forEach(button => {
                    button.onclick = () => {
                        identityAssignment.action = '{{ url('/admin/users') }}/' + button.dataset.id + '/identity';
                        selectedMember.textContent = 'Selected: ' + button.dataset.name + ' // ' + button.dataset.citizen;
                        memberResults.innerHTML = '';
                        memberSearch.value = button.dataset.name;
                    };
                });
            })
            .catch(() => {
                memberResults.innerHTML = '<p class="text-red-300">Member search unavailable.</p>';
            });
        }, 250);
    });
}
</script>
@endpush
@endsection
