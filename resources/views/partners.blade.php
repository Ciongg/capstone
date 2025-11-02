<x-layouts.app>
<main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <header class="text-center mb-10">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800">Partners</h1>
        <p class="text-gray-600 mt-2">Our partners who make Formigo possible.</p>
    </header>

    <style>
        /* Multi-line clamp without Tailwind plugin */
        .clamp-6 {
            display: -webkit-box;
            -webkit-line-clamp: 6; /* adjust lines as needed */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    @php
        $merchants = \App\Models\Merchant::select('id','name','logo_path','description','email','contact_number','partner_type')->orderBy('name')->get();
    @endphp

    @if($merchants->isEmpty())
        <div class="text-center text-gray-400">No partners yet.</div>
    @else
        <section>
            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($merchants as $m)
                    <li
                        x-data="{
                            expanded: false,
                            canExpand: false,
                            recalc() {
                                this.$nextTick(() => {
                                    const el = this.$refs.desc;
                                    if (!el) return;
                                    this.canExpand = el.scrollHeight > el.clientHeight + 1;
                                });
                            }
                        }"
                        x-init="recalc(); window.addEventListener('resize', () => recalc())"
                        class="bg-white rounded-xl shadow p-5 flex gap-4"
                    >
                        <div class="flex-shrink-0 flex items-start justify-center">
                            @if($m->logo_path)
                                <img
                                    src="{{ asset('storage/' . $m->logo_path) }}"
                                    alt="{{ $m->name }} logo"
                                    class="h-16 w-16 object-contain"
                                />
                            @else
                                <div class="h-16 w-16 bg-gray-100 text-gray-600 rounded flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(mb_substr($m->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-gray-800 truncate" title="{{ $m->name }}">{{ $m->name }}</h3>

                            @if($m->partner_type)
                                <div class="mt-1 text-xs italic text-gray-500">
                                    {{ $m->partner_type }}
                                </div>
                            @endif

                            <!-- MOVED: Contact info above description -->
                            <div class="mt-3 space-y-1">
                                @if($m->email)
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Email:</span>
                                        <a href="mailto:{{ $m->email }}" class="text-[#03b8ff] hover:underline break-words">{{ $m->email }}</a>
                                    </p>
                                @endif
                                @if($m->contact_number)
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Contact:</span>
                                        <span class="break-words">{{ $m->contact_number }}</span>
                                    </p>
                                @endif
                            </div>

                            @if($m->description)
                                <p x-ref="desc"
                                   :class="expanded ? '' : 'clamp-6'"
                                   class="text-sm text-gray-700 mt-2 break-words whitespace-pre-line transition-all">{{ $m->description }}</p>
                                <button
                                    x-show="canExpand || expanded"
                                    @click="expanded = !expanded; recalc()"
                                    class="mt-2 text-xs font-semibold text-[#03b8ff] hover:underline focus:outline-none"
                                >
                                    <span x-text="expanded ? 'See less' : 'See more'"></span>
                                </button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</main>
</x-layouts.app>
