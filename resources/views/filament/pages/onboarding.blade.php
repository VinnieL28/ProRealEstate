<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Progress Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Setup Progress</span>
                <span class="text-sm font-bold text-amber-500">{{ $percent }}%</span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                <div class="h-2 bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>

            {{-- Step indicators --}}
            <div class="flex justify-between mt-3">
                @foreach(['Company', 'Invite', 'Twilio', 'Gmail', 'Import', 'Done'] as $i => $label)
                    <div class="flex flex-col items-center">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                            {{ isset($steps[$i + 1]) && $steps[$i + 1]
                                ? 'bg-green-500 text-white'
                                : ($currentStep === $i + 1 ? 'bg-amber-400 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-500') }}">
                            {{ isset($steps[$i + 1]) && $steps[$i + 1] ? '✓' : ($i + 1) }}
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 hidden sm:block">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

            @if($currentStep === 1)
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Step 1: Company Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Company Name</label>
                        <input type="text" wire:model.defer="company_name"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Timezone</label>
                        <select wire:model.defer="timezone"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400 outline-none">
                            <option value="America/New_York">Eastern (America/New_York)</option>
                            <option value="America/Chicago">Central (America/Chicago)</option>
                            <option value="America/Denver">Mountain (America/Denver)</option>
                            <option value="America/Los_Angeles">Pacific (America/Los_Angeles)</option>
                            <option value="Europe/London">London (Europe/London)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Currency</label>
                        <select wire:model.defer="currency"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400 outline-none">
                            <option value="USD">USD ($)</option>
                            <option value="CAD">CAD ($)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="EUR">EUR (€)</option>
                        </select>
                    </div>
                    <button wire:click="saveStep1"
                            class="w-full bg-amber-500 text-white py-2.5 rounded-lg font-semibold hover:bg-amber-600 transition-colors">
                        Save &amp; Continue →
                    </button>
                </div>

            @elseif($currentStep === 2)
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Step 2: Invite Team Members</h3>
                <p class="text-sm text-gray-500 mb-4">Add your agents, managers, and staff. You can also do this later.</p>
                <a href="{{ route('filament.admin.pages.invite-member') }}"
                   class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                    Go to Invite Page
                </a>
                <button wire:click="skipStep(2)" class="ml-3 text-sm text-gray-400 hover:underline">Skip for now →</button>

            @elseif($currentStep === 3)
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Step 3: Connect Twilio <span class="text-sm font-normal text-gray-400">(optional)</span></h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Account SID</label>
                        <input type="text" wire:model.defer="twilio_sid" placeholder="ACxxxxxxxxxx"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Auth Token</label>
                        <input type="password" wire:model.defer="twilio_token"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Twilio Phone Number</label>
                        <input type="text" wire:model.defer="twilio_phone" placeholder="+15551234567"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="saveTwilio"
                                class="flex-1 bg-amber-500 text-white py-2.5 rounded-lg font-semibold hover:bg-amber-600">
                            Save &amp; Continue →
                        </button>
                        <button wire:click="skipStep(3)" class="text-sm text-gray-400 hover:underline px-4">Skip</button>
                    </div>
                </div>

            @elseif($currentStep === 4)
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Step 4: Connect Gmail <span class="text-sm font-normal text-gray-400">(optional)</span></h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Google Client ID</label>
                        <input type="text" wire:model.defer="gmail_client_id"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Google Client Secret</label>
                        <input type="password" wire:model.defer="gmail_client_secret"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="saveGmail"
                                class="flex-1 bg-amber-500 text-white py-2.5 rounded-lg font-semibold hover:bg-amber-600">
                            Save &amp; Continue →
                        </button>
                        <button wire:click="skipStep(4)" class="text-sm text-gray-400 hover:underline px-4">Skip</button>
                    </div>
                </div>

            @elseif($currentStep === 5)
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Step 5: Import Existing Leads <span class="text-sm font-normal text-gray-400">(optional)</span></h3>
                <p class="text-sm text-gray-500 mb-4">Import leads from a CSV file to get started quickly. You can also do this later from the Leads page.</p>
                <a href="{{ route('filament.admin.resources.leads.index') }}"
                   class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                    Go to Leads → Import CSV
                </a>
                <button wire:click="skipStep(5)" class="ml-3 text-sm text-gray-400 hover:underline">Skip →</button>

            @elseif($currentStep === 6)
                <div class="text-center py-8">
                    <div class="text-5xl mb-4">🎉</div>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">You're all set!</h3>
                    <p class="text-gray-500 mb-6">Your CRM is ready to use. Start adding leads and managing your pipeline.</p>
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                       class="inline-block bg-amber-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-amber-600 transition-colors">
                        Go to Dashboard →
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-filament-panels::page>
