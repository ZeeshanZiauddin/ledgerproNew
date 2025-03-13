<x-filament::widget >
        <div class="grid grid-cols-1 md:grid-cols-{{$this->count}} gap-4">
            @foreach ($this->getResources() as $resource)
                <livewire:resource-count-component 
                    resource-class="{{ $resource['resource'] }}" 
                    description="{{ $resource['description'] }}" 
                    color="{{ $resource['color'] }}" 
                    theme="{{ $resource['theme'] }}" 
                />
            @endforeach
        </div>
</x-filament::widget>
