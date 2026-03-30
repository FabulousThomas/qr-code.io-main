<style>
    .font-color-black,
    #checkout-email-input,
    #checkout-email-input::placeholder {
        color: black !important;
    }

    /* Cart Modal Dark Theme Fixes */
    #cartModal .modal-content {
        background-color: var(--color-dark) !important;
        border: 1px solid var(--text-dark) !important;
    }

    #cartModal .modal-header {
        background-color: var(--bg-dark) !important;
        border-bottom: 1px solid var(--text-dark) !important;
    }

    #cartModal .modal-title {
        color: var(--color-white) !important;
        font-weight: 600 !important;
    }

    #cartModal .modal-body {
        background-color: var(--color-dark) !important;
        color: var(--color-white) !important;
    }

    #cartModal .table-dark {
        background-color: var(--bg-dark) !important;
        color: var(--color-white) !important;
    }

    #cartModal .table-dark th,
    #cartModal .table-dark td {
        border-color: var(--text-dark) !important;
        color: var(--color-white) !important;
    }

    #cartModal .badge {
        font-weight: 500 !important;
    }

    #cartModal .cart-summary {
        background-color: var(--bg-dark) !important;
        border-color: var(--text-dark) !important;
    }

    #cartModal .modal-footer {
        background-color: var(--bg-dark) !important;
        border-top: 1px solid var(--text-dark) !important;
    }

    #cartModal .btn-secondary {
        background-color: var(--text-dark) !important;
        border-color: var(--text-dark) !important;
        color: var(--color-white) !important;
    }

    #cartModal .btn-secondary:hover {
        background-color: #888888 !important;
    }

    #cartModal .close span {
        color: var(--color-white) !important;
    }

    #cartModal .text-white {
        color: var(--color-white) !important;
    }

    #cartModal .text-white-50 {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    /* SweetAlert input styling for cart checkout */
    .swal-content__input {
        background-color: var(--color-white) !important;
        color: black !important;
        border: 1px solid var(--text-dark) !important;
    }

    /* Customize Modal Dark Theme Fixes */
    #customize .modal-content {
        background-color: var(--color-dark) !important;
        border: 1px solid var(--text-dark) !important;
    }

    #customize .modal-header {
        background-color: var(--bg-dark) !important;
        border-bottom: 1px solid var(--text-dark) !important;
    }

    #customize .modal-title {
        color: var(--color-white) !important;
        font-weight: 600 !important;
    }

    #customize .modal-body {
        background-color: var(--color-dark) !important;
        color: var(--color-white) !important;
    }

    #customize .form-label {
        color: var(--color-white) !important;
        font-weight: 500 !important;
    }

    #customize .form-control {
        background-color: var(--bg-dark) !important;
        border-color: var(--text-dark) !important;
        color: var(--color-white) !important;
    }

    #customize .form-control:focus {
        border-color: var(--color-orange) !important;
        box-shadow: 0 0 0 0.2rem rgba(245, 183, 89, 0.25) !important;
    }

    #customize .form-control::placeholder {
        color: var(--text-dark) !important;
    }

    #customize .close span {
        color: var(--color-white) !important;
    }

    #customize .text-danger {
        color: #dc3545 !important;
    }

    #customize .card-body {
        background-color: var(--bg-dark) !important;
        border: 1px solid var(--text-dark) !important;
    }

    // Placeholder: initialize OPay payment using their Checkout API.
    // You need to plug in your OPay public key / config and follow their docs
    // for opening the payment page. After a successful payment, the frontend
    // should call the Barcode::verifyOpayPayment endpoint to unlock codes.
    function initializeOpayPayment(email, amount, itemCount) {

        // Example stub: post to a backend route that creates an OPay transaction
        fetch('<?= URLROOT ?>/barcode/initOpayPayment', {

            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }

            ,
            body: JSON.stringify({
                email, amount, itemCount
            })

    }) .then(response=> response.json()) .then(data=> {
            if (data.success && data.paymentUrl) {
                window.location.href=data.paymentUrl; // Redirect to OPay checkout
            }

            else {
                swal('Error', data.message || 'Unable to start OPay payment.', 'error');
            }

        }) .catch(error=> {
            console.error('OPay init error:', error);
            swal('Error', 'An error occurred starting OPay payment.', 'error');
        });
    }
</style>

<!-- Modal for customizing QR code -->
<div class="modal fade" id="customize" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="customizeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered modal-fullscreen">
        <div class="container modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customizeLabel">Customize QR Code</h5>
                <button type="button" class="close border-0 bg-transparent" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row d-non">
                    <div class="col-md-8">
                        <form action="" method="POST" id="customizeForm" enctype="multipart/form-data">
                            <?php csrf_input(); ?>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="foregroundColor">Foreground Color:</label>
                                    <input type="color" id="foregroundColor" name="foregroundColor"
                                        class="form-control color-input shadow-none border bg-transparent"
                                        value="<?= isset($_SESSION['customize']['foregroundColor']) ? sprintf("#%02x%02x%02x", $_SESSION['customize']['foregroundColor'][0], $_SESSION['customize']['foregroundColor'][1], $_SESSION['customize']['foregroundColor'][2]) : '#000000' ?>"
                                        required />
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="backgroundColor">Background Color:</label>
                                    <input type="color" id="backgroundColor" name="backgroundColor"
                                        class="form-control color-input shadow-none border bg-transparent"
                                        value="<?= isset($_SESSION['customize']['backgroundColor']) ? sprintf("#%02x%02x%02x", $_SESSION['customize']['backgroundColor'][0], $_SESSION['customize']['backgroundColor'][1], $_SESSION['customize']['backgroundColor'][2]) : '#ffffff' ?>"
                                        required />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="size">Size:</label>
                                    <input type="number" id="size" name="size"
                                        class="form-control shadow-none border bg-transparent"
                                        value="<?= $_SESSION['customize']['size'] ?? 300 ?>">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="margin">Margin:</label>
                                    <input type="number" id="margin" name="margin"
                                        class="form-control shadow-none border bg-transparent"
                                        value="<?= $_SESSION['customize']['margin'] ?? 10 ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="qrCodeFormat">QR Code Format:</label>
                                    <select id="qrCodeFormat" name="qrCodeFormat"
                                        class="form-control shadow-none border bg-transparent">
                                        <option value="png" selected>PNG</option>
                                        <option value="svg">SVG</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="labelText">Display Text:</label>
                                    <input type="text" id="labelText" name="labelText"
                                        class="form-control shadow-none border bg-transparent"
                                        value="<?= $_SESSION['customize']['labelText'] ?? '' ?>" placeholder="Optional">
                                </div>
                            </div>
                            <div class="row d-non">
                                <div class="col-md-6 mb-2">
                                    <label for="logo">Logo:</label>
                                    <input type="file" id="logo" name="logo"
                                        class="form-control shadow-none border bg-transparent"
                                        value="<?= $_SESSION['logoPath'] ?? '' ?>" accept="image/*">
                                    <?php if (isset($_SESSION['logoPath']) && $_SESSION['logoPath']): ?>
                                        <label>Current Logo: <img
                                                src="<?= URLROOT ?>/images/uploads/<?= $_SESSION['logoPath'] ?>" alt="Logo"
                                                style="width: 50px;"></label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <small class="text-uppercase text-danger">Note-> More features coming soon< </small>
                                    <div class="row">
                                        <div class="col-6 mb-2">
                                            <button type="submit" name="btnCustomize"
                                                class="form-control btn btn-orange rounded-0 d-flex align-items-center justify-content-center"><i
                                                    class="las la-plus-square" style="font-size: 1.1rem;"></i>Apply
                                                Changes</button>
                                        </div>
                                    </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="card-body text-center border py-4">
                            <div class="position-relative d-inline-block">
                                <img id="qrCodePreview" src="<?= $_SESSION['qrImageData'] ?? '' ?>"
                                    alt="QR Code Preview" class="img-fluid"
                                    style="width: 250px; filter: blur(8px); pointer-events: none;">
                                <div class="position-absolute top-50 start-50 translate-middle text-center bg-white shadow rounded p-3"
                                    style="width: 210px;">
                                    <i class="las la-lock text-warning" style="font-size: 2.2rem;"></i>
                                    <p class="fw-semibold mb-1">Preview Locked</p>
                                    <p class="text-muted small mb-0">Apply your changes and complete checkout to unlock
                                        the clear image.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal for cart modal -->
<div class="modal fade" id="cartModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Your Cart</h5>
                <button type="button" class="close border-0 bg-transparent shadow-none" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true" class="text-white fs-3">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cartModalBody">
                <!-- Check if the cart is empty -->
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class='text-center py-5'>
                        <div class='mb-3'>
                            <i class='las la-shopping-cart' style='font-size: 4rem; color: #ccc;'></i>
                        </div>
                        <h5 class='text-white mb-2'>Your cart is empty!</h5>
                        <p class='text-white mb-0'>Add some QR codes or barcodes to your cart to get started.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive cart-items-table">
                        <table class="table table-dark table-hover align-middle mb-4">
                            <thead>
                                <tr>
                                    <th scope="col">Item</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Format</th>
                                    <th scope="col">Details</th>
                                    <th scope="col" class="text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                    <tr class="cart-item">
                                        <td class="fw-semibold">Item #<?= ($index + 1) ?></td>
                                        <td>
                                            <?php if (!empty($item['type'])): ?>
                                                <span
                                                    class="badge bg-info text-uppercase"><?= htmlspecialchars($item['type']) ?></span>
                                            <?php else: ?>
                                                <span class="text-white-50">—</span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['randomGenerated'])): ?>
                                                <span class="badge bg-warning text-dark ms-1">Random</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['format'])): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($item['format']) ?></span>
                                            <?php else: ?>
                                                <span class="text-white-50">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="min-width: 220px;">
                                            <?php if (isset($item['type']) && $item['type'] === 'qrcode'): ?>
                                                <div class="text-white small">
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <i class="las la-qrcode text-warning"></i>
                                                        <span class="fw-semibold">QR Code</span>
                                                        <span class="badge bg-dark border border-warning text-warning">Locked</span>
                                                        <?php if (!empty($item['randomGenerated'])): ?>
                                                            <span class="badge bg-warning text-dark">Random
                                                                ×<?= (int) ($item['randomGeneratedCount'] ?? 1) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-white-50 d-flex flex-wrap gap-2 mt-1">
                                                        <span><i class="las la-lock me-1"></i>Unlock after payment</span>
                                                        <?php if (!empty($item['qrCodeValue'])): ?>
                                                            <span><i class="las la-database me-1"></i>Data hidden</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php elseif (isset($item['barcodeValue'])): ?>
                                                <?php
                                                $displayValue = isset($item['displayValue']) ? ($item['displayValue'] === 'true' || $item['displayValue'] === true) : true;
                                                $lineColor = $item['lineColor'] ?? '#000000';
                                                $backgroundColor = $item['backgroundColor'] ?? '#FFFFFF';
                                                $width = $item['width'] ?? '2';
                                                $height = $item['height'] ?? '100';
                                                $barcodeCount = is_array($item['barcodeValue']) ? count($item['barcodeValue']) : 1;
                                                ?>
                                                <div class="text-white small">
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <span class="badge"
                                                            style="background: <?= htmlspecialchars($lineColor) ?>; color: <?= $lineColor === '#FFFFFF' ? '#000' : '#FFF' ?>;">Line</span>
                                                        <span class="badge"
                                                            style="background: <?= htmlspecialchars($backgroundColor) ?>; color: <?= $backgroundColor === '#FFFFFF' ? '#000' : '#FFF' ?>;">Bg</span>
                                                        <span
                                                            class="badge bg-secondary"><?= htmlspecialchars($width) ?>×<?= htmlspecialchars($height) ?>px</span>
                                                        <span class="badge bg-dark border border-warning text-warning">Locked</span>
                                                        <?php if (!$displayValue): ?>
                                                            <span class="badge bg-dark border border-secondary text-white-50">Value
                                                                Hidden</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($item['randomGenerated'])): ?>
                                                            <span class="badge bg-warning text-dark">Random ×<?= $barcodeCount ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-white-50 d-flex flex-wrap gap-2 mt-1">
                                                        <span><i class="las la-barcode me-1"></i><?= $barcodeCount ?> locked</span>
                                                        <span><i class="las la-lock me-1"></i>Visible after payment</span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-white">No value</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-item-btn"
                                                onclick="removeCartItem(<?= $index ?>)" title="Remove from cart">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cart Summary -->
                    <?php
                    $totalQRCodes = 0;
                    $totalBarcodes = 0;
                    $randomDiscountEligible = 0;
                    foreach ($_SESSION['cart'] as $item) {
                        if (isset($item['type']) && $item['type'] === 'qrcode') {
                            $totalQRCodes++;
                        } elseif (isset($item['barcodeValue'])) {
                            $count = is_array($item['barcodeValue']) ? count($item['barcodeValue']) : 1;
                            $totalBarcodes += $count;

                            if (!empty($item['randomGenerated'])) {
                                $availableCap = max(0, 20 - $randomDiscountEligible);
                                if ($availableCap > 0) {
                                    $eligibleCount = min($count, (int) ($item['randomGeneratedCount'] ?? $count), $availableCap);
                                    $randomDiscountEligible += $eligibleCount;
                                }
                            }
                        }
                    }
                    $qrPrice = 500;
                    $barcodePrice = 700;
                    $totalItems = $totalQRCodes + $totalBarcodes;
                    $baseAmount = ($totalQRCodes * $qrPrice) + ($totalBarcodes * $barcodePrice);
                    $discountAmount = (int) round($randomDiscountEligible * $barcodePrice * 0.20);
                    $totalAmount = max(0, $baseAmount - $discountAmount);
                    ?>
                    <div class='cart-summary mt-3 p-3 border-top' style='background: rgba(255, 255, 255, 0.05);'>
                        <h6 class='text-white mb-3'><i class='las la-shopping-cart'></i> Cart Summary</h6>
                        <div class='row text-white'>
                            <div class='col-6 mb-2'>
                                <div class='d-flex align-items-center'>
                                    <i class='las la-box text-success me-2' style='font-size: 1.5rem;'></i>
                                    <div>
                                        <small class='d-block text-white'>Total Items</small>
                                        <strong style='font-size: 1.2rem;'><?= $totalItems ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class='col-6 mb-2'>
                                <div class='d-flex align-items-center'>
                                    <i class='las la-qrcode text-info me-2' style='font-size: 1.5rem;'></i>
                                    <div>
                                        <small class='d-block text-white'>QR Codes</small>
                                        <strong style='font-size: 1.2rem;'><?= $totalQRCodes ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class='col-6'>
                                <div class='d-flex align-items-center'>
                                    <i class='las la-barcode text-warning me-2' style='font-size: 1.5rem;'></i>
                                    <div>
                                        <small class='d-block text-white'>Barcodes</small>
                                        <strong style='font-size: 1.2rem;'><?= $totalBarcodes ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class='d-flex align-items-center'>
                                    <i class='las la-money-bill-wave text-warning me-2' style='font-size: 1.5rem;'></i>
                                    <div>
                                        <small class='d-block text-white'>Total Amount</small>
                                        <strong style='font-size: 1.4rem;'>₦<?= number_format($totalAmount) ?></strong>
                                        <?php if ($discountAmount > 0): ?>
                                            <small class='d-block text-success'>Includes 20% off discount</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <button type="button" class="btn btn-orange me-2" id="checkoutBtn"
                        onclick="proceedToCheckout('paystack')">
                        <i class="las la-shopping-cart las-sm"></i> Pay with Paystack
                    </button>
                    <button type="button" class="btn btn-success" id="opayCheckoutBtn" onclick="proceedToCheckout('opay')">
                        <i class="las la-credit-card las-sm"></i> Pay with OPay
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function proceedToCheckout(provider = 'paystack') {
        // Calculate total amount from cart
        const cartItems = <?= json_encode($_SESSION['cart'] ?? []) ?>;

        if (cartItems.length === 0) {
            swal("Empty Cart", "Your cart is empty. Please add items before checkout.", "warning");
            return;
        }

        const cartModalElement = document.getElementById('cartModal');
        const cartModal = cartModalElement ? (bootstrap.Modal.getInstance(cartModalElement) || bootstrap.Modal.getOrCreateInstance(cartModalElement)) : null;

        const launchEmailPrompt = () => {
            // Calculate totals with distinct pricing (mirror PHP summary logic)
            let totalQrCodes = 0;
            let totalBarcodes = 0;
            let randomDiscountEligible = 0;

            cartItems.forEach(item => {
                if (item.type === 'qrcode') {
                    totalQrCodes += 1;
                } else if (item.barcodeValue) {
                    const count = Array.isArray(item.barcodeValue) ? item.barcodeValue.length : 1;
                    totalBarcodes += count;

                    if (item.randomGenerated) {
                        const availableCap = Math.max(0, 20 - randomDiscountEligible);
                        if (availableCap > 0) {
                            const eligibleCount = Math.min(
                                count,
                                typeof item.randomGeneratedCount === 'number' ? item.randomGeneratedCount : count,
                                availableCap
                            );
                            randomDiscountEligible += eligibleCount;
                        }
                    }
                }
            });

            const qrPrice = 500;   // ₦500 per QR code
            const barcodePrice = 700; // ₦700 per barcode
            const totalItems = totalQrCodes + totalBarcodes;
            const baseAmount = (totalQrCodes * qrPrice) + (totalBarcodes * barcodePrice);
            const discountAmount = Math.round(randomDiscountEligible * barcodePrice * 0.20);
            const totalAmount = Math.max(0, baseAmount - discountAmount);

            swal({
                title: "Enter Your Email",
                text: discountAmount > 0
                    ? `Total: ₦${totalAmount.toLocaleString()} for ${totalItems} item(s) (20% discount applied)`
                    : `Total: ₦${totalAmount.toLocaleString()} for ${totalItems} item(s)`,
                content: "input",
                buttons: {
                    cancel: "Cancel",
                    confirm: {
                        text: "Proceed to Payment",
                        closeModal: false,
                    }
                },
                closeOnClickOutside: false,
            }).then((value) => {
                if (value === null) {
                    swal.close();
                    if (cartModal) {
                        cartModal.show();
                    }
                    return;
                }

                const email = (value || '').trim();

                if (!email) {
                    swal("Email Required", "Please enter your email address.", "error");
                    if (cartModal) {
                        cartModal.show();
                    }
                    return;
                }

                // Validate email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    swal("Invalid Email", "Please enter a valid email address.", "error");
                    if (cartModal) {
                        cartModal.show();
                    }
                    return;
                }

                swal.close();

                // Close cart modal (ensure hidden state)
                if (cartModal) {
                    cartModal.hide();
                }

                // Initialize chosen payment provider
                if (provider === 'opay') {
                    initializeOpayPayment(email, totalAmount, totalItems);
                } else {
                    initializePaystackPayment(email, totalAmount, totalItems);
                }
            });

            // Configure and focus the SweetAlert input once it is rendered
            setTimeout(function () {
                const inp = document.querySelector('.swal-content__input');
                if (inp) {
                    inp.type = 'email';
                    inp.placeholder = 'your.email@example.com';
                    inp.required = true;
                    inp.id = 'checkout-email-input';
                    inp.autocomplete = 'email';
                    inp.classList.add('form-control shadow-none font-color-black');
                    inp.style.padding = '10px';
                    // inp.style.border = '1px solid #ddd';
                    inp.style.borderRadius = '4px';
                    inp.style.fontSize = '14px';
                    inp.focus();
                }
            }, 100);
        };

        if (cartModal) {
            const cleanUpBackdrops = () => {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            };

            if (cartModalElement.classList.contains('show')) {
                cartModalElement.addEventListener('hidden.bs.modal', function handleHidden() {
                    cartModalElement.removeEventListener('hidden.bs.modal', handleHidden);
                    cleanUpBackdrops();
                    launchEmailPrompt();
                }, { once: true });
                cartModal.hide();
            } else {
                cleanUpBackdrops();
                launchEmailPrompt();
            }
        } else {
            launchEmailPrompt();
        }
    }

    function initializePaystackPayment(email, amount, itemCount) {
        const paystackPublicKey = "<?= htmlspecialchars($_ENV['PAYSTACK_PUBLIC'] ?? '') ?>";

        const handler = PaystackPop.setup({
            key: paystackPublicKey,
            email: email,
            amount: amount * 100, // Convert to kobo (Paystack uses smallest currency unit)
            currency: "NGN",
            ref: 'QR_BARCODE_' + Math.floor((Math.random() * 1000000000) + 1) + '_' + Date.now(),
            metadata: {
                custom_fields: [
                    {
                        display_name: "Items Count",
                        variable_name: "items_count",
                        value: itemCount
                    },
                    {
                        display_name: "Product Type",
                        variable_name: "product_type",
                        value: "QR Codes & Barcodes"
                    }
                ]
            },
            callback: function (response) {
                // Payment successful
                console.log('Payment successful. Reference:', response.reference);

                // Verify payment on server
                verifyPayment(response.reference, email);
            },
            onClose: function () {
                // Payment window closed
                swal("Payment Cancelled", "You closed the payment window. Your cart items are still saved.", "info");
            }
        });

        handler.openIframe();
    }

    function verifyPayment(reference, email) {
        // Show loading state
        swal({
            title: "Verifying Payment",
            text: "Please wait while we verify your payment...",
            icon: "info",
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
        });

        // Send verification request to server
        fetch('<?= URLROOT ?>/barcode/verifyPayment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reference: reference,
                email: email
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Payment verified successfully
                    swal({
                        title: "Payment Successful!",
                        text: "Your payment has been verified. Your barcodes / QR codes are now available for download.",
                        icon: "success",
                        button: "View My Codes",
                    }).then(() => {
                        // Redirect to success page or reload to show unlocked codes
                        window.location.href = '<?= URLROOT ?>/barcode/paymentSuccess';
                    });
                } else {
                    swal("Verification Failed", data.message || "Unable to verify your payment. Please contact support.", "error");
                }
            })
            .catch(error => {
                console.error('Verification error:', error);
                swal("Error", "An error occurred while verifying your payment. Please contact support with reference: " + reference, "error");
            });
    }

    function removeCartItem(index) {
        // Use SweetAlert for confirmation
        swal({
            title: "Are you sure?",
            text: "Do you want to remove this item from the cart?",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancel",
                    value: false,
                    visible: true,
                    className: "",
                    closeModal: true,
                },
                confirm: {
                    text: "Yes, remove it!",
                    value: true,
                    visible: true,
                    className: "btn-danger",
                    closeModal: false
                }
            },
            dangerMode: true,
        }).then((willDelete) => {
            if (!willDelete) {
                return;
            }

            // Get the modal body and show loading state
            const modalBody = document.getElementById('cartModalBody');
            const originalContent = modalBody.innerHTML;
            modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Removing item...</p></div>';

            // Disable all buttons during removal
            const removeButtons = document.querySelectorAll('.remove-item-btn');
            const checkoutBtn = document.getElementById('checkoutBtn');
            removeButtons.forEach(btn => btn.disabled = true);
            if (checkoutBtn) checkoutBtn.disabled = true;

            // Send AJAX request to remove the item
            fetch('<?= URLROOT ?>/barcode/removeItem', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    index: index
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update modal body with new cart content
                        modalBody.innerHTML = data.cartHtml || originalContent;

                        // Update checkout button visibility
                        const checkoutBtn = document.getElementById('checkoutBtn');
                        const footer = document.querySelector('#cartModal .modal-footer');

                        if (data.hasItems) {
                            // Show checkout button if items exist
                            if (!checkoutBtn && footer) {
                                // Create checkout button if it doesn't exist
                                const closeBtn = footer.querySelector('button[data-bs-dismiss="modal"]');
                                if (closeBtn) {
                                    const newCheckoutBtn = document.createElement('button');
                                    newCheckoutBtn.type = 'button';
                                    newCheckoutBtn.className = 'btn btn-orange';
                                    newCheckoutBtn.id = 'checkoutBtn';
                                    newCheckoutBtn.onclick = proceedToCheckout;
                                    newCheckoutBtn.innerHTML = '<i class="las la-shopping-cart las-sm"></i> Proceed to Checkout';
                                    closeBtn.insertAdjacentElement('afterend', newCheckoutBtn);
                                }
                            } else if (checkoutBtn) {
                                checkoutBtn.style.display = 'inline-block';
                            }
                        } else {
                            // Hide or remove checkout button if cart is empty
                            if (checkoutBtn) {
                                checkoutBtn.remove(); // Remove instead of hide for cleaner UI
                            }
                        }

                        // Render barcodes after content update
                        setTimeout(function () {
                            if (typeof window.renderCartBarcodes === 'function') {
                                window.renderCartBarcodes();
                            } else {
                                // Try again after a bit more delay
                                setTimeout(function () {
                                    if (typeof window.renderCartBarcodes === 'function') {
                                        window.renderCartBarcodes();
                                    }
                                }, 200);
                            }
                        }, 150);

                        // Show success message using SweetAlert
                        swal("Removed!", "Item has been removed from the cart.", "success");

                    } else {
                        // Restore original content on error
                        modalBody.innerHTML = originalContent;
                        swal("Error!", data.message || 'Failed to remove item from cart', "error");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Restore original content on error
                    modalBody.innerHTML = originalContent;
                    swal("Error!", "An error occurred while removing the item. Please try again.", "error");
                })
                .finally(() => {
                    // Re-enable buttons
                    const removeButtons = document.querySelectorAll('.remove-item-btn');
                    removeButtons.forEach(btn => btn.disabled = false);
                    const checkoutBtn = document.getElementById('checkoutBtn');
                    if (checkoutBtn) checkoutBtn.disabled = false;
                });
        });
    }

    function updateItemNumbers() {
        const items = document.querySelectorAll('.cart-item');
        items.forEach((item, index) => {
            const itemNumber = item.querySelector('strong');
            if (itemNumber) {
                itemNumber.textContent = 'Item #' + (index + 1);
            }
        });
    }

    // Function to render barcodes in the cart (make it global)
    window.renderCartBarcodes = function () {
        // Wait for JsBarcode to be available
        if (typeof JsBarcode === 'undefined') {
            console.warn('JsBarcode not loaded yet, retrying...');
            setTimeout(window.renderCartBarcodes, 100);
            return;
        }

        const barcodeSvgs = document.querySelectorAll('.barcode-svg');
        if (barcodeSvgs.length === 0) {
            console.log('No barcode SVG elements found in cart');
            return;
        }

        console.log('Found ' + barcodeSvgs.length + ' barcode SVG elements to render');

        // Check if we have valid barcode values
        let validBarcodes = false;
        barcodeSvgs.forEach(function (svg, idx) {
            // Skip if already rendered (has children)
            if (svg.children.length > 0) {
                return;
            }

            const value = svg.getAttribute('data-value');
            if (!value) {
                console.log('No value found for barcode:', svg.id);
                return;
            }

            validBarcodes = true;
            const format = svg.getAttribute('data-format') || 'EAN13';
            const displayValue = svg.getAttribute('data-display') === 'true';
            const lineColor = svg.getAttribute('data-line-color') || '#000000';
            const backgroundColor = svg.getAttribute('data-bg-color') || '#FFFFFF';
            const width = svg.getAttribute('data-width') || '2';
            const height = svg.getAttribute('data-height') || '100';

            try {
                // Clean the value - remove any suffix like "-1", "-2" for display
                let cleanValue = value;
                if (cleanValue.includes('-')) {
                    const parts = cleanValue.split('-');
                    cleanValue = parts[0];
                }

                console.log('Rendering barcode:', cleanValue, 'Format:', format, 'SVG:', svg.id || idx);
                JsBarcode(svg, cleanValue, {
                    format: format,
                    displayValue: displayValue,
                    lineColor: lineColor,
                    background: backgroundColor,
                    width: parseFloat(width),
                    height: parseFloat(height)
                });
                console.log('Barcode rendered successfully for:', cleanValue);
            } catch (e) {
                console.error('Error rendering barcode:', e, 'Value:', value, 'Format:', format);
            }
        });
    };

    // Render barcodes when modal is shown - using Bootstrap 5 event
    document.addEventListener('DOMContentLoaded', function () {
        const cartModal = document.getElementById('cartModal');
        if (cartModal) {
            // Use Bootstrap 5 modal events
            cartModal.addEventListener('shown.bs.modal', function () {
                console.log('Cart modal shown, attempting to render barcodes...');
                // Try multiple times with increasing delays
                setTimeout(window.renderCartBarcodes, 100);
                setTimeout(window.renderCartBarcodes, 500);
                setTimeout(window.renderCartBarcodes, 1000);
            });
        }
    });

    // Attempt to render on page load as well
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(window.renderCartBarcodes, 500);
        });
    } else {
        setTimeout(window.renderCartBarcodes, 500);
    }
</script>