<!-- HEAD -->
<?php include APPROOT . '/views/inc/head.php'; ?>

<body>
    <!-- NAVBAR -->
    <?php include APPROOT . '/views/inc/navbar.php'; ?>

    <div class="container mt-5 text-">
        <h3 class="text-center mb-2">Barcode &amp; QR Code Generator</h3>
        <p class="text-center text-white mb-3">Follow the simple steps below to create barcodes for your products.</p>

        <div class="d-flex justify-content-center mb-4">
            <a href="<?= URLROOT ?>/" class="btn btn-outline-light me-2">
                <i class="las la-qrcode me-1"></i> QR Code
            </a>

            <button type="button" class="btn btn-orange">
                <i class="las la-barcode me-1"></i> Barcode
            </button>
        </div>

        <form method="POST" autocomplete="off" id="barcodeForm">
            <?php csrf_input(); ?>
            <div class="row shadow rounded-0 dark-box mt-5 p-lg-3 p-md-1">
                <div class="col-lg-8 col-md-12 dark-box">
                    <div class="card rounded-0 my- border-0 bg-transparent">
                        <div class="card-body pt-">

                            <div class="mb-2">
                                <label for="" class="form-label mb-1">Step 1 &mdash; Enter barcode content</label>
                                <div class="input-group">
                                    <input type="text" class="form-control shadow-none border rounded-end-0"
                                        name="barcodeValue" id="barcodeValue"
                                        placeholder="Type or paste the number / text for your barcode"
                                        value="<?= $data['barcodeValue'] ?>" required>
                                    <button type="button" onclick="generateRandomNumber()"
                                        class="btn btn-green rounded-start-0"><i class="las la-random"
                                            style="font-size: 1.3rem;"></i></button>
                                </div>
                                <small class="text-muted">For UPC-A use 11 digits, for EAN-13 use 12–13 digits. Other
                                    formats can use text as allowed.</small>
                            </div>

                            <div class="mb-2">
                                <label for="format" class="form-label">Step 2 &mdash; Choose barcode type</label>
                                <select id="format" name="format" class="form-select shadow-none border">
                                    <option class="text-dark" value="CODE128" <?= $data['format'] === 'CODE128' ? 'selected' : ''; ?>>CODE-128</option>
                                    <option class="text-dark" value="EAN13" <?= $data['format'] === 'EAN13' ? 'selected' : ''; ?>>EAN-13</option>
                                    <option class="text-dark" value="UPC" <?= $data['format'] === 'UPC' ? 'selected' : ''; ?>>UPC</option>
                                    <option class="text-dark" value="CODE39" <?= $data['format'] === 'CODE39' ? 'selected' : ''; ?>>CODE-39</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label for="numBarcodes" class="form-label mb-1">Step 3 &mdash; How many copies?</label>
                                <input type="number" class="form-control shadow-none border" name="numBarcodes"
                                    id="numBarcodes" value="<?= $data['numBarcodes']; ?>" min="1" max="100" required>
                            </div>

                            <div class="mb-2">
                                <label for="imageFormat" class="form-label">Step 4 &mdash; Choose image format</label>
                                <select id="imageFormat" name="imageFormat"
                                    class="form-select shadow-none border rounded-0 text-light">
                                    <option class="text-dark" value="png" <?= $data['imageFormat'] === 'png' ? 'selected' : ''; ?>>PNG</option>
                                    <option class="text-dark" value="jpg" <?= $data['imageFormat'] === 'jpg' ? 'selected' : ''; ?>>JPG</option>
                                    <option class="text-dark" value="svg" <?= $data['imageFormat'] === 'svg' ? 'selected' : ''; ?>>SVG</option>
                                    <option class="text-dark" value="pdf" <?= $data['imageFormat'] === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                                </select>
                            </div>

                            <div class="row shadow rounded-0 dark-box mt-4 p-lg-3 p-md-1">
                                <h6 class="text-start pt-2">Customize Barcode</h6>
                                <div class="dark-box">
                                    <div class="card rounded-0 my- border-0 bg-transparent">
                                        <div class="card-body pt-">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label for="lineColor" class="form-label">Line Color:</label>
                                                    <input type="color" id="lineColor" name="lineColor"
                                                        class="form-control color-input shadow-none border"
                                                        value="<?= $data['lineColor'] ?>" required />
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="backgroundColor" class="form-label">Background
                                                        Color:</label>
                                                    <input type="color" id="backgroundColor" name="backgroundColor"
                                                        class="form-control color-input shadow-none border"
                                                        value="<?= $data['backgroundColor'] ?>" required />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label for="fontSize" class="form-label">Font Sise:</label>
                                                    <input type="number" id="fontSize" name="fontSize"
                                                        class="form-control color-input shadow-none border"
                                                        value="<?= $data['fontSize'] ?>" min="10" max="20" required />
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="height" class="form-label">Barcode Height:</label>
                                                    <input type="number" id="height" name="height"
                                                        class="form-control color-input shadow-none border"
                                                        value="<?= $data['height'] ?>" min="20" max="100" required />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label for="width" class="form-label">Line Width:</label>
                                                    <input type="number" id="width" name="width"
                                                        class="form-control shadow-none border"
                                                        value="<?= $data['width'] ?>" min="1" max="5" required />
                                                </div>
                                                <div class="col-md-6 mb-">
                                                    <label for="displayValue" class="form-label">Barcode
                                                        displayValue:</label>
                                                    <select id="displayValue" name="displayValue"
                                                        class="form-select shadow-none border">
                                                        <option class="text-dark" value="true"
                                                            <?= $data['displayValue'] === 'true' ? 'selected' : ''; ?>>Show
                                                        </option>
                                                        <option class="text-dark" value="false"
                                                            <?= $data['displayValue'] === 'false' ? 'selected' : ''; ?>>
                                                            Hide</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- <div class="mb-2">
                                                <label for="displayValue" class="form-label"></label>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-12 dark-nav d-none">
                                    <div class="card rounded-0 rounded-end border-0 px-0 dark-nav">
                                        <div class="card-body text-center px-0">
                                            <div
                                                class="row d-flex flex-colum align-items-center justify-content-center">
                                                <div class="col-md-6 col-sm-12 mb-3">
                                                    <button type="submit"
                                                        class="btn btn-green form-control rounded-0 d-flex align-items-center justify-content-center shadow-none"><i
                                                            class="las la-plus-square" style="font-size: 1.1rem;"></i>
                                                        Generate</button>
                                                </div>
                                                <div class="col-md-6 col-sm-12 mt-3">
                                                    <button type="submit" name="generateRandom"
                                                        class="btn btn-green form-control rounded-0 d-flex align-items-center justify-content-center shadow-none"><i
                                                            class="las la-plus-square"
                                                            style="font-size: 1.1rem;"></i>Random
                                                        Barcode(s)</button>
                                                </div>
                                                <div class="col-md-6 col-sm-12 mb-3">
                                                    <select id="imageFormat" name="imageFormat"
                                                        class="form-select shadow-none border rounded-0 text-light">
                                                        <option class="text-dark" value="png"
                                                            <?= $data['imageFormat'] === 'png' ? 'selected' : ''; ?>>PNG
                                                        </option>
                                                        <option class="text-dark" value="jpg"
                                                            <?= $data['imageFormat'] === 'jpg' ? 'selected' : ''; ?>>JPG
                                                        </option>
                                                        <option class="text-dark" value="svg"
                                                            <?= $data['imageFormat'] === 'svg' ? 'selected' : ''; ?>>SVG
                                                        </option>
                                                        <option class="text-dark" value="pdf"
                                                            <?= $data['imageFormat'] === 'pdf' ? 'selected' : ''; ?>>PDF
                                                        </option>
                                                    </select>

                                                </div>
                                                <div class="col-md-6 col-sm-12 mb-1">
                                                    <button type="button" id="download"
                                                        class="btn btn-green rounded-0 form-control"
                                                        onclick="saveBarcodes()">
                                                        <i class="las la-download las-sm"></i> Download Barcodes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paystack Payment Button -->
                                <!-- <div class="container mt-5">
                    <h4 class="text-center mb-2">Step 5 &mdash; Unlock and download</h4>
                    <p class="text-center text-muted mb-3">Pay securely to unlock full-size images and enable downloads for all items in your cart.</p>
                    <button id="paystackButton" class="btn btn-green form-control mt-1">Pay and unlock my barcodes</button>
                </div> -->
                            </div>

                            <div class="card-body text-center px-0">
                                <div class="row d-flex flex-colum align-items-center justify-content-center">
                                    <div class="col-md-6 col-sm-12 mb-1">
                                        <button type="button"
                                            class="btn btn-green form-control rounded-0 d-flex align-items-center justify-content-center shadow-none"
                                            id="addToCart"><i class="las la-plus-square" style="font-size: 1.1rem;"></i>
                                            Generate</button>
                                    </div>
                                    <div class="col-md-6 col-sm-12 mb-1">
                                        <button type="submit" name="generateRandom"
                                            class="btn btn-green form-control rounded-0 d-flex align-items-center justify-content-center shadow-none"><i
                                                class="las la-plus-square" style="font-size: 1.1rem;"></i>Random
                                            Barcode(s)</button>
                                    </div>

                                    <!-- <div class="col-md-6 col-sm-12 mb-1">
                                        <button type="button" id="download"
                                            class="btn btn-secondary rounded-0 form-control" disabled
                                            title="Available after payment">
                                            <i class="las la-lock las-sm"></i> Download Locked
                                        </button>
                                        <small class="text-muted d-block text-center mt-1">
                                            <i class="las la-info-circle"></i> Downloads available after checkout
                                        </small>
                                    </div> -->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <?php $previewLocked = empty($_SESSION['payment_completed']); ?>
                <div class="col-lg-4 col-md-12 sticky-top p-0 bg-light">
                    <div class="card rounded-0 rounded-end border-0 bg-light">
                        <div class="card-body position-relative">
                            <div class="preview-header mb-3">
                                <h6 class="text-center text-muted">Step 5 &mdash; Preview</h6>
                                <?php if (!empty($_SESSION['barcodeValue'])): ?>
                                    <?php if ($previewLocked): ?>
                                        <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                            <i class="las la-lock me-2"></i>
                                            <small class="mb-0">Preview locked - Complete checkout to unlock full images</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success py-2 px-3 mb-2" role="alert">
                                            <i class="las la-check-circle me-2"></i>
                                            <small class="mb-0">Preview unlocked - Ready to download</small>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="barcode-preview-wrapper border rounded bg-white shadow-sm p-2 position-relative <?= $previewLocked ? 'locked' : 'unlocked'; ?>"
                                data-locked="<?= $previewLocked ? 'true' : 'false'; ?>">
                                <div id="barcodes" class="barcode-preview-grid"
                                    style="<?= $previewLocked ? 'filter: blur(8px); pointer-events: none;' : ''; ?>">
                                    <?php if (!empty($_SESSION['barcodeValue'])): ?>
                                        <?php foreach ($_SESSION['barcodeValue'] as $index => $value): ?>
                                            <div class="barcode-preview-tile">
                                                <svg class="barcode" id="barcode-<?= $index; ?>"
                                                    data-index="<?= $index; ?>"></svg>
                                                <small class="text-muted d-block mt-1 text-truncate">
                                                    <i class="las la-<?= $previewLocked ? 'lock' : 'unlock' ?> me-1 text-<?= $previewLocked ? 'warning' : 'success' ?>"></i>
                                                    <?= $previewLocked ? 'Hidden' : 'Unlocked' ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted py-4 text-center">
                                            <i class="las la-barcode text-info display-6"></i>
                                            <p class="mb-0 mt-2">Generate barcodes to preview them here.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($previewLocked): ?>
                                    <div class="barcode-preview-overlay small text-center">
                                        <i class="las la-lock text-warning" style="font-size: 2.5rem;"></i>
                                        <p class="fw-semibold mb-1">Preview locked</p>
                                        <p class="mb-0">Pay once to unlock, view and download all barcodes in your cart.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($_SESSION['barcodeValue']) && !$previewLocked): ?>
                                <div class="d-flex justify-content-center mt-3">
                                    <button type="button" id="download" class="btn btn-orange rounded-0"
                                        onclick="saveBarcodes()">
                                        <i class="las la-download las-sm"></i> Download Barcodes
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>

    <!-- ================================================================== -->
    <?php include APPROOT . '/views/inc/footer.php'; ?>
    <?php include APPROOT . '/views/inc/script-links.php'; ?>

    <script>
        function generateRandomNumber() {
            // Generate 12-digit random number for EAN-13 (JsBarcode will add the check digit)
            const randomNum = Math.floor(100000000000 + Math.random() * 900000000000);
            document.getElementById('barcodeValue').value = randomNum;
        }
        // Generate the barcodes using the input or random values and customization options
        // Even when preview is visually locked, we still render barcodes so server-side images can be generated
        <?php if (!empty($_SESSION['barcodeValue'])): ?>
            <?php foreach ($_SESSION['barcodeValue'] as $index => $value): ?>
                JsBarcode("#barcode-<?= $index; ?>", "<?= $value; ?>", {
                    format: "<?= $data['format']; ?>", // Barcode format
                    displayValue: <?= $data['displayValue']; ?>, // Show/hide the value
                    lineColor: "<?= $data['lineColor']; ?>", // Line color
                    background: "<?= $data['backgroundColor']; ?>", // Background color
                    fontSize: <?= $data['fontSize']; ?>, // Font size
                    width: <?= $data['width']; ?>, // Line width
                    height: <?= $data['height']; ?> // Barcode height
                });
            <?php endforeach; ?>
        <?php endif; ?>

        // Function to save all barcodes
        function saveBarcodes() {
            const imageFormat = document.getElementById("imageFormat").value;

            if (imageFormat === "pdf") {
                // Save all barcodes as a single PDF
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF();
                const barcodeContainers = document.querySelectorAll(".barcode-container");

                let promises = []; // Array to hold all promises

                barcodeContainers.forEach((container, index) => {
                    const svgElement = container.querySelector("svg");
                    const svgData = new XMLSerializer().serializeToString(svgElement);

                    // Convert SVG to canvas
                    const canvas = document.createElement("canvas");
                    const context = canvas.getContext("2d");

                    // Set canvas dimensions explicitly
                    canvas.width = 300; // Adjust as needed
                    canvas.height = 150; // Adjust as needed

                    // Draw the SVG onto the canvas
                    const img = new Image();
                    img.src = "data:image/svg+xml;base64," + btoa(svgData);

                    // Wrap the onload logic in a promise
                    const promise = new Promise((resolve) => {
                        img.onload = function () {
                            context.drawImage(img, 0, 0, canvas.width, canvas.height);

                            // Convert canvas to image data
                            const imageData = canvas.toDataURL("image/png");

                            // Add the barcode to the PDF
                            if (index > 0) pdf.addPage(); // Add a new page for each barcode after the first
                            pdf.addImage(imageData, "PNG", 10, 10, 100, 50); // Adjust dimensions as needed
                            resolve();
                        };
                    });

                    promises.push(promise); // Add the promise to the array
                });

                // Wait for all promises to resolve before saving the PDF
                Promise.all(promises).then(() => {
                    pdf.save("barcodes-" + Date.now() + ".pdf");
                });
            } else {
                // Save each barcode individually
                const barcodeContainers = document.querySelectorAll(".barcode-container");

                barcodeContainers.forEach((container, index) => {
                    const svgElement = container.querySelector("svg");
                    const svgData = new XMLSerializer().serializeToString(svgElement);

                    if (imageFormat === "svg") {
                        // Save as SVG
                        const blob = new Blob([svgData], {
                            type: "image/svg+xml"
                        });
                        const url = URL.createObjectURL(blob);

                        // Create a download link
                        const a = document.createElement("a");
                        a.href = url;
                        a.download = "barcode-" + (index + 1) + "-" + <?= $data['barcodeValue'] ?> + ".svg";
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    } else {
                        // Save as PNG or JPEG
                        const canvas = document.createElement("canvas");
                        const context = canvas.getContext("2d");

                        // Set canvas dimensions explicitly
                        canvas.width = 300; // Adjust as needed
                        canvas.height = 150; // Adjust as needed

                        // Draw the SVG onto the canvas
                        const img = new Image();
                        img.src = "data:image/svg+xml;base64," + btoa(svgData);

                        img.onload = function () {
                            context.drawImage(img, 0, 0, canvas.width, canvas.height);

                            // Convert canvas to the selected image format
                            const imageData = canvas.toDataURL("image/" + imageFormat);

                            // Create a download link
                            const a = document.createElement("a");
                            a.href = imageData;
                            a.download = "barcode-" + (index + 1) + "-" + <?= $data['barcodeValue'] ?> + "." + imageFormat;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        };
                    }
                });
            }
        }
    </script>

    <script>
        const previewLocked = <?= $previewLocked ? 'true' : 'false'; ?>;
        const randomSavedIds = <?= json_encode($_SESSION['barcode_saved_ids'] ?? []); ?>;

        function validateBarcodeValue(value, format) {
            if (!value) {
                return { valid: false, message: 'Please enter barcode content.' };
            }

            const trimmed = value.trim();

            if (format === 'EAN13') {
                if (!/^\d+$/.test(trimmed)) {
                    return { valid: false, message: 'EAN-13 must contain digits only.' };
                }
                if (trimmed.length !== 12 && trimmed.length !== 13) {
                    return { valid: false, message: 'EAN-13 must be 12 or 13 digits long.' };
                }
            } else if (format === 'UPC') {
                if (!/^\d+$/.test(trimmed)) {
                    return { valid: false, message: 'UPC-A must contain digits only.' };
                }
                if (trimmed.length !== 11 && trimmed.length !== 12) {
                    return { valid: false, message: 'UPC-A must be 11 or 12 digits long.' };
                }
            } else if (format === 'CODE39') {
                const upper = trimmed.toUpperCase();
                // Allowed: A-Z, 0-9, space, - . $ / + %
                if (!/^[A-Z0-9 \-\.\$/\+%]*$/.test(upper)) {
                    return {
                        valid: false,
                        message: 'CODE-39 supports A–Z, 0–9 and - . space $ / + % only.'
                    };
                }
            } else if (format === 'CODE128') {
                // JsBarcode CODE128 is flexible; just guard against extremely long strings
                if (trimmed.length > 80) {
                    return {
                        valid: false,
                        message: 'CODE-128 content is too long. Please use 80 characters or fewer.'
                    };
                }
            }

            return { valid: true, message: '' };
        }

        // Prevent the form from submitting normally for the Generate button
        document.addEventListener('DOMContentLoaded', function () {
            const barcodeForm = document.getElementById('barcodeForm');
            const addToCartBtn = document.getElementById('addToCart');

            console.log('Form found:', barcodeForm);
            console.log('Add to Cart button found:', addToCartBtn);

            // Check if barcodes were generated via generateRandom (page was reloaded)
            // If preview barcodes exist, save them as image files (regardless of locked state)
            const barcodeElements = document.querySelectorAll('.barcode[id^="barcode-"]');
            if (barcodeElements.length > 0) {
                console.log('Found', barcodeElements.length, 'barcodes in preview, saving to files...');
                saveExistingBarcodesToFiles();
            }

            if (!addToCartBtn) {
                console.error('Add to Cart button not found!');
                return;
            }

            // Prevent form submission when Generate button is clicked
            barcodeForm.addEventListener('submit', function (e) {
                // Only prevent if it's NOT the generateRandom button
                if (!e.submitter || !e.submitter.name || e.submitter.name !== 'generateRandom') {
                    e.preventDefault();
                    console.log('Form submission prevented');
                }
            });

            addToCartBtn.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent form submission
                e.stopPropagation(); // Stop event bubbling
                console.log('Generate button clicked!');

                const barcodeValue = document.getElementById('barcodeValue').value;
                const numBarcodes = parseInt(document.getElementById('numBarcodes').value);
                const format = document.getElementById('format').value;

                if (!barcodeValue || numBarcodes < 1) {
                    swal("Error", "Please enter valid barcode content and quantity.", "error");
                    return;
                }

                const validation = validateBarcodeValue(barcodeValue, format);
                if (!validation.valid) {
                    swal("Invalid Barcode", validation.message, "error");
                    return;
                }

                // Get customization options
                const displayValue = document.getElementById('displayValue') ? document.getElementById('displayValue').value : 'true';
                const lineColor = document.getElementById('lineColor') ? document.getElementById('lineColor').value : '#000000';
                const backgroundColor = document.getElementById('backgroundColor') ? document.getElementById('backgroundColor').value : '#FFFFFF';
                const width = document.getElementById('width') ? document.getElementById('width').value : '2';
                const height = document.getElementById('height') ? document.getElementById('height').value : '100';

                // Prepare the barcodes to add to the cart
                const barcodes = [];

                // Note: Barcode images are generated on-demand from stored values
                // This is more efficient than storing base64 images in the database

                if (numBarcodes === 1) {
                    // Single barcode
                    barcodes.push({
                        barcodeValue: barcodeValue,
                        format: format,
                        displayValue: displayValue,
                        lineColor: lineColor,
                        backgroundColor: backgroundColor,
                        width: width,
                        height: height
                    });
                } else {
                    // Multiple barcodes - group them together
                    const barcodeValues = [];
                    for (let i = 0; i < numBarcodes; i++) {
                        barcodeValues.push(barcodeValue + '-' + (i + 1)); // Append a unique identifier
                    }
                    barcodes.push({
                        barcodeValue: barcodeValues,
                        format: format,
                        displayValue: displayValue,
                        lineColor: lineColor,
                        backgroundColor: backgroundColor,
                        width: width,
                        height: height
                    });
                }

                // Validate barcodes array before sending
                if (!barcodes || barcodes.length === 0) {
                    console.error('Barcodes array is empty!');
                    alert('Error: No barcodes to send');
                    return;
                }

                console.log('Number of barcodes:', barcodes.length);
                console.log('Barcodes array:', JSON.stringify(barcodes, null, 2));

                const payload = { barcodes: barcodes };
                console.log('Full payload:', JSON.stringify(payload, null, 2));

                // Send the barcodes to the server
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        barcodes: barcodes
                    }),
                })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Server response:', data);
                        if (data.status === 'success') {
                            // Update preview with generated barcodes
                            updateBarcodePreview(data.barcode_values, data.customization);

                            // Save barcode images to server
                            saveBarcodeImagesToServer(data.barcode_values, data.customization, data.saved_ids);

                            swal("Added!", data.message || 'Barcodes added to cart successfully!', "success");
                        } else {
                            swal("Error!", data.message || 'Failed to add barcodes to cart.', "error");
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        swal("Error!", "An error occurred while adding barcodes to cart.", "error");
                    });
            });
        });

        // Function to update barcode preview
        function updateBarcodePreview(barcodeValues, customization) {
            const barcodesContainer = document.getElementById('barcodes');
            if (!barcodesContainer) {
                console.error('Barcodes container not found');
                return;
            }

            barcodesContainer.innerHTML = '';

            const wrapper = document.querySelector('.barcode-preview-wrapper');
            const isLocked = wrapper?.dataset.locked === 'true';

            if (isLocked) {
                barcodesContainer.style.filter = 'blur(8px)';
                barcodesContainer.style.pointerEvents = 'none';

                barcodeValues.forEach(() => {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'barcode-preview-tile placeholder';
                    placeholder.innerHTML = `
                        <div class="placeholder-barcode mb-1"></div>
                        <small class="text-muted d-block text-truncate"><i class="las la-lock me-1 text-warning"></i>Hidden</small>
                    `;
                    barcodesContainer.appendChild(placeholder);
                });

                // Ensure overlay exists for locked state
                if (!wrapper.querySelector('.barcode-preview-overlay')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'barcode-preview-overlay small text-center';
                    overlay.innerHTML = `
                        <i class="las la-lock text-warning" style="font-size: 2.5rem;"></i>
                        <p class="fw-semibold mb-1">Preview Locked</p>
                        <p class="mb-0">Complete checkout to unlock your barcodes.</p>
                    `;
                    wrapper.appendChild(overlay);
                }

                console.log('Barcode preview placeholders rendered (locked state).');
                return;
            }

            barcodesContainer.style.filter = '';
            barcodesContainer.style.pointerEvents = '';

            // Remove overlay if present (unlocked state)
            const existingOverlay = wrapper?.querySelector('.barcode-preview-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            // Generate preview for each barcode when unlocked
            // Always save images server-side so files exist in /public/images/barimage
            barcodeValues.forEach((value, index) => {
                const container = document.createElement('div');
                container.className = 'barcode-container mb-1';

                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.classList.add('barcode');
                svg.id = 'barcode-' + index;

                container.appendChild(svg);
                barcodesContainer.appendChild(container);

                try {
                    let cleanValue = value;
                    if (cleanValue.includes('-')) {
                        cleanValue = cleanValue.split('-')[0];
                    }

                    JsBarcode('#barcode-' + index, cleanValue, {
                        format: customization.format,
                        displayValue: customization.displayValue === 'true' || customization.displayValue === true,
                        lineColor: customization.lineColor,
                        background: customization.backgroundColor,
                        width: parseFloat(customization.width),
                        height: parseFloat(customization.height)
                    });
                } catch (e) {
                    console.error('Error rendering barcode:', e);
                }
            });

            console.log('Barcode preview updated with', barcodeValues.length, 'barcodes (unlocked).');
        }

        // Function to save barcode images to server as files
        function saveBarcodeImagesToServer(barcodeValues, customization, savedIds) {
            if (!savedIds || savedIds.length === 0) {
                console.log('No saved IDs, skipping image save');
                return;
            }

            barcodeValues.forEach((value, index) => {
                setTimeout(() => {
                    try {
                        // Create SVG element
                        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');

                        // Clean the value - remove suffix
                        let cleanValue = value;
                        if (cleanValue.includes('-')) {
                            cleanValue = cleanValue.split('-')[0];
                        }

                        JsBarcode(svg, cleanValue, {
                            format: customization.format,
                            displayValue: customization.displayValue === 'true' || customization.displayValue === true,
                            lineColor: customization.lineColor,
                            background: customization.backgroundColor,
                            width: parseFloat(customization.width),
                            height: parseFloat(customization.height)
                        });

                        // Convert SVG to PNG
                        const svgData = new XMLSerializer().serializeToString(svg);
                        const canvas = document.createElement('canvas');
                        canvas.width = 400;
                        canvas.height = 200;
                        const ctx = canvas.getContext('2d');
                        const img = new Image();

                        img.onload = function () {
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                            const imageData = canvas.toDataURL('image/png');

                            // Send to server to save as file
                            fetch('<?= URLROOT ?>/barcode/saveImageFile', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id: savedIds[index] || null,
                                    value: cleanValue,
                                    imageData: imageData
                                })
                            })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        console.log('Barcode image saved:', result.filename);
                                    } else {
                                        console.error('Failed to save barcode image:', result.message);
                                    }
                                })
                                .catch(error => console.error('Error saving barcode image:', error));
                        };

                        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
                    } catch (e) {
                        console.error('Error generating barcode image:', e);
                    }
                }, index * 100); // Stagger requests to avoid overwhelming server
            });
        }

        // Function to save existing barcode previews to files (for generateRandom)
        function saveExistingBarcodesToFiles() {
            const barcodeElements = document.querySelectorAll('.barcode[id^="barcode-"]');
            const format = document.getElementById('format')?.value || 'EAN13';
            const displayValue = document.getElementById('displayValue')?.value || 'true';
            const lineColor = document.getElementById('lineColor')?.value || '#000000';
            const backgroundColor = document.getElementById('backgroundColor')?.value || '#FFFFFF';
            const width = document.getElementById('width')?.value || '2';
            const height = document.getElementById('height')?.value || '100';

            barcodeElements.forEach((svgElement, index) => {
                setTimeout(() => {
                    try {
                        // Get the value from the SVG element's data or text content
                        const svgData = new XMLSerializer().serializeToString(svgElement);

                        // Extract barcode value from SVG (look for text element)
                        const textMatch = svgData.match(/>(\d+)</);
                        const barcodeValue = textMatch ? textMatch[1] : null;

                        if (!barcodeValue) {
                            console.error('Could not extract barcode value from SVG');
                            return;
                        }

                        // Convert SVG to PNG
                        const canvas = document.createElement('canvas');
                        canvas.width = 400;
                        canvas.height = 200;
                        const ctx = canvas.getContext('2d');
                        const img = new Image();

                        img.onload = function () {
                            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                            const imageData = canvas.toDataURL('image/png');

                            // Determine matching saved ID for this random barcode (if available)
                            const matchedId = Array.isArray(randomSavedIds) && randomSavedIds.length > index
                                ? randomSavedIds[index]
                                : null;

                            // Send to server to save as file and attach to DB record
                            fetch('<?= URLROOT ?>/barcode/saveImageFile', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id: matchedId,
                                    value: barcodeValue,
                                    imageData: imageData,
                                    format: format,
                                    displayValue: displayValue,
                                    lineColor: lineColor,
                                    backgroundColor: backgroundColor,
                                    width: width,
                                    height: height
                                })
                            })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        console.log('Barcode image saved for random generation:', result.filename);
                                    } else {
                                        console.error('Failed to save barcode image:', result.message);
                                    }
                                })
                                .catch(error => console.error('Error saving barcode image:', error));
                        };

                        img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
                    } catch (e) {
                        console.error('Error generating barcode image from preview:', e);
                    }
                }, index * 100);
            });
        }
    </script>

</body>

</html>