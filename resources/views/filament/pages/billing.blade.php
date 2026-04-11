<x-filament::page>
    <div class="space-y-8">
        {{-- Current status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Current Status</h3>

            @if($currentSubscription)
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        Active — {{ ucfirst($currentSubscription['plan']) }}
                    </span>
                    @if($currentSubscription['cancelled'])
                        <span class="text-sm text-gray-500">Cancels: {{ $currentSubscription['ends_at'] }}</span>
                    @else
                        <span class="text-sm text-gray-500">Renews: {{ $currentSubscription['renews_at'] }}</span>
                    @endif
                    <button wire:click="cancelSubscription"
                            onclick="return confirm('Cancel your subscription?')"
                            class="text-sm text-red-500 hover:underline">
                        Cancel Subscription
                    </button>
                </div>
            @elseif($onTrial)
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        Free Trial
                    </span>
                    <span class="text-sm text-gray-500">Expires: {{ $trialEndsAt }}</span>
                </div>
            @else
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        No Active Subscription
                    </span>
                    <span class="text-sm text-gray-500">Please subscribe to continue using the CRM.</span>
                </div>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 text-sm">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $userCount }}</div>
                    <div class="text-gray-500">Team Members</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $leadCount }}</div>
                    <div class="text-gray-500">Total Leads</div>
                </div>
            </div>
        </div>

        {{-- Plan cards --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Choose a Plan</h3>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach($plans as $key => $plan)
                    @php
                        $isCurrent = ($team?->subscription_plan === $key);
                    @endphp
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl border
                        {{ $key === 'pro' ? 'border-amber-400 ring-2 ring-amber-400' : 'border-gray-200 dark:border-gray-700' }}
                        p-6 flex flex-col">

                        @if($key === 'pro')
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
                        @endif

                        <div class="text-xl font-bold text-gray-800 dark:text-white">{{ $plan['name'] }}</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            ${{ $plan['price'] }}<span class="text-base font-normal text-gray-500">/mo</span>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300 flex-1">
                            <li>✓ Up to {{ $plan['agents'] === 999 ? 'Unlimited' : $plan['agents'] }} agents</li>
                            <li>✓ Up to {{ $plan['leads'] === 999999 ? 'Unlimited' : number_format($plan['leads']) }} leads</li>
                            <li>✓ Twilio SMS &amp; Calling</li>
                            <li>✓ Gmail Integration</li>
                            <li>✓ Document Generation</li>
                            @if($key !== 'starter')
                                <li>✓ Advanced Analytics</li>
                            @endif
                            @if($key === 'enterprise')
                                <li>✓ Priority Support</li>
                                <li>✓ Custom Integrations</li>
                            @endif
                        </ul>

                        <button
                            wire:click="subscribe('{{ $key }}')"
                            {{ $isCurrent ? 'disabled' : '' }}
                            class="mt-6 w-full py-2 rounded-lg font-semibold text-sm
                                {{ $isCurrent
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : ($key === 'pro'
                                        ? 'bg-amber-500 text-white hover:bg-amber-600'
                                        : 'bg-gray-800 text-white hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500') }}">
                            {{ $isCurrent ? 'Current Plan' : 'Subscribe' }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="text-xs text-gray-400">
            Payments processed by Stripe. Cancel anytime. All prices in USD.
        </p>
    </div>
</x-filament::page>
