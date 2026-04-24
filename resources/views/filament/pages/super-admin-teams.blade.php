<x-filament-panels::page>
    <div class="space-y-4">
        @if(session('super_admin_viewing_team'))
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl px-4 py-3 flex items-center justify-between">
                <span class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">
                    Viewing as team ID: {{ session('super_admin_viewing_team') }}
                </span>
                <button wire:click="clearTeamSwitch"
                        class="text-xs text-yellow-700 dark:text-yellow-300 underline hover:no-underline">
                    Exit team view
                </button>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Team</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Trial Ends</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Users / Leads / Deals</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($teams as $team)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ $team['name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $team['slug'] ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $team['owner']['name'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $team['subscription_plan'] === 'enterprise' ? 'bg-purple-100 text-purple-700' :
                                       ($team['subscription_plan'] === 'pro' ? 'bg-blue-100 text-blue-700' :
                                       ($team['subscription_plan'] === 'starter' ? 'bg-green-100 text-green-700' :
                                       'bg-yellow-100 text-yellow-700')) }}">
                                    {{ ucfirst($team['subscription_plan'] ?? 'trial') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $team['trial_ends_at'] ? \Carbon\Carbon::parse($team['trial_ends_at'])->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $team['users_count'] }} / {{ $team['leads_count'] }} / {{ $team['deals_count'] }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $team['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $team['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button wire:click="switchToTeam({{ $team['id'] }})"
                                            class="text-xs text-blue-600 hover:underline">View</button>
                                    <button wire:click="toggleTeamActive({{ $team['id'] }})"
                                            class="text-xs {{ $team['is_active'] ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                        {{ $team['is_active'] ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">No teams found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
