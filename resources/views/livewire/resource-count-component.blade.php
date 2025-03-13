<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 cursor-pointer" style="border-left:8px solid {{$color}}">
<div class="grid " style="grid-template-columns: 2fr 1fr">
        <div >
            <h3 class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ class_basename($model) }}</h3>
            <p class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-gray-950 dark:text-white mb-2">{{ $count }}</p>
            <p class="fi-wi-stats-overview-stat-description text-sm fi-color-custom text-custom-600 dark:text-custom-400" style="color:{{$color}}">{{ $description }}</p>
        </div>
            <div class="mt-2 flex justify-end items-center">
                <div style="height:max-content">
                    {{ $this->createAction }}
                </div>
            </div>
        </div>

    <x-filament-actions::modals />
</div>
