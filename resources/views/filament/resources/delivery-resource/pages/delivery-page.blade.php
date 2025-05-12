<x-filament::page>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div id="message-container" class="mb-4"></div>

    <h1 class="text-2xl font-bold mb-6 text-center">Create New Delivery</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Client Name
            </label>
            <input type="text" id="client_name"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Delivery Date
            </label>
            <input type="date" id="delivery_date"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Car Number
            </label>
            <input type="text" name="car_number" required
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-primary-500 focus:ring-primary-500">
        </div>
    </div>

    <div class="border rounded-lg p-4 dark:border-gray-700 mb-6">
        <div class="flex gap-4 mb-4">
            <button type="button" onclick="startScanner()"
                    class="filament-button inline-flex items-center justify-center py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
                Start QR Scanner
            </button>
            <button type="button" onclick="stopScanner()"
                    class="filament-button inline-flex items-center justify-center py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded-md">
                Stop Scanner
            </button>
        </div>
        <div id="qr-scanner" class="w-full max-w-md mx-auto mb-6"></div>
    </div>

    <div class="overflow-x-auto rounded-lg border dark:border-gray-700 mb-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Product Code</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Batch Number</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody id="packages-list" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                </tbody>
        </table>
    </div>

    <div class="flex justify-end gap-4">
        <button type="button" onclick="submitDelivery()"
                class="filament-button inline-flex items-center justify-center py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
            Submit Delivery
        </button>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    let html5QrCode = null;
    let scannedPackages = [];
    let isProcessing = false;
    let isScanning = false;

    // Scanner configuration
    const scannerConfig = {
        fps: 5, // Reduced from 10 to 5 for better performance
        qrbox: 200, // Smaller scanning area
        aspectRatio: 1.0, // Square aspect ratio for better QR detection
        disableFlip: true,
        videoConstraints: {
            width: { ideal: 1280 }, // Limit resolution
            height: { ideal: 720 },
            facingMode: "environment"
        }
    };

    // Error whitelist for console
    const IGNORED_ERRORS = [
        'No MultiFormat Readers',
        'NotFoundException',
        'Could not access camera'
    ];

    $(document).ready(function() {
        $('#start-scanner').click(startScanner);
        $('#stop-scanner').click(stopScanner);
    });

        // Initialize CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // No need to attach click listeners here as the onclick is directly in the buttons
        });

        function startScanner() {
        Html5Qrcode.getCameras().then(devices => {
            if (devices.length === 0) {
                showMessage('No cameras found', 'error');
                return;
            }

            html5QrCode = new Html5Qrcode("qr-scanner");
            html5QrCode.start(
                devices[0].id, 
                { 
                    fps: 10,
                    qrbox: 250,
                    aspectRatio: 1.777,
                    disableFlip: true // Improve performance
                },
                decodedText => handleScannedPackage(decodedText),
                errorMessage => {
                    // Suppress common multi-format reader errors
                    if (!errorMessage.includes('No MultiFormat Readers')) {
                        console.error('Scanner error:', errorMessage);
                    }
                }
            ).catch(err => {
                showMessage(`Camera error: ${err}`, 'error');
            });
        });
    }

    async function stopScanner() {
        if (!html5QrCode || !isScanning) return;
        
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
            $('#qr-scanner').hide();
            showMessage('Scanner stopped', 'info');
        } catch (err) {
            console.error('Error stopping scanner:', err);
        } finally {
            isScanning = false;
            toggleScannerButtons(false);
            html5QrCode = null;
        }
    }

    function handleScannerError(errorMessage) {
        if (!IGNORED_ERRORS.some(msg => errorMessage.includes(msg))) {
            console.warn('Scanner warning:', errorMessage);
        }
    }

    async function handleScannedPackage(qrCode) {
        if (isProcessing) return;
        isProcessing = true;

        try {
            if (scannedPackages.some(p => p.qr_code === qrCode)) {
                showMessage('Package already scanned', 'warning');
                return;
            }

            const response = await $.post('/scan-package', { qr_code: qrCode });
            
            scannedPackages.push({
                ...response,
                qr_code: qrCode
            });
            
            updatePackagesTable();
            showMessage('Package scanned successfully', 'success');
        } catch (error) {
            const message = error.responseJSON?.message || 'Error scanning package';
            showMessage(message, 'error');
        } finally {
            isProcessing = false;
        }
    }

    function toggleScannerButtons(scannerActive) {
        $('#start-scanner').prop('disabled', scannerActive);
        $('#stop-scanner').prop('disabled', !scannerActive);
    }

        function updatePackagesTable() {
            const tbody = $('#packages-list');
            tbody.empty();

            scannedPackages.forEach(pkg => {
                const row = `
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${pkg.product_code}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${pkg.quantity}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${pkg.batch_number}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">${pkg.status}</td>
                        <td class="px-4 py-3 text-sm">
                            <button onclick="removePackage('${pkg.qr_code}')"
                                    class="text-red-600 hover:text-red-900">
                                Remove
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        async function removePackage(qrCode) {
            try {
                const packageToRemove = scannedPackages.find(p => p.qr_code === qrCode);
                if (packageToRemove && packageToRemove.id) {
                    await $.ajax({
                        url: `/packages/${packageToRemove.id}`,
                        method: 'DELETE'
                    });
                }

                scannedPackages = scannedPackages.filter(p => p.qr_code !== qrCode);
                updatePackagesTable();
                showMessage('Package removed', 'success');
            } catch (error) {
                showMessage('Error removing package', 'error');
            }
        }

        async function submitDelivery() {
            const clientName = $('#client_name').val();
            const deliveryDate = $('#delivery_date').val();

            if (!clientName) {
                showMessage('Client Name is required', 'error');
                return;
            }

            if (!deliveryDate) {
                showMessage('Delivery Date is required', 'error');
                return;
            }

            if (scannedPackages.length === 0) {
                showMessage('Please scan at least one package', 'warning');
                return;
            }

            try {
                const response = await $.post('/deliveries', {
                    client_name: clientName,
                    delivery_date: deliveryDate,
                    packages: scannedPackages.map(pkg => ({
                        product_code: pkg.product_code,
                        quantity: pkg.quantity,
                        batch_number: pkg.batch_number,
                        // We don't need to send the 'status' or 'qr_code' for submission
                    })),
                });

                showMessage('Delivery created successfully', 'success');
                // Optionally, you can redirect the user or clear the form here
                $('#client_name').val('');
                $('#delivery_date').val('');
                scannedPackages = [];
                updatePackagesTable();

            } catch (error) {
                const message = error.responseJSON?.message || 'Error creating delivery';
                showMessage(message, 'error');
            }
        }

        function showMessage(message, type) {
            const container = $('#message-container');
            const alertClass = type === 'error' ?
                'bg-red-100 border-red-400 text-red-700' :
                'bg-green-100 border-green-400 text-green-700';

            container.html(`
                <div class="${alertClass} border px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">${message}</span>
                </div>
            `);
            // Automatically clear the message after a few seconds
            setTimeout(() => {
                container.empty();
            }, 5000);
        }
    </script>
    @endpush
</x-filament::page>