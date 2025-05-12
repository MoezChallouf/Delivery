{{-- resources/views/filament/forms/components/qr-scanner.blade.php --}}

<div x-data="qrScanner">
    <div id="qr-reader" class="w-full"></div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('qrScanner', () => ({
            scanner: null,
            
            init() {
                this.scanner = new Html5Qrcode('qr-reader');
                
                this.scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    this.handleScan.bind(this),
                    (error) => console.error(error)
                ).catch((err) => {
                    console.error('Error starting scanner:', err);
                });
            },
            
            handleScan(decodedText) {
                Livewire.emit('qrScanned', decodedText);
            },
            
            stopScanner() {
                if (this.scanner) {
                    this.scanner.stop().then(() => {
                        this.scanner = null;
                    }).catch(console.error);
                }
            }
        }));
    });
</script>
@endpush