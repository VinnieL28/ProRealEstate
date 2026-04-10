<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-clock">
        <x-slot name="heading">
            <span class="flex items-center gap-2 font-semibold">
                Activity Timeline
                <span class="text-xs font-normal text-gray-500 ml-1">— all calls, SMS, emails, tasks & stage changes</span>
            </span>
        </x-slot>

        @if(count($events) === 0)
            <div class="py-10 text-center text-gray-500">
                <x-heroicon-o-clock class="w-10 h-10 mx-auto mb-2 opacity-30" />
                <p class="text-sm">No activity recorded for this lead yet.</p>
            </div>
        @else
            <ol class="relative border-l border-gray-700 ml-3 space-y-0">
                @foreach($events as $event)
                    @php
                        $colorMap = [
                            'blue'   => 'bg-blue-900/60 text-blue-300 ring-blue-700',
                            'green'  => 'bg-green-900/60 text-green-300 ring-green-700',
                            'emerald'=> 'bg-emerald-900/60 text-emerald-300 ring-emerald-700',
                            'violet' => 'bg-violet-900/60 text-violet-300 ring-violet-700',
                            'amber'  => 'bg-amber-900/60 text-amber-300 ring-amber-700',
                            'sky'    => 'bg-sky-900/60 text-sky-300 ring-sky-700',
                            'orange' => 'bg-orange-900/60 text-orange-300 ring-orange-700',
                            'indigo' => 'bg-indigo-900/60 text-indigo-300 ring-indigo-700',
                            'teal'   => 'bg-teal-900/60 text-teal-300 ring-teal-700',
                            'yellow' => 'bg-yellow-900/60 text-yellow-300 ring-yellow-700',
                            'gray'   => 'bg-gray-700 text-gray-300 ring-gray-600',
                        ];
                        $badgeClass = $colorMap[$event['color']] ?? $colorMap['gray'];

                        $dotMap = [
                            'blue'   => 'bg-blue-500',
                            'green'  => 'bg-green-500',
                            'emerald'=> 'bg-emerald-500',
                            'violet' => 'bg-violet-500',
                            'amber'  => 'bg-amber-500',
                            'sky'    => 'bg-sky-500',
                            'orange' => 'bg-orange-500',
                            'indigo' => 'bg-indigo-500',
                            'teal'   => 'bg-teal-500',
                            'yellow' => 'bg-yellow-500',
                            'gray'   => 'bg-gray-500',
                        ];
                        $dotClass = $dotMap[$event['color']] ?? 'bg-gray-500';
                    @endphp

                    <li class="mb-6 ml-6">
                        {{-- Timeline dot --}}
                        <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full ring-2 ring-gray-900 {{ $dotClass }}">
                            @svg($event['icon'], 'w-3 h-3 text-white')
                        </span>

                        {{-- Card --}}
                        <div class="p-3 rounded-lg border border-gray-700/60 bg-gray-900/50 hover:bg-gray-800/40 transition">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $badgeClass }}">
                                    @svg($event['icon'], 'w-3 h-3')
                                    {{ $event['title'] }}
                                </span>
                                <span class="text-xs text-gray-500 ml-auto" title="{{ $event['at_fmt'] }}">
                                    {{ $event['at_human'] }}
                                </span>
                            </div>

                            @if($event['body'])
                                <p class="text-sm text-gray-300 leading-relaxed mt-1 whitespace-pre-line">{{ $event['body'] }}</p>
                            @endif

                            <p class="text-xs text-gray-500 mt-1.5">
                                <span class="text-gray-600">by</span>
                                <span class="text-gray-400 font-medium">{{ $event['user'] }}</span>
                                &nbsp;·&nbsp;
                                <span>{{ $event['at_fmt'] }}</span>
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if(count($events) >= $perPage)
                <div class="mt-4 text-center">
                    <button
                        wire:click="loadMore"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 text-sm transition"
                    >
                        <x-heroicon-o-arrow-down class="w-4 h-4" />
                        Load more events
                    </button>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
