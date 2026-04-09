<x-filament::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="col-span-1">
            <x-filament::section heading="Upload Attachment">
                <form wire:submit.prevent="upload" class="space-y-3">
                    {{ $this->form }}
                    <x-filament::button type="submit">Upload</x-filament::button>
                </form>
            </x-filament::section>
        </div>
        <div class="lg:col-span-2">
            <x-filament::section heading="Recent Attachments">
                <div class="divide-y divide-gray-800">
                    @forelse($attachments as $file)
                        <div class="py-2 flex justify-between text-sm text-slate-200">
                            <div>
                                <div class="font-semibold">{{ $file['name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $file['related_type'] }} #{{ $file['related_id'] }} • {{ $file['mime_type'] ?? '' }}</div>
                                <div class="text-xs">
                                    <a class="text-primary-400 hover:underline" href="{{ Storage::url($file['path']) }}" target="_blank">Download</a>
                                </div>
                            </div>
                            <div class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($file['created_at'])->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-slate-500">No files yet.</div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament::page>
