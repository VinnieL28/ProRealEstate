<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Filter Bar --}}
        <div class="flex flex-wrap gap-2">
            @foreach(['all' => 'All', 'note' => 'Notes', 'call' => 'Calls', 'email' => 'Emails', 'meeting' => 'Meetings', 'task' => 'Tasks'] as $key => $label)
                <button
                    wire:click="setFilter('{{ $key }}')"
                    class="px-3 py-1 rounded-full text-sm font-medium transition-colors
                        {{ $filter === $key
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Feed --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
            @forelse($activities as $activity)
                <div class="flex items-start gap-4 px-5 py-4 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ match($activity['type']) {
                                'call'    => 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
                                'email'   => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
                                'meeting' => 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400',
                                'task'    => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
                                default   => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                            } }}">
                            @switch($activity['type'])
                                @case('call')    <x-heroicon-o-phone class="w-4 h-4" /> @break
                                @case('email')   <x-heroicon-o-envelope class="w-4 h-4" /> @break
                                @case('meeting') <x-heroicon-o-calendar-days class="w-4 h-4" /> @break
                                @case('task')    <x-heroicon-o-clipboard-document-check class="w-4 h-4" /> @break
                                @default         <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4" />
                            @endswitch
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $activity['description'] }}</p>
                        <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium">{{ $activity['user'] }}</span>
                            <span>·</span>
                            @if($activity['related_type'])
                                <span class="capitalize">{{ strtolower($activity['related_type']) }}</span>
                                <span>·</span>
                            @endif
                            <span>{{ $activity['created_at'] }}</span>
                        </div>
                    </div>

                    {{-- Type badge --}}
                    <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full capitalize
                        {{ match($activity['type']) {
                            'call'    => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                            'email'   => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                            'meeting' => 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300',
                            'task'    => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                            default   => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                        } }}">
                        {{ $activity['type'] ?? 'note' }}
                    </span>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-gray-900">
                    <x-heroicon-o-rss class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No activity yet</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Activity will appear here as your team logs calls, notes, and emails.</p>
                </div>
            @endforelse
        </div>

        {{-- Load More --}}
        @if(count($activities) >= $perPage)
            <div class="text-center">
                <button
                    wire:click="loadMore"
                    class="px-6 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                >
                    Load More
                </button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
