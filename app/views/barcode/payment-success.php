<!-- HEAD -->
<?php include APPROOT . '/views/inc/head.php'; ?>

<body>
    <!-- NAVBAR -->
    <?php include APPROOT . '/views/inc/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Success Message -->
                <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
                    <div class="card-body p-5 text-center">
                        <!-- Success Icon -->
                        <div class="mb-4">
                            <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px;">
                                <i class="las la-check text-white" style="font-size: 4rem;"></i>
                            </div>
                        </div>

                        <!-- Success Title -->
                        <h2 class="text-success mb-3">Payment Successful!</h2>
                        <p class="text-muted mb-4">
                            Thank you for your purchase. Your payment has been processed successfully.
                        </p>

                        <!-- Payment Details -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <div class="row text-start">
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Reference Number</small>
                                        <strong class="text-dark"><?= htmlspecialchars($data['payment_reference']) ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Amount Paid</small>
                                        <strong class="text-success">₦<?= number_format($data['payment_amount'], 2) ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Email</small>
                                        <strong class="text-dark"><?= htmlspecialchars($data['customer_email']) ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Date</small>
                                        <strong class="text-dark"><?= date('M d, Y H:i', strtotime($data['payment_date'])) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Purchased Items (Summary) -->
                        <div class="card border-0 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="las la-box"></i> Your Purchased Items</h5>
                            </div>
                            <div class="card-body text-start">
                                <?php if (!empty($data['cart_items'])): ?>
                                    <?php foreach ($data['cart_items'] as $index => $item): ?>
                                        <div class="mb-3 p-3 border rounded bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge bg-success me-2">Item #<?= ($index + 1) ?></span>
                                                    <?php if (isset($item['type'])): ?>
                                                        <span class="badge bg-info"><?= strtoupper($item['type']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (isset($item['format'])): ?>
                                                        <span class="badge bg-secondary ms-1"><?= htmlspecialchars($item['format']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php if (isset($item['type']) && $item['type'] === 'qrcode'): ?>
                                                <p class="mb-0 text-muted">QR code purchased. Full image is available in the codes list below.</p>
                                            <?php elseif (isset($item['barcodeValue'])): ?>
                                                <?php if (is_array($item['barcodeValue'])): ?>
                                                    <p class="mb-1"><strong>Count:</strong> <?= count($item['barcodeValue']) ?> barcodes</p>
                                                <?php else: ?>
                                                    <p class="mb-0"><strong>Value:</strong> <code><?= htmlspecialchars($item['barcodeValue']) ?></code></p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No items found.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Your Codes (Images & Downloads) -->
                        <div class="card border-0 mb-4">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="las la-qrcode"></i> Your Codes</h5>
                                <small class="text-white-50">Visible because your payment was successful</small>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($data['codes'])): ?>
                                    <div class="row g-3">
                                        <?php foreach ($data['codes'] as $code): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="border rounded p-2 h-100 d-flex flex-column align-items-center justify-content-between">
                                                    <div class="w-100 text-center mb-2">
                                                        <img src="<?= htmlspecialchars($code['image_data_uri']) ?>" alt="Code Image"
                                                            class="img-fluid" style="max-height: 200px; object-fit: contain;">
                                                    </div>
                                                    <div class="w-100 small text-muted mb-2">
                                                        <div><strong>Type:</strong> <?= htmlspecialchars(strtoupper($code['type'])) ?></div>
                                                        <?php if (!empty($code['format'])): ?>
                                                            <div><strong>Format:</strong> <?= htmlspecialchars($code['format']) ?></div>
                                                        <?php endif; ?>
                                                        <div class="text-truncate"><strong>Value:</strong> <code><?= htmlspecialchars($code['value']) ?></code></div>
                                                    </div>
                                                    <?php if (!empty($code['image_path'])): ?>
                                                        <?php
                                                        $downloadHref = $code['image_path'];
                                                        if (defined('URLROOT')) {
                                                            if (strpos($downloadHref, URLROOT) !== 0) {
                                                                $downloadHref = rtrim(URLROOT, '/') . '/' . ltrim($downloadHref, '/');
                                                            }
                                                        }
                                                        ?>
                                                        <a href="<?= htmlspecialchars($downloadHref) ?>" class="btn btn-outline-success btn-sm w-100 mt-1" download>
                                                            <i class="las la-download"></i> Download
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No codes were found for this session.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="<?= URLROOT ?>/barcode/downloadAll" class="btn btn-success btn-lg">
                                <i class="las la-download"></i> Download All Codes
                            </a>
                            <a href="<?= URLROOT ?>/barcode/myCodes" class="btn btn-outline-success btn-lg">
                                <i class="las la-folder-open"></i> View All My Codes
                            </a>
                            <a href="<?= URLROOT ?>/barcode" class="btn btn-outline-primary btn-lg">
                                <i class="las la-plus"></i> Generate More
                            </a>
                        </div>

                        <!-- Note -->
                        <div class="alert alert-info mt-4" role="alert">
                            <i class="las la-info-circle"></i>
                            <strong>Note:</strong> A receipt has been sent to your email address. 
                            You can download your codes anytime by logging in with your email.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include APPROOT . '/views/inc/footer.php'; ?>

    <!-- SCRIPTS -->
    <?php include APPROOT . '/views/inc/script-links.php'; ?>
</body>

</html>
