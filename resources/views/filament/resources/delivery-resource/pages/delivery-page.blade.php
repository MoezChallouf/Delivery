<x-filament::page>
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <h1 class="text-2xl font-bold mb-6 text-center">Create New Delivery</h1>

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form wire:submit.prevent="submit">
        <div class="space-y-6">
            <!-- Delivery Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Client Name
                    </label>
                    <input type="text" 
                           wire:model="client_name" 
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Car Number
                    </label>
                    <input type="text" 
                           wire:model="car_number" 
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Date
                    </label>
                    <input type="date" 
                           wire:model="delivery_date" 
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <!-- QR Scanner Section -->
            <div class="border rounded-lg p-4 dark:border-gray-700">
                <div class="mb-4">
                    <button type="button" 
                            @click="startScanner()"
                            class="filament-button inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                        Start QR Scanner
                    </button>
                    
                    <button type="button" 
                            @click="stopScanner()"
                            class="filament-button inline-flex items-center justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 ml-2">
                        Stop Scanner
                    </button>
                </div>

                <div id="qr-scanner" class="w-full max-w-md mx-auto mb-6"></div>

                <!-- Scanned Packages Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Package ID</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Product Code</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scannedPackages as $package)
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $package['id'] }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $package['product_code'] }}</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $package['quantity'] }}</td>
                                <td class="px-4 py-2 text-green-600 dark:text-green-400">✓ Confirmed</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" 
                    class="filament-button inline-flex items-center justify-center py-3 px-6 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                Create Delivery
            </button>
        </div>
    </form>

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            let html5QrCode = null;

            function startScanner() {
                html5QrCode = new Html5Qrcode("qr-scanner");
                
                html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: 250
                    },
                    (decodedText) => {
                        Livewire.dispatch('qrScanned', { qrCode: decodedText });
                    }
                ).catch((err) => {
                    console.error(err);
                });
            }

            function stopScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().catch((err) => {
                        console.error(err);
                    });
                }
            }

            document.addEventListener('livewire:initialized', () => {
                Livewire.on('duplicatePackage', (packageId) => {
                    alert(`Package ${packageId} already scanned!`);
                });
            });
        </script>
    @endpush
</x-filament::page>