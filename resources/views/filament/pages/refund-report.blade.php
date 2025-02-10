<x-filament::page>
    <div class="space-x-6">
         <!-- Sidebar (Filters/Form) -->
         <div class="flex gap-6">
             <div style="width: 400px" class="shadow rounded-lg p-4">
                <h2 class="text-lg font-semibold mb-4">Filter Reports</h2>
                {{ $this->form }}
              
            </div>
            <div class="grow shadow rounded-lg p-4 overflow-auto">
                <iframe id="pdf-preview"
                src="{{ route('generate.pdf.preview', ['start_date' => $start_date, 'end_date' => $end_date, 'status' => $status, 'supplier'=> $supplier, 'customer'=> $customer]) }}"
                width="100%" height="600px">
            </iframe>
            </div>
         </div>
    </div>
   <script>
    document.addEventListener('DOMContentLoaded', function () {
        Livewire.on('filtersUpdated', (start_date, end_date, status, supplier, customer) => {
            let pdfPreview = document.getElementById('pdf-preview');
            let params = new URLSearchParams({
                start_date: start_date || '',
                end_date: end_date || '',
                status: status || 'all',
                supplier: supplier || '',
                customer: customer || ''
            }).toString();
            pdfPreview.src = "{{ route('generate.pdf.preview') }}" + "?" + params;
        });
    });

    </script>
</x-filament::page>



