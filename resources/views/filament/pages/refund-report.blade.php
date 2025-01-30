<x-filament::page>
    <div class="flex space-x-6">
        <!-- PDF Viewer -->
            <!-- Use the route to generate PDF inline -->
            <iframe src="{{ route('filament.pages.refund-report.pdf') }}" width="100%" height="600px"></iframe>
    </div>
</x-filament::page>
