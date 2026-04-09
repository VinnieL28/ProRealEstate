<x-filament::page>
    @foreach($this->getWidgets() as $widget)
        @livewire($widget)
    @endforeach
</x-filament::page>
