<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Pending Invitations</h2>
            @if(count($pendingInvitations) === 0)
                <p class="text-sm text-gray-400 mt-2">No pending invitations.</p>
            @else
                <div class="mt-3 divide-y divide-gray-200 dark:divide-gray-700 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    @foreach($pendingInvitations as $inv)
                        <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800">
                            <div>
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ $inv['email'] }}</div>
                                <div class="text-xs text-gray-400">
                                    Role: {{ ucwords(str_replace('_', ' ', $inv['role'])) }} &middot;
                                    Expires: {{ \Carbon\Carbon::parse($inv['expires_at'])->diffForHumans() }}
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <p class="text-xs text-gray-400">
            Invitations expire after 7 days. Members will receive an email with a signup link.
        </p>
    </div>
</x-filament-panels::page>
