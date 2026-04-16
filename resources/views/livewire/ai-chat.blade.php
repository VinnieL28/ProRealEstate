<div
    class="flex flex-col rounded-xl border border-gray-700 overflow-hidden bg-gray-900"
    style="height: calc(100vh - 13rem);"
    x-data="{}"
    x-init="$watch('$wire.messages', () => { $nextTick(() => { let el = $refs.chatBody; if (el) el.scrollTop = el.scrollHeight; }); })"
>
    {{-- Header --}}
    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-700 bg-gray-800 flex-shrink-0">
        <div class="flex items-center justify-center w-9 h-9 rounded-full bg-violet-600">
            <x-heroicon-o-sparkles class="w-5 h-5 text-white" />
        </div>
        <div>
            <p class="text-sm font-semibold text-white">AI Assistant</p>
            <p class="text-xs text-gray-400">Powered by Claude</p>
        </div>
    </div>

    {{-- Messages --}}
    <div
        x-ref="chatBody"
        class="flex-1 overflow-y-auto px-5 py-4 space-y-4"
    >
        @if (empty($messages))
            <div class="flex flex-col items-center justify-center h-full text-center space-y-3">
                <x-heroicon-o-sparkles class="w-10 h-10 text-violet-500 opacity-50" />
                <p class="text-gray-300 font-medium">How can I help you today?</p>
                <p class="text-gray-500 text-sm" style="max-width: 22rem;">Ask me about leads, deals, properties, tasks, or anything else in your CRM.</p>
            </div>
        @endif

        @foreach ($messages as $message)
            @if ($message['role'] === 'user')
                <div class="flex justify-end">
                    <div class="rounded-2xl px-4 py-3 bg-violet-600 text-white text-sm leading-relaxed shadow" style="max-width: 75%;">
                        {{ $message['content'] }}
                    </div>
                </div>
            @else
                <div class="flex justify-start gap-3">
                    <div class="flex-shrink-0 flex items-end">
                        <div class="w-7 h-7 rounded-full bg-gray-700 flex items-center justify-center">
                            <x-heroicon-o-sparkles class="w-4 h-4 text-violet-400" />
                        </div>
                    </div>
                    <div class="rounded-2xl px-4 py-3 bg-gray-700 text-gray-100 text-sm leading-relaxed shadow whitespace-pre-wrap" style="max-width: 75%;">
                        {{ $message['content'] }}
                    </div>
                </div>
            @endif
        @endforeach

        @if ($loading)
            <div class="flex justify-start gap-3">
                <div class="flex-shrink-0 flex items-end">
                    <div class="w-7 h-7 rounded-full bg-gray-700 flex items-center justify-center">
                        <x-heroicon-o-sparkles class="w-4 h-4 text-violet-400" />
                    </div>
                </div>
                <div class="rounded-2xl px-4 py-3 bg-gray-700 shadow">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: -0.3s;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: -0.15s;"></span>
                        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <div class="px-4 py-3 border-t border-gray-700 bg-gray-800 flex-shrink-0">
        <form wire:submit.prevent="sendMessage" class="flex items-end gap-3">
            <textarea
                wire:model="input"
                rows="1"
                placeholder="Ask anything about your CRM…"
                @keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage(); }"
                class="flex-1 resize-none bg-gray-700 border border-gray-600 text-white placeholder-gray-400 text-sm rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                style="min-height: 46px; max-height: 140px;"
                x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
            ></textarea>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex-shrink-0 w-11 h-11 flex items-center justify-center rounded-xl bg-violet-600 hover:bg-violet-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                <span wire:loading wire:target="sendMessage">
                    <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="sendMessage">
                    <x-heroicon-o-paper-airplane class="w-5 h-5 text-white" style="transform: rotate(90deg);" />
                </span>
            </button>
        </form>
        <p class="mt-2 text-center text-xs text-gray-500">Press Enter to send · Shift+Enter for new line</p>
    </div>
</div>
