/**
 * POS Barcode Scanner - IMPROVED VERSION
 * Camera-based barcode scanning using QuaggaJS
 */

(function() {
    'use strict';

    const scannerModal = document.getElementById('barcode-scanner-modal');
    const scannerBtn = document.getElementById('open-scanner-btn');
    const closeScannerBtn = document.getElementById('close-scanner-btn');
    const scannerViewport = document.getElementById('scanner-viewport');
    const scannerStatus = document.getElementById('scanner-status');
    const manualBarcodeInput = document.getElementById('manual-barcode-input');
    const manualBarcodeBtn = document.getElementById('manual-barcode-btn');
    
    let isScanning = false;
    let lastScannedCode = null;
    let lastScanTime = 0;
    let scanAttempts = 0;

    if (!scannerBtn || !scannerModal) {
        console.warn('Barcode scanner elements not found');
        return;
    }

    // Initialize Quagga with FASTER settings
    function initScanner() {
        if (typeof Quagga === 'undefined') {
            console.error('QuaggaJS library not loaded');
            updateStatus('Library barcode scanner tidak ditemukan', 'danger');
            return;
        }

        updateStatus('Memulai kamera...', 'info');

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: scannerViewport,
                constraints: {
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    facingMode: "environment", // Use back camera
                    aspectRatio: { min: 1, max: 2 }
                },
                area: { // Smaller scan area = faster processing
                    top: "30%",
                    right: "15%",
                    left: "15%",
                    bottom: "30%"
                },
                singleChannel: false // Use color for better detection
            },
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "upc_reader",
                    "upc_e_reader"
                ],
                debug: {
                    drawBoundingBox: false, // Disable for speed
                    showFrequency: false,
                    drawScanline: false,
                    showPattern: false
                },
                multiple: false
            },
            locate: true,
            locator: {
                patchSize: "small", // Smaller = faster
                halfSample: true
            },
            numOfWorkers: navigator.hardwareConcurrency || 4,
            frequency: 20, // INCREASED to 20 scans per second
            debug: false
        }, function(err) {
            if (err) {
                console.error('Quagga initialization error:', err);
                updateStatus('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.', 'danger');
                return;
            }
            
            console.log("Quagga initialization finished. Ready to start");
            Quagga.start();
            isScanning = true;
            updateStatus('Arahkan kamera ke barcode produk. Pastikan cahaya cukup terang.', 'success');
        });

        // Listen for barcode detection with validation
        Quagga.onProcessed(onProcessed);
        Quagga.onDetected(onBarcodeDetected);
    }

    // Draw detection boxes on canvas
    function onProcessed(result) {
        const drawingCtx = Quagga.canvas.ctx.overlay;
        const drawingCanvas = Quagga.canvas.dom.overlay;

        if (result) {
            if (result.boxes) {
                drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), 
                                          parseInt(drawingCanvas.getAttribute("height")));
                
                // Draw all detection boxes
                result.boxes.filter(function (box) {
                    return box !== result.box;
                }).forEach(function (box) {
                    Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {
                        color: "green", 
                        lineWidth: 2
                    });
                });
            }

            // Draw the final detection box
            if (result.box) {
                Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {
                    color: "#00F", 
                    lineWidth: 2
                });
            }

            // Draw the barcode line
            if (result.codeResult && result.codeResult.code) {
                Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {
                    color: 'red', 
                    lineWidth: 3
                });
            }
        }
    }

    // Stop scanner
    function stopScanner() {
        if (typeof Quagga !== 'undefined' && isScanning) {
            Quagga.stop();
            Quagga.offProcessed(onProcessed);
            Quagga.offDetected(onBarcodeDetected);
            isScanning = false;
            scanAttempts = 0;
            console.log('Scanner stopped');
        }
    }

    // Handle barcode detection with improved validation
    function onBarcodeDetected(result) {
        const code = result.codeResult.code;
        const currentTime = Date.now();

        // Validate barcode quality
        if (!validateBarcode(result)) {
            scanAttempts++;
            if (scanAttempts % 20 === 0) {
                updateStatus('Barcode tidak jelas. Coba dekatkan atau jauhkan kamera.', 'warning');
            }
            return;
        }

        // Prevent duplicate scans (debounce 2 seconds)
        if (code === lastScannedCode && (currentTime - lastScanTime) < 2000) {
            return;
        }

        lastScannedCode = code;
        lastScanTime = currentTime;
        scanAttempts = 0;

        console.log('Barcode detected:', code);
        updateStatus(`✓ Barcode terdeteksi: ${code}`, 'success');

        // Play beep sound
        playBeep();

        // Stop scanner temporarily to prevent multiple scans
        Quagga.pause();

        // Add to cart
        addProductByBarcode(code);
    }

    // Validate barcode quality
    function validateBarcode(result) {
        // Check if we have a valid result
        if (!result || !result.codeResult) {
            return false;
        }

        const code = result.codeResult.code;
        
        // Basic validation - code should not be empty and should have reasonable length
        if (!code || code.length < 4 || code.length > 20) {
            return false;
        }

        // Check barcode format strength (error correction)
        const err = result.codeResult.decodedCodes
            .filter(_ => _.error !== undefined)
            .map(_ => _.error);
        
        const maxErr = Math.max.apply(null, err);
        
        // Only accept barcodes with good quality (error rate < 0.1)
        if (maxErr > 0.1) {
            return false;
        }

        return true;
    }

    // Add product to cart by barcode
    function addProductByBarcode(barcode) {
        updateStatus('Menambahkan produk ke keranjang...', 'info');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        fetch('/pos/add-by-barcode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStatus(`✓ ${data.message}`, 'success');
                
                // Show product info
                showProductInfo(data.product);
                
                // Reload page after 2 seconds to update cart
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                updateStatus(`✗ ${data.message}`, 'danger');
                // Resume scanning after error
                setTimeout(() => {
                    if (isScanning) {
                        Quagga.start();
                        updateStatus('Siap memindai barcode berikutnya', 'info');
                    }
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            updateStatus('Terjadi kesalahan saat menambahkan produk', 'danger');
            // Resume scanning after error
            setTimeout(() => {
                if (isScanning) {
                    Quagga.start();
                    updateStatus('Siap memindai barcode berikutnya', 'info');
                }
            }, 2000);
        });
    }

    // Update scanner status message
    function updateStatus(message, type = 'info') {
        if (!scannerStatus) return;

        const bgClass = {
            'info': 'bg-info',
            'success': 'bg-success',
            'danger': 'bg-danger',
            'warning': 'bg-warning'
        };

        scannerStatus.className = `alert text-white ${bgClass[type] || 'bg-info'}`;
        scannerStatus.textContent = message;
        scannerStatus.style.display = 'block';
    }

    // Show product info
    function showProductInfo(product) {
        const infoHtml = `
            <div class="mt-2 p-2 border rounded bg-light">
                <strong>${product.name}</strong><br>
                <small>Kode: ${product.code} | Harga: Rp ${formatPrice(product.price)}</small>
            </div>
        `;
        
        const infoContainer = document.getElementById('product-info');
        if (infoContainer) {
            infoContainer.innerHTML = infoHtml;
        }
    }

    // Format price
    function formatPrice(price) {
        return new Intl.NumberFormat('id-ID').format(price);
    }

    // Play beep sound
    function playBeep() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.3;

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            console.log('Audio not supported');
        }
    }

    // Event listeners
    if (scannerBtn) {
        scannerBtn.addEventListener('click', function() {
            $(scannerModal).modal('show');
            setTimeout(() => {
                initScanner();
            }, 500);
        });
    }

    if (closeScannerBtn) {
        closeScannerBtn.addEventListener('click', function() {
            stopScanner();
            $(scannerModal).modal('hide');
        });
    }

    // Stop scanner when modal is closed
    $(scannerModal).on('hidden.bs.modal', function () {
        stopScanner();
    });

    // Manual barcode input
    if (manualBarcodeBtn && manualBarcodeInput) {
        manualBarcodeBtn.addEventListener('click', function() {
            const barcode = manualBarcodeInput.value.trim();
            if (barcode) {
                addProductByBarcode(barcode);
                manualBarcodeInput.value = '';
            }
        });

        manualBarcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                manualBarcodeBtn.click();
            }
        });
    }
})();