document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('barcodeForm');
    const generateRandomBtn = document.getElementById('generateRandom');
    const barcodeValueInput = document.getElementById('barcodeValue');
    const numBarcodesInput = document.getElementById('numBarcodes');
    const barcodeContainer = document.getElementById('barcodeContainer');

    function generateRandomBarcode() {
        return Math.floor(100000000000 + Math.random() * 900000000000).toString();
    }

    if (generateRandomBtn) {
        generateRandomBtn.addEventListener('click', function() {
            barcodeValueInput.value = generateRandomBarcode();
        });
    }

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const barcodeValue = barcodeValueInput.value.trim();
        const numBarcodes = parseInt(numBarcodesInput.value) || 1;
        if (!barcodeValue) {
            alert('Please enter a barcode value or generate a random one.');
            return;
        }

        barcodeContainer.innerHTML = '';

        const barcodes = [];
        for (let i = 0; i < numBarcodes; i++) {
            const barcodeId = `barcode-${i}`;
            const barcodeDiv = document.createElement('div');
            barcodeDiv.className = 'mb-3';
            barcodeDiv.innerHTML = `<svg id="${barcodeId}"></svg>`;
            barcodeContainer.appendChild(barcodeDiv);

            try {
                JsBarcode(`#${barcodeId}`, barcodeValue, {
                    format: "EAN13",
                    width: 2,
                    height: 100,
                    displayValue: true,
                    fontSize: 16,
                    background: "#ffffff",
                    lineColor: "#000000"
                });

                const svgElement = document.getElementById(barcodeId);
                // Serialize SVG and create data URL (base64)
                const svgString = new XMLSerializer().serializeToString(svgElement);
                const svgBase64 = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgString)));

                barcodes.push({
                    type: 'barcode',
                    format: 'EAN13',
                    barcodeValue: barcodeValue,
                    displayValue: true,
                    lineColor: '#000000',
                    backgroundColor: '#ffffff',
                    width: 2,
                    height: 100,
                    fontSize: 16,
                    imageData: svgBase64
                });
            } catch (error) {
                console.error('Error generating barcode:', error);
            }
        }

        if (barcodes.length > 0) {
            saveBarcodes(barcodes);
        }
    });

    function saveBarcodes(barcodes) {
        const endpoint = (window.URLROOT ? window.URLROOT : '') + '/barcode';
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ barcodes: barcodes })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Saved IDs:', data.saved_ids || data.savedIds || data.savedIds);
                alert('Barcodes added to cart successfully!');
                // Optionally refresh cart UI or open cart modal
                const cartModalEl = document.getElementById('cartModal');
                if (cartModalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(cartModalEl);
                    modal.show();
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to add barcodes to cart'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving barcodes. Please try again.');
        });
    }
});