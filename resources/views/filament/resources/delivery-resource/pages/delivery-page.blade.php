<x-filament::page>
 
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Notification Area -->
<div id="notification" class="hidden p-4 rounded-md text-sm font-medium mb-4"></div>

            <x-filament::card>
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Create New Delivery</h2>
    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client Name</label>
                            <input type="text" id="client_name" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Date</label>
                            <input type="date" id="delivery_date" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Car Number</label>
                            <input type="text" id="car_number" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
    
                    <x-filament::card>
                        <div class="space-y-4">
                            <div class="flex gap-2">
                                <x-filament::button type="button" id="start-scanner" icon="heroicon-o-qr-code">Start Scanner</x-filament::button>
                                <x-filament::button type="button" color="danger" id="stop-scanner" class="hidden" icon="heroicon-o-stop">Stop Scanner</x-filament::button>
                            </div>
                            <div id="scanner-container" class="w-full max-w-md mx-auto"></div>
                        </div>
                    </x-filament::card>
    
                    <x-filament::card>
                        <div class="overflow-x-auto rounded-lg border shadow-sm dark:border-gray-700">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3">QR Code</th>
                                        <th class="px-4 py-3">Product Details</th>
                                        <th class="px-4 py-3">Quantity</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="scanned-packages"></tbody>
                            </table>
                        </div>
                    </x-filament::card>
    
                    <div class="flex justify-end">
                        <x-filament::button type="button" id="submit-delivery" icon="heroicon-o-truck">Create Delivery</x-filament::button>
                    </div>
                </div>
            </x-filament::card>
        </div>
    
    
    

        @push('scripts')
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            class DeliveryScanner {
                constructor() {
                    this.scanner = null;
                    this.packages = new Map();
                    this.scannedSet = new Set();
                    this.initializeListeners();
                }
        
                initializeListeners() {
                    document.getElementById('start-scanner').addEventListener('click', () => this.start());
                    document.getElementById('stop-scanner').addEventListener('click', () => this.stop());
                    document.getElementById('submit-delivery').addEventListener('click', () => this.submit());
                }
        
                async start() {
                    try {
                        this.scanner = new Html5Qrcode('scanner-container');
                        await this.scanner.start(
                            { facingMode: "environment" }, 
                            { fps: 10, qrbox: 250 },
                            qrCode => this.handleScan(qrCode)
                        );
                        this.toggleScannerUI(true);
                    } catch (error) {
                        this.showError(error.message);
                    }
                }
        
                async stop() {
                    if (this.scanner) {
                        await this.scanner.stop();
                        this.toggleScannerUI(false);
                    }
                }
        
                async handleScan(qrCode) {
                    if (this.scannedSet.has(qrCode)) {
                        this.showError('Package already scanned');
                        return;
                    }
        
                    this.scannedSet.add(qrCode); // block immediately
        
                    try {
                        const response = await fetch(`/admin/packages/${qrCode}`);
                        if (!response.ok) throw new Error('Package not found');
                        
                        const pkg = await response.json();
                        this.packages.set(qrCode, pkg);
                        this.addPackageToTable(pkg);
                        this.showSuccess('Package scanned successfully');
                    } catch (error) {
                        this.showError(error.message);
                        this.scannedSet.delete(qrCode); // allow retry if fetch fails
                    }
                }
        
                addPackageToTable(pkg) {
                    const tbody = document.getElementById('scanned-packages');
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-2">${pkg.qr_code}</td>
                        <td class="px-4 py-2">${pkg.product.code_article} - ${pkg.product.color} / ${pkg.product.size}</td>
                        <td class="px-4 py-2">${pkg.quantity}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                ${pkg.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${pkg.status}
                            </span>
                        </td>
                        <td class="px-4 py-2">${pkg.created_at}</td>
                        <td class="px-4 py-2">
                            <button onclick="scanner.remove('${pkg.qr_code}')" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                Remove
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                }
        
                remove(qrCode) {
                    this.packages.delete(qrCode);
                    this.scannedSet.delete(qrCode);
                    this.updateTable();
                }
        
                updateTable() {
                    const tbody = document.getElementById('scanned-packages');
                    tbody.innerHTML = '';
                    this.packages.forEach(pkg => this.addPackageToTable(pkg));
                }
        
                toggleScannerUI(isScanning) {
                    document.getElementById('start-scanner').classList.toggle('hidden', isScanning);
                    document.getElementById('stop-scanner').classList.toggle('hidden', !isScanning);
                }
        
                async submit() {
                    const formData = {
                        client_name: document.getElementById('client_name').value,
                        delivery_date: document.getElementById('delivery_date').value,
                        car_number: document.getElementById('car_number').value,
                        packages: Array.from(this.packages.keys())
                    };
        
                    try {
                        const response = await fetch('/admin/deliveries', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(formData)
                        });
        
                        if (!response.ok) throw new Error(await response.text());
                        window.location.href = '/admin/deliveries';
                    } catch (error) {
                        this.showError(error.message);
                    }
                }
        
                showNotification(message, type) {
                    const el = document.getElementById('notification');
                    el.textContent = message;
                    el.className = `p-4 rounded-md text-sm font-medium mb-4 ${type === 'success' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'}`;
                    el.classList.remove('hidden');
        
                    clearTimeout(this.notificationTimeout);
                    this.notificationTimeout = setTimeout(() => {
                        el.classList.add('hidden');
                    }, 3000);
                }
        
                showError(message) {
                    this.showNotification(`Error: ${message}`, 'error');
                }
        
                showSuccess(message) {
                    this.showNotification(message, 'success');
                }
            }
        
            // Initialize scanner
            const scanner = new DeliveryScanner();
        </script>
        @endpush
        
</x-filament::page>