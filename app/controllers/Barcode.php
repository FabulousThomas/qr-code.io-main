<?php
require_once APPROOT . '/middleware/SecurityMiddleware.php';
require_once APPROOT . '/helpers/helpers.php';
// Logger wrapper (uses Monolog if available)
if (file_exists(APPROOT . '/libraries/Logger.php')) {
    require_once APPROOT . '/libraries/Logger.php';
}

// Load QR code library
if (file_exists(APPROOT . '/../vendor/autoload.php')) {
    require_once APPROOT . '/../vendor/autoload.php';
} else {
    require_once 'vendor/autoload.php';
}

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;

class Barcode extends Controller
{
    private $data = '';
    private $formData = '';
    // debug log file name
    private $debugLogFile = null;

    private function debugLog($message)
    {
        // Only log when APP_DEBUG is enabled (avoid storing sensitive data in production)
        $debugEnv = getenv('APP_DEBUG');
        if (!($debugEnv !== false && (string)$debugEnv === '1')) {
            return;
        }

        // Lazy init debug path
        if ($this->debugLogFile === null) {
            $logDir = APPROOT . '/../storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $this->debugLogFile = $logDir . '/barcode_debug.log';
        }

        $entry = '[' . date('Y-m-d H:i:s') . '] ' . trim((string) $message) . PHP_EOL;

        // Rotate/purge if file grows too large or too old
        try {
            if (file_exists($this->debugLogFile)) {
                $maxSize = 5 * 1024 * 1024; // 5MB
                $fileSize = filesize($this->debugLogFile) ?: 0;
                if ($fileSize > $maxSize) {
                    @rename($this->debugLogFile, $this->debugLogFile . '.1');
                } else {
                    // purge entries older than 30 days by truncating when mtime > 30 days
                    $mtime = filemtime($this->debugLogFile) ?: 0;
                    if (time() - $mtime > 30 * 24 * 3600) {
                        @unlink($this->debugLogFile);
                    }
                }
            }
        } catch (Exception $e) {
            // ignore rotation errors
        }

        @file_put_contents($this->debugLogFile, $entry, FILE_APPEND | LOCK_EX);
    }
    
    // Validate barcode value according to selected format
    private function validateBarcodeValue($value, $format)
    {
        $value = trim((string) $value);
        $format = strtoupper((string) $format);

        if ($value === '') {
            return ['valid' => false, 'message' => 'Please enter barcode content.'];
        }

        if ($format === 'EAN13') {
            if (!ctype_digit($value)) {
                return ['valid' => false, 'message' => 'EAN-13 must contain digits only.'];
            }
            $len = strlen($value);
            if ($len !== 12 && $len !== 13) {
                return ['valid' => false, 'message' => 'EAN-13 must be 12 or 13 digits long.'];
            }
        } elseif ($format === 'UPC') {
            if (!ctype_digit($value)) {
                return ['valid' => false, 'message' => 'UPC-A must contain digits only.'];
            }
            $len = strlen($value);
            if ($len !== 11) {
                return ['valid' => false, 'message' => 'UPC-A must be 11 digits long.'];
            }
        } elseif ($format === 'CODE39') {
            $upper = strtoupper($value);
            if (!preg_match('/^[A-Z0-9 \-\.\$\/\+\%]*$/', $upper)) {
                return ['valid' => false, 'message' => 'CODE-39 supports A–Z, 0–9 and - . space $ / + % only.'];
            }
        } elseif ($format === 'CODE128') {
            if (strlen($value) > 80) {
                return ['valid' => false, 'message' => 'CODE-128 content is too long. Please use 80 characters or fewer.'];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    // Generate a random barcode value that matches the selected format rules
    private function generateRandomValueForFormat($format)
    {
        $format = strtoupper((string) $format);

        if ($format === 'EAN13') {
            // Generate 12 digits + calculate check digit
            $digits = str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
            
            // Calculate check digit
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int)$digits[$i] * (($i % 2 === 0) ? 1 : 3);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            return $digits . $checkDigit;
        }

        if ($format === 'UPC') {
            // Generate 11 digits + calculate check digit
            $digits = str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
            
            // Calculate check digit
            $sum = 0;
            for ($i = 0; $i < 11; $i++) {
                $sum += (int)$digits[$i] * (($i % 2 === 0) ? 3 : 1);
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            return $digits . $checkDigit;
        }

        if ($format === 'CODE39') {
            // Allowed chars: A–Z, 0–9 and - . space $ / + %
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -.$/+%';
            $length = 8;
            $result = '';
            $maxIndex = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $idx = random_int(0, $maxIndex);
                $result .= $chars[$idx];
            }
            return $result;
        }

        // Default / CODE128: safe ASCII subset, moderate length
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = 10;
        $result = '';
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $idx = random_int(0, $maxIndex);
            $result .= $chars[$idx];
        }
        return $result;
    }

    // Generate QR code for automatic barcode tracking
    private function generateTrackingQrCode($trackingUrl, $barcode)
    {
        try {
            // Create QR code with tracking URL
            $qrCode = new QrCode(
                data: $trackingUrl,
                encoding: new Encoding('UTF-8'),
                size: 200,
                margin: 10,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255),
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            return $result->getDataUri();
        } catch (Exception $e) {
            // Return default QR code if generation fails
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
        }
    }

    public function __construct()
    {
    }
    public function index()
    {
        // Admin quick-inspect: show recent codes when requested.
        // Access rules:
        // - If APP_ADMIN_TOKEN is set, the caller must provide ?admin_token=... or X-ADMIN-TOKEN header matching it.
        // - Otherwise, allow only when APP_DEBUG=1.
        if (isset($_GET['recent_admin'])) {
            $adminToken = app_admin_token();
            $allowed = false;
            if (!empty($adminToken)) {
                $provided = $_GET['admin_token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null);
                if (!empty($provided) && hash_equals($adminToken, (string)$provided)) {
                    $allowed = true;
                }
            } else {
                if ((getenv('APP_DEBUG') !== false && (string)getenv('APP_DEBUG') === '1')) {
                    $allowed = true;
                }
            }

            if ($allowed) {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
                $model = $this->model('BarcodeQrModel');
                $codes = $model->getLatest($limit);
                $data = ['codes' => $codes];
                $this->view('barcode/recent_admin', $data);
                return;
            }

            http_response_code(403);
            echo 'Access denied.';
            return;
        }
        // When user returns to generator after a completed payment, start a fresh session for locking
        if (isset($_SESSION['payment_completed']) && $_SESSION['payment_completed']) {
            unset(
                $_SESSION['payment_completed'],
                $_SESSION['payment_reference'],
                $_SESSION['customer_email'],
                $_SESSION['payment_amount'],
                $_SESSION['payment_date']
            );
        }

        $this->formData = filteration($_REQUEST);

        // Initialize barcode value and customization options
        $this->data = [
            'numBarcodes' => $this->formData['numBarcodes'] ?? 1, // Default to 1 barcode;
            'format' => $this->formData['format'] ?? 'EAN13',
            'displayValue' => $this->formData['displayValue'] ?? 'true',
            'lineColor' => $this->formData['lineColor'] ?? '#000000',
            'backgroundColor' => $this->formData['backgroundColor'] ?? '#FFFFFF',
            'fontSize' => $this->formData['fontSize'] ?? '16',
            'width' => $this->formData['width'] ?? '2',
            'height' => $this->formData['height'] ?? '100',
            'imageFormat' => $this->formData['imageFormat'] ?? 'png',  // Default format is PNG
            'barcodeValue' => $this->formData['barcodeValue'] ?? '', // Default value
        ];
        $_SESSION['qrImageTimestamp'] = time(); // Store the current timestamp

        // Initialize the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Handle AJAX POST request for adding barcodes to cart (CHECK THIS FIRST!)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Prefer the centralized JSON parser helper
        $input = parse_json_request();
        $rawInput = file_get_contents('php://input');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($input)) {
            // Require CSRF token for JSON POST requests
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (empty($csrfToken) || !function_exists('csrf_verify') || !csrf_verify($csrfToken)) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'CSRF token invalid or missing']);
                exit;
            }

            // Log using wrapper so Monolog can be used when available
            if (class_exists('Logger')) {
                Logger::debug('Raw input: ' . $rawInput);
                Logger::debug('Decoded input: ' . print_r($input, true));
            } else {
                $this->debugLog("Raw input: " . $rawInput);
                $this->debugLog("Decoded input: " . print_r($input, true));
            }

            $barcodes = $input['barcodes'] ?? [];
            if (class_exists('Logger')) {
                Logger::debug('Barcodes array: ' . print_r($barcodes, true));
            } else {
                $this->debugLog("Barcodes array: " . print_r($barcodes, true));
            }

            if (empty($barcodes)) {
                header('Content-Type: application/json');
                $this->debugLog("ERROR: Barcodes array is empty!");
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No barcodes provided',
                    'debug' => [
                        'raw_input_length' => strlen($rawInput),
                        'decoded_input' => $input,
                        'barcodes' => $barcodes
                    ]
                ]);
                exit;
            }

            // Load the model
            $barcodeQrModel = $this->model('BarcodeQrModel');

            // Add the barcodes to the cart and save to database
            $savedIds = [];
            foreach ($barcodes as $barcode) {
                // Add type identifier for cart items
                $barcode['type'] = 'barcode';

                $format = isset($barcode['format']) ? strtoupper($barcode['format']) : 'EAN13';

                $itemSavedIds = [];

                // Save to database
                // Handle single or multiple barcode values
                $barcodeValues = [];
                if (is_array($barcode['barcodeValue'])) {
                    $barcodeValues = $barcode['barcodeValue'];
                } else {
                    // Remove suffix like "-1", "-2" for database storage, but keep the original in cart
                    $barcodeValue = $barcode['barcodeValue'];
                    if (strpos($barcodeValue, '-') !== false) {
                        $parts = explode('-', $barcodeValue);
                        $barcodeValue = $parts[0];
                    }
                    $barcodeValues = [$barcodeValue];
                }

                // Save each barcode value separately
                foreach ($barcodeValues as $barcodeVal) {
                    // Clean the barcode value - remove suffix
                    $cleanValue = $barcodeVal;
                    if (strpos($cleanValue, '-') !== false) {
                        $parts = explode('-', $cleanValue);
                        $cleanValue = $parts[0];
                    }

                    $validation = $this->validateBarcodeValue($cleanValue, $format);
                    if (!$validation['valid']) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'error',
                            'message' => $validation['message'],
                        ]);
                        exit;
                    }

                    $timestamp = time();
                    $randomId = uniqid();
                    $filename = "barcode-{$cleanValue}-{$timestamp}-{$randomId}.png";
                    $filePath = URLROOT . '/images/barimage/' . $filename;

                    // Generate actual barcode image data using JsBarcode on server side
                    $imageData = $this->generateBarcodeImageData($cleanValue, $barcode['format'] ?? 'EAN13', $barcode);

                    $saveData = [
                        'type' => 'barcode',
                        'value' => $cleanValue,
                        'format' => $barcode['format'] ?? 'EAN13',
                        'imageData' => $imageData, // Use actual image data, not file path
                        'imagePath' => $filePath, // Store file path separately
                        'customization' => [
                            'displayValue' => $barcode['displayValue'] ?? 'true',
                            'lineColor' => $barcode['lineColor'] ?? '#000000',
                            'backgroundColor' => $barcode['backgroundColor'] ?? '#FFFFFF',
                            'width' => $barcode['width'] ?? '2',
                            'height' => $barcode['height'] ?? '100',
                            'fontSize' => $barcode['fontSize'] ?? '16'
                        ]
                    ];

                    $id = $barcodeQrModel->save($saveData);
                    if ($id) {
                        $savedIds[] = $id;
                        $itemSavedIds[] = $id;
                        
                        // Create automatic tracking QR code for this barcode
                        $trackingUrl = URLROOT . "/analytics/track?id=" . $id;
                        $barcode['tracking_url'] = $trackingUrl;
                        $barcode['auto_qr_code'] = true;
                        
                        // Generate QR code that points to barcode analytics
                        $barcode['qr_tracking_image'] = $this->generateTrackingQrCode($trackingUrl, $barcode);
                    }
                }

                $barcode['saved_ids'] = $itemSavedIds;

                // Add to session cart with saved ids
                $_SESSION['cart'][] = $barcode;
            }

            // Store barcode values in session for preview
            $_SESSION['barcodeValue'] = [];
            foreach ($barcodes as $barcode) {
                if (is_array($barcode['barcodeValue'])) {
                    $_SESSION['barcodeValue'] = array_merge($_SESSION['barcodeValue'], $barcode['barcodeValue']);
                } else {
                    $_SESSION['barcodeValue'][] = $barcode['barcodeValue'];
                }
            }

            // Return JSON response for AJAX
            header('Content-Type: application/json');
            if (!empty($savedIds)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Barcodes added to cart. Refresh page to view',
                    'saved_ids' => $savedIds,
                    'barcode_values' => $_SESSION['barcodeValue'],
                    'customization' => $barcodes[0] // Send customization for preview
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Barcodes added to cart. Refresh page to view',
                    'barcode_values' => $_SESSION['barcodeValue'],
                    'customization' => $barcodes[0]
                ]);
            }
            exit;
        }

        // Generate random barcode values according to selected format
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check for non-JSON form submissions
            if (empty($rawInput) || strpos($contentType, 'application/json') === false) {
                if (!isset($_POST['csrf_token']) || !function_exists('csrf_verify') || !csrf_verify($_POST['csrf_token'])) {
                    alert('message', 'Invalid or missing CSRF token');
                    return;
                }
            }
            if (isset($this->formData['generateRandom'])) {
                $barcodeValues = [];
                for ($i = 0; $i < $this->data['numBarcodes']; $i++) {
                    $barcodeValues[] = $this->generateRandomValueForFormat($this->data['format']);
                }
                $_SESSION['barcodeValue'] = $barcodeValues;

                // Add to cart and database
                $barcodeQrModel = $this->model('BarcodeQrModel');
                $savedIds = [];

                // Create cart item
                $barcodeCount = count($barcodeValues);

                $cartItem = [
                    'type' => 'barcode',
                    'barcodeValue' => $barcodeCount === 1 ? $barcodeValues[0] : $barcodeValues,
                    'format' => $this->data['format'],
                    'displayValue' => $this->data['displayValue'],
                    'lineColor' => $this->data['lineColor'],
                    'backgroundColor' => $this->data['backgroundColor'],
                    'width' => $this->data['width'],
                    'height' => $this->data['height'],
                    'fontSize' => $this->data['fontSize'],
                    'randomGenerated' => true,
                    'randomGeneratedCount' => $barcodeCount
                ];

                // Save each barcode value to database (without image yet; image will be attached later)
                foreach ($barcodeValues as $barcodeVal) {
                    $format = $this->data['format'];

                    $validation = $this->validateBarcodeValue($barcodeVal, $format);
                    if (!$validation['valid']) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'error',
                            'message' => $validation['message'],
                        ]);
                        exit;
                    }

                    $saveData = [
                        'type' => 'barcode',
                        'value' => $barcodeVal,
                        'format' => $this->data['format'],
                        'imageData' => $this->generateBarcodeImageData($barcodeVal, $this->data['format'], [
                            'displayValue' => $this->data['displayValue'],
                            'lineColor' => $this->data['lineColor'],
                            'backgroundColor' => $this->data['backgroundColor'],
                            'width' => $this->data['width'],
                            'height' => $this->data['height'],
                            'fontSize' => $this->data['fontSize']
                        ]),
                        'customization' => [
                            'displayValue' => $this->data['displayValue'],
                            'lineColor' => $this->data['lineColor'],
                            'backgroundColor' => $this->data['backgroundColor'],
                            'width' => $this->data['width'],
                            'height' => $this->data['height'],
                            'fontSize' => $this->data['fontSize']
                        ]
                    ];

                    $id = $barcodeQrModel->save($saveData);
                    if ($id) {
                        $savedIds[] = $id;
                    }
                }

                $cartItem['saved_ids'] = $savedIds;

                // Store saved IDs in session so frontend (generateRandom flow) can attach images to existing records
                $_SESSION['barcode_saved_ids'] = $savedIds;

                // Store success message in session
                if (!empty($savedIds)) {
                    $_SESSION['success_message'] = count($barcodeValues) . ' barcode(s) generated and added to cart successfully!';

                    if (isset($_SESSION['success_message'])) {
                        alert('Success', $_SESSION['success_message']);
                        unset($_SESSION['success_message']);
                    }
                }

                // Add to session cart
                $_SESSION['cart'][] = $cartItem;

                // Use the user-input barcode value for all barcodes
            } elseif (isset($this->formData['barcodeValue'])) {
                $_SESSION['barcodeValue'] = array_fill(0, $this->data['numBarcodes'], $this->formData['barcodeValue']);

                // PHP logic to save the barcodes (for non-PDF formats)
            } elseif (isset($this->formData['imageData'])) {
                $imageData = $this->formData['imageData'];
                $imageFormat = $this->formData['imageFormat'];

                // Remove the "data:image/..." part
                $imageData = preg_replace('/^data:image\/(\w+);base64,/', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);

                // Decode the base64 data
                $imageData = base64_decode($imageData);

                // Ensure the "barcodes" folder exists
                if (!is_dir('barcodes')) {
                    mkdir('barcodes', 0755, true);
                }

                // Save the image to a file
                $filePath = APPROOT . '/images/barimage/barcode-' . $_SESSION['barcodeValue'] . '.' . $imageFormat; // Unique filename
                if (file_put_contents($filePath, $imageData)) {
                    echo "<p>Barcode saved as $filePath</p>";
                } else {
                    echo "<p>Failed to save the barcode.</p>";
                }
            }
        }

        // Check if the session variable should be unset
        if (isset($_SESSION['qrImageTimestamp'])) {
            $currentTime = time();
            $sessionTime = $_SESSION['qrImageTimestamp'];
            $timeoutDuration = 100; // 1 hour in seconds

            if ($currentTime - $sessionTime > $timeoutDuration) {
                unset($_SESSION['barcodeValue']);
                unset($_SESSION['qrImageTimestamp']);
            }
        }

        $this->view('barcode', $this->data);
    }

    // Remove item from cart
    public function removeItem()
    {
        // Set response header to JSON
        header('Content-Type: application/json');

        // Initialize the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Get the JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $index = isset($input['index']) ? (int) $input['index'] : null;

        // Validate index
        if ($index === null || $index < 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid item index'
            ]);
            exit;
        }

        // Check if cart is empty
        if (empty($_SESSION['cart'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
            exit;
        }

        // Check if index exists
        if (!isset($_SESSION['cart'][$index])) {
            echo json_encode([
                'success' => false,
                'message' => 'Item not found in cart'
            ]);
            exit;
        }

        $item = $_SESSION['cart'][$index];

        // Delete associated database records and images, if any
        try {
            $barcodeQrModel = $this->model('BarcodeQrModel');

            $idsToDelete = [];

            // For barcode items, we store saved IDs directly on the cart item
            if (!empty($item['saved_ids']) && is_array($item['saved_ids'])) {
                $idsToDelete = array_merge($idsToDelete, $item['saved_ids']);
            }

            // For QR code items, resolve saved ID from session mapping by value
            if (($item['type'] ?? '') === 'qrcode' && !empty($item['qrCodeValue']) && !empty($_SESSION['qr_saved_ids']) && is_array($_SESSION['qr_saved_ids'])) {
                $qrValue = $item['qrCodeValue'];
                if (!empty($_SESSION['qr_saved_ids'][$qrValue])) {
                    $idsToDelete[] = $_SESSION['qr_saved_ids'][$qrValue];
                    // Remove mapping so it doesn't linger
                    unset($_SESSION['qr_saved_ids'][$qrValue]);
                }
            }

            // Remove duplicates
            $idsToDelete = array_values(array_unique(array_filter($idsToDelete)));

            foreach ($idsToDelete as $codeId) {
                $code = $barcodeQrModel->getById($codeId);
                if ($code && !empty($code->image_path)) {
                    // Resolve physical path similarly to model logic
                    $imagePath = $code->image_path;

                    if (defined('URLROOT') && strpos($imagePath, URLROOT) === 0) {
                        $relative = substr($imagePath, strlen(URLROOT));
                        $imagePath = APPROOT . '/../public' . $relative;
                    } elseif (strpos($imagePath, APPROOT) !== 0) {
                        $imagePath = APPROOT . '/../public/' . ltrim($imagePath, '/');
                    }

                    if (file_exists($imagePath)) {
                        @unlink($imagePath);
                    }
                }
            }
        } catch (Exception $e) {
            // Fail silently for delete errors, but log for debugging
            error_log('Error deleting code(s) for cart item: ' . $e->getMessage());
        }

        // Remove the item from cart
        unset($_SESSION['cart'][$index]);

        // Reindex the array to maintain sequential keys
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        // Generate updated cart HTML
        $cartHtml = $this->getCartHtml();
        $hasItems = !empty($_SESSION['cart']);

        // Return success response with updated cart HTML
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'cartHtml' => $cartHtml,
            'hasItems' => $hasItems
        ]);
        exit;
    }

    // Verify OPay payment (stub – fill in OPay API call using their docs)
    public function verifyOpayPayment()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $reference = $input['reference'] ?? null;
        $email = $input['email'] ?? null;

        if (!$reference || !$email) {
            echo json_encode([
                'success' => false,
                'message' => 'Payment reference and email are required'
            ]);
            exit;
        }

        try {
            // TODO: Call OPay verify API here with your secret key.
            // Dummy credentials for development – replace with real values from OPay dashboard
            $opaySecretKey = 'OPAY_TEST_SECRET_KEY_XXXX';
            $opayMerchantId = 'OPAY_TEST_MERCHANT_ID_XXXX';

            // Example steps:
            // 1. Send GET/POST to OPay verify endpoint with $reference using $opaySecretKey / $opayMerchantId.
            // 2. Parse JSON response and set $paymentSuccessful and $amountInNaira accordingly.

            $paymentSuccessful = false; // <- replace with real check
            $amountInNaira = 0;         // <- replace with real amount from OPay

            if (!$paymentSuccessful) {
                echo json_encode([
                    'success' => false,
                    'message' => 'OPay payment verification failed'
                ]);
                exit;
            }

            // Reuse existing unlock logic from verifyPayment
            $_SESSION['payment_completed'] = true;
            $_SESSION['payment_reference'] = $reference;
            $_SESSION['customer_email'] = $email;
            $_SESSION['payment_amount'] = $amountInNaira;
            $_SESSION['payment_date'] = date('Y-m-d H:i:s');

            // Regenerate session ID after successful payment
            $oldSessionId = session_id();
            $security = new SecurityMiddleware();
            $security->regenerateSessionId();
            $newSessionId = session_id();

            // Collect IDs of codes in the current cart
            $codeIds = [];
            if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    // First priority: Check for direct db_id in cart items
                    if (!empty($item['db_id'])) {
                        $codeIds[] = $item['db_id'];
                    }
                    
                    // For barcode items, get saved_ids directly
                    if (!empty($item['saved_ids']) && is_array($item['saved_ids'])) {
                        $codeIds = array_merge($codeIds, $item['saved_ids']);
                    }
                    
                    // For QR code items, resolve saved ID from session mapping by value
                    if (($item['type'] ?? '') === 'qrcode' && !empty($item['qrCodeValue']) && !empty($_SESSION['qr_saved_ids']) && is_array($_SESSION['qr_saved_ids'])) {
                        $qrValue = $item['qrCodeValue'];
                        if (!empty($_SESSION['qr_saved_ids'][$qrValue])) {
                            $codeIds[] = $_SESSION['qr_saved_ids'][$qrValue];
                        } else {
                            // Try to find by checking if any saved ID value matches the tracking URL pattern
                            foreach ($_SESSION['qr_saved_ids'] as $savedKey => $savedId) {
                                if (strpos($qrValue, 'analytics/track?id=' . $savedId) !== false || 
                                    strpos($savedKey, 'analytics/track?id=' . $savedId) !== false) {
                                    $codeIds[] = $savedId;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $codeIds = array_values(array_unique(array_filter($codeIds)));

            // Create or find user by email and attach only current-cart codes
            $userModel = $this->model('User');
            $user = $userModel->findOrCreateByEmail($email);
            $barcodeQrModel = $this->model('BarcodeQrModel');

            if (!empty($codeIds)) {
                if ($user && isset($user->id)) {
                    $_SESSION['user_id'] = $user->id;
                    $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, $user->id, $email);
                } else {
                    $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, 0, $email);
                }
            } else {
                // Fallback: update by session if we have no IDs
                if ($user && isset($user->id)) {
                    $_SESSION['user_id'] = $user->id;
                    $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), $user->id, $email);
                } else {
                    $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), 0, $email);
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'OPay payment verified successfully',
                'amount' => $amountInNaira,
                'reference' => $reference
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error verifying OPay payment: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Save barcode image as file on server
    public function saveImageFile()
    {
        header('Content-Type: application/json');

        // Get the JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $barcodeId = $input['id'] ?? null;
        $value = $input['value'] ?? null;
        $imageData = $input['imageData'] ?? null;

        if (!$imageData || !$value) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid parameters'
            ]);
            exit;
        }

        try {
            $barcodeQrModel = $this->model('BarcodeQrModel');

            if ($barcodeId) {
                // Update existing barcode record with image data
                $updated = $barcodeQrModel->updateImage($barcodeId, $imageData);

                if (!$updated) {
                    throw new Exception('Failed to store image data for existing barcode');
                }
            } else {
                // No ID provided (e.g. generateRandom flow) - create a new record with this image
                $format = $input['format'] ?? 'EAN13';
                $customization = [
                    'displayValue' => $input['displayValue'] ?? 'true',
                    'lineColor' => $input['lineColor'] ?? '#000000',
                    'backgroundColor' => $input['backgroundColor'] ?? '#FFFFFF',
                    'width' => $input['width'] ?? '2',
                    'height' => $input['height'] ?? '100',
                    'fontSize' => $input['fontSize'] ?? '16',
                ];

                $saveData = [
                    'type' => 'barcode',
                    'value' => $value,
                    'format' => $format,
                    'imageData' => $imageData,
                    'customization' => $customization,
                ];

                $newId = $barcodeQrModel->save($saveData);
                if (!$newId) {
                    throw new Exception('Failed to create barcode record with image');
                }

                $barcodeId = $newId;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Barcode image saved successfully',
                'id' => $barcodeId,
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error saving barcode image: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Save barcode image to database (legacy method)
    public function saveImage()
    {
        header('Content-Type: application/json');

        // Get the JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $barcodeId = $input['id'] ?? null;
        $imageData = $input['imageData'] ?? null;

        if (!$barcodeId || !$imageData) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid parameters'
            ]);
            exit;
        }

        // Load the model and update the barcode with image data
        $barcodeQrModel = $this->model('BarcodeQrModel');
        $result = $barcodeQrModel->updateImage($barcodeId, $imageData);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Barcode image saved successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to save barcode image'
            ]);
        }
        exit;
    }

    // Verify Paystack payment
    public function verifyPayment()
    {
        header('Content-Type: application/json');

        // Get input
        $input = json_decode(file_get_contents('php://input'), true);
        $reference = $input['reference'] ?? null;
        $email = $input['email'] ?? null;

        if (!$reference) {
            echo json_encode([
                'success' => false,
                'message' => 'Payment reference is required'
            ]);
            exit;
        }

        try {
            // Paystack Secret Key from environment
            $paystackSecretKey = $_ENV['PAYSTACK_SECRET'] ?: '';
            if ($paystackSecretKey === '') {
                throw new Exception('Server is not configured with PAYSTACK_SECRET');
            }

            // Verify payment with Paystack API
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $paystackSecretKey,
                    "Cache-Control: no-cache",
                ),
            ));
            // Enforce SSL verification in non-local environments
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $isLocal = in_array($host, ['localhost', '127.0.0.1'], true);
            $debug = (getenv('APP_DEBUG') ?: '0') === '1';
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $isLocal ? false : true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $isLocal ? 0 : 2);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            // Handle cURL SSL issues more gracefully in local development (only when APP_DEBUG=1)
            if ($err) {
                if ($isLocal && $debug && strpos($err, 'SSL certificate problem') !== false) {
                    // Local-only fallback: assume success so that the rest of the
                    // payment flow (unlocking codes) can be tested without valid SSL
                    $_SESSION['payment_completed'] = true;
                    $_SESSION['payment_reference'] = $reference;
                    $_SESSION['customer_email'] = $email;
                    $_SESSION['payment_amount'] = 0;
                    $_SESSION['payment_date'] = date('Y-m-d H:i:s');

                    // Regenerate session ID after successful payment
                    $security = new SecurityMiddleware();
                    $security->regenerateSessionId();

                    // Collect IDs of codes in the current cart
                    $codeIds = [];
                    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            // For barcode items, get saved_ids directly
                            if (!empty($item['saved_ids']) && is_array($item['saved_ids'])) {
                                $codeIds = array_merge($codeIds, $item['saved_ids']);
                            }
                            
                            // For QR code items, resolve saved ID from session mapping by value
                            if (($item['type'] ?? '') === 'qrcode' && !empty($item['qrCodeValue']) && !empty($_SESSION['qr_saved_ids']) && is_array($_SESSION['qr_saved_ids'])) {
                                $qrValue = $item['qrCodeValue'];
                                if (!empty($_SESSION['qr_saved_ids'][$qrValue])) {
                                    $codeIds[] = $_SESSION['qr_saved_ids'][$qrValue];
                                }
                            }
                        }
                    }

                    $codeIds = array_values(array_unique(array_filter($codeIds)));

                    // Create or find user by email and attach only current-cart codes
                    $userModel = $this->model('User');
                    $user = $userModel->findOrCreateByEmail($email);
                    $barcodeQrModel = $this->model('BarcodeQrModel');
                    if (!empty($codeIds)) {
                        if ($user && isset($user->id)) {
                            $_SESSION['user_id'] = $user->id;
                            $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, $user->id, $email);
                        } else {
                            $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, 0, $email);
                        }
                    } else {
                        // Fallback: update by session if we have no IDs
                        if ($user && isset($user->id)) {
                            $_SESSION['user_id'] = $user->id;
                            $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), $user->id, $email);
                        } else {
                            $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), 0, $email);
                        }
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => 'Payment treated as verified in local environment (SSL issue)',
                        'amount' => 0,
                        'reference' => $reference
                    ]);
                    exit;
                }

                throw new Exception("cURL Error: " . $err);
            }

            if ($response === false || $response === null) {
                throw new Exception('No response from Paystack');
            }

            $result = json_decode($response, true);
            if (!is_array($result)) {
                throw new Exception('Invalid JSON from Paystack');
            }

            // Check if payment was successful
            if ($result['status'] && $result['data']['status'] === 'success') {
                // Payment successful - unlock codes
                $_SESSION['payment_completed'] = true;
                $_SESSION['payment_reference'] = $reference;
                $_SESSION['customer_email'] = $email;
                $_SESSION['payment_amount'] = $result['data']['amount'] / 100; // Convert from kobo
                $_SESSION['payment_date'] = date('Y-m-d H:i:s');

                // Regenerate session ID after successful payment
                $security = new SecurityMiddleware();
                $security->regenerateSessionId();

                // Collect IDs of codes in the current cart
                $codeIds = [];
                if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        // First priority: Check for direct db_id in cart items
                        if (!empty($item['db_id'])) {
                            $codeIds[] = $item['db_id'];
                        }
                        
                        // For barcode items, get saved_ids directly
                        if (!empty($item['saved_ids']) && is_array($item['saved_ids'])) {
                            $codeIds = array_merge($codeIds, $item['saved_ids']);
                        }
                        
                        // For QR code items, resolve saved ID from session mapping by value
                        if (($item['type'] ?? '') === 'qrcode' && !empty($item['qrCodeValue']) && !empty($_SESSION['qr_saved_ids']) && is_array($_SESSION['qr_saved_ids'])) {
                            $qrValue = $item['qrCodeValue'];
                            if (!empty($_SESSION['qr_saved_ids'][$qrValue])) {
                                $codeIds[] = $_SESSION['qr_saved_ids'][$qrValue];
                            } else {
                                // Try to find by checking if any saved ID value matches the tracking URL pattern
                                foreach ($_SESSION['qr_saved_ids'] as $savedKey => $savedId) {
                                    if (strpos($qrValue, 'analytics/track?id=' . $savedId) !== false || 
                                        strpos($savedKey, 'analytics/track?id=' . $savedId) !== false) {
                                        $codeIds[] = $savedId;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $codeIds = array_values(array_unique(array_filter($codeIds)));

                // Create or find user by email and attach only current-cart codes
                $userModel = $this->model('User');
                $user = $userModel->findOrCreateByEmail($email);
                $barcodeQrModel = $this->model('BarcodeQrModel');
                if (!empty($codeIds)) {
                    if ($user && isset($user->id)) {
                        $_SESSION['user_id'] = $user->id;
                        $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, $user->id, $email);
                    } else {
                        $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, 0, $email);
                    }
                } else {
                    // Fallback: update by session if we have no IDs
                    if ($user && isset($user->id)) {
                        $_SESSION['user_id'] = $user->id;
                        $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), $user->id, $email);
                    } else {
                        $barcodeQrModel->attachSessionCodesToUserAndEmail(session_id(), 0, $email);
                    }
                }
                $paymentData = [
                    'reference' => $reference,
                    'email' => $email,
                    'amount' => $result['data']['amount'] / 100,
                    'currency' => $result['data']['currency'],
                    'status' => 'success',
                    'cart_items' => json_encode($_SESSION['cart'] ?? []),
                    'payment_date' => date('Y-m-d H:i:s')
                ];

                // Note: You'll need to add a savePayment method to your model
                // $barcodeQrModel->savePayment($paymentData);

                echo json_encode([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'amount' => $result['data']['amount'] / 100,
                    'reference' => $reference
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Payment verification failed'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Payment success page
    public function paymentSuccess()
    {
        // Check if payment was completed
        if (!isset($_SESSION['payment_completed']) || !$_SESSION['payment_completed']) {
            // Redirect to barcode page if no payment
            header('Location: ' . URLROOT . '/barcode');
            exit;
        }

        $barcodeQrModel = $this->model('BarcodeQrModel');
        $sessionId = session_id();
        if (!empty($_SESSION['user_id'])) {
            $codes = $barcodeQrModel->getByUser($_SESSION['user_id']);
        } else {
            $codes = $barcodeQrModel->getBySession($sessionId);
        }
        $codesWithImages = [];

        if (!empty($codes)) {
            foreach ($codes as $code) {
                $imageDataUri = $barcodeQrModel->getImageDataUri($code);
                if (empty($imageDataUri)) {
                    continue;
                }

                $codesWithImages[] = [
                    'id' => $code->id,
                    'type' => $code->type,
                    'format' => $code->format,
                    'value' => $code->value,
                    'image_data_uri' => $imageDataUri,
                    'image_path' => $code->image_path,
                ];
            }
        }

        $data = [
            'title' => 'Payment Successful',
            'payment_reference' => $_SESSION['payment_reference'] ?? 'N/A',
            'payment_amount' => $_SESSION['payment_amount'] ?? 0,
            'customer_email' => $_SESSION['customer_email'] ?? 'N/A',
            'payment_date' => $_SESSION['payment_date'] ?? date('Y-m-d H:i:s'),
            'cart_items' => $_SESSION['cart'] ?? [],
            'codes' => $codesWithImages,
        ];

        // Clear cart after successful payment so user starts fresh
        unset($_SESSION['cart']);

        // Load success view
        $this->view('barcode/payment-success', $data);
    }

    // List all codes for the logged-in user
    public function myCodes()
    {
        if (empty($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $barcodeQrModel = $this->model('BarcodeQrModel');
        $codes = $barcodeQrModel->getByUser($_SESSION['user_id'] ?? '');
        $codesWithImages = [];

        if (!empty($codes)) {
            foreach ($codes as $code) {
                $imageDataUri = $barcodeQrModel->getImageDataUri($code);
                if (empty($imageDataUri)) {
                    continue;
                }

                $codesWithImages[] = [
                    'id' => $code->id,
                    'type' => $code->type,
                    'format' => $code->format,
                    'value' => $code->value,
                    'image_data_uri' => $imageDataUri,
                    'image_path' => $code->image_path,
                ];
            }
        }

        $data = [
            'title' => 'My Codes',
            'codes' => $codesWithImages,
        ];

        $this->view('users/myCodes', $data);
    }

    public function downloadAll()
    {
        if (!isset($_SESSION['payment_completed']) || !$_SESSION['payment_completed']) {
            header('Location: ' . URLROOT . '/barcode');
            exit;
        }

        $barcodeQrModel = $this->model('BarcodeQrModel');
        $sessionId = session_id();
        if (!empty($_SESSION['user_id'])) {
            $codes = $barcodeQrModel->getByUser($_SESSION['user_id']);
        } else {
            $codes = $barcodeQrModel->getBySession($sessionId);
        }

        if (empty($codes)) {
            header('Location: ' . URLROOT . '/barcode/paymentSuccess');
            exit;
        }

        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'codes_');

        if ($zip->open($tmpFile, \ZipArchive::OVERWRITE) !== true) {
            header('Location: ' . URLROOT . '/barcode/paymentSuccess');
            exit;
        }

        foreach ($codes as $code) {
            if (empty($code->image_path)) {
                continue;
            }

            $imagePath = $code->image_path;

            if (defined('URLROOT') && strpos($imagePath, URLROOT) === 0) {
                $relative = substr($imagePath, strlen(URLROOT));
                $filePath = APPROOT . '/../public' . $relative;
            } elseif (strpos($imagePath, APPROOT) === 0) {
                $filePath = $imagePath;
            } else {
                $filePath = APPROOT . '/../public/' . ltrim($imagePath, '/');
            }

            if (!file_exists($filePath)) {
                continue;
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $safeValue = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $code->value);
            $filename = ($code->type ?: 'code') . '-' . $safeValue;
            if (!empty($code->format)) {
                $filename .= '-' . $code->format;
            }
            $filename .= '.' . ($extension ?: 'png');

            $zip->addFile($filePath, $filename);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="codes-' . date('Ymd-His') . '.zip"');
        header('Content-Length: ' . filesize($tmpFile));

        readfile($tmpFile);
        @unlink($tmpFile);
        exit;
    }

    // Get cart HTML content for modal
    private function getCartHtml()
    {
        ob_start();

        if (empty($_SESSION['cart'])) {
            ?>
            <div class='text-center py-5'>
                <div class='mb-3'>
                    <i class='las la-shopping-cart' style='font-size: 4rem; color: #ccc;'></i>
                </div>
                <h5 class='text-muted mb-2'>Your cart is empty!</h5>
                <p class='text-muted mb-0'>Add some QR codes or barcodes to your cart to get started.</p>
            </div>
            <?php
            return ob_get_clean();
        }

        $cartItems = $_SESSION['cart'];
        ?>
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
                    <?php foreach ($cartItems as $index => $item): ?>
                        <tr class="cart-item">
                            <td class="fw-semibold">Item #<?= ($index + 1) ?></td>
                            <td>
                                <?php if (!empty($item['type'])): ?>
                                    <span class="badge bg-info text-uppercase"><?= htmlspecialchars($item['type']) ?></span>
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
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="las la-qrcode text-warning"></i>
                                            <strong class="text-white mb-0">QR Code</strong>
                                            <span class="badge bg-dark border border-warning text-warning">Locked</span>
                                        </div>
                                        <div class="text-white-50">
                                            <i class="las la-lock me-1"></i>Available after payment
                                        </div>
                                        <?php if (!empty($item['qrCodeValue'])): ?>
                                            <div class="text-white-50">
                                                <i class="las la-database me-1"></i>Data hidden until checkout completes
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['randomGenerated'])): ?>
                                            <div class="text-white-50">
                                                <i class="las la-random me-1"></i><?= (int) ($item['randomGeneratedCount'] ?? 1) ?> random
                                                value(s)
                                            </div>
                                        <?php endif; ?>
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
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge"
                                                style="background: <?= htmlspecialchars($lineColor) ?>; color: <?= $lineColor === '#FFFFFF' ? '#000' : '#FFF' ?>;">Line</span>
                                            <span class="badge"
                                                style="background: <?= htmlspecialchars($backgroundColor) ?>; color: <?= $backgroundColor === '#FFFFFF' ? '#000' : '#FFF' ?>;">Bg</span>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($width) ?> ×
                                                <?= htmlspecialchars($height) ?> px</span>
                                            <?php if (!$displayValue): ?>
                                                <span class="badge bg-dark border border-secondary text-white-50">Value Hidden</span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['randomGenerated'])): ?>
                                                <span class="badge bg-warning text-dark">Random</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-white-50">
                                            <i class="las la-barcode me-1"></i><?= $barcodeCount ?>
                                            barcode<?= $barcodeCount > 1 ? 's' : '' ?> locked
                                        </div>
                                        <div class="text-white-50">
                                            <i class="las la-lock me-1"></i>Values revealed after payment
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

        <?php
        $totalQRCodes = 0;
        $totalBarcodes = 0;
        $randomDiscountEligible = 0;

        foreach ($cartItems as $item) {
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
                                <small class='d-block text-success'>Includes ₦<?= number_format($discountAmount) ?> discount (20%
                                    off up to 20 random barcodes)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
    
    /**
     * Generate barcode image data on server side
     */
    private function generateBarcodeImageData($value, $format, $options = [])
    {
        try {
            // Use a simple approach - generate SVG barcode and convert to PNG
            $width = $options['width'] ?? 2;
            $height = $options['height'] ?? 100;
            $lineColor = $options['lineColor'] ?? '#000000';
            $backgroundColor = $options['backgroundColor'] ?? '#FFFFFF';
            $displayValue = ($options['displayValue'] ?? 'true') === 'true';
            
            // Generate a simple placeholder barcode
            return $this->generatePlaceholderBarcode($value, $format);
            
        } catch (Exception $e) {
            error_log("Failed to generate barcode image data: " . $e->getMessage());
            return $this->generatePlaceholderBarcode($value, $format);
        }
    }
    
    /**
     * Generate placeholder barcode
     */
    private function generatePlaceholderBarcode($value, $format)
    {
        // Generate a simple placeholder image
        $image = imagecreatetruecolor(200, 100);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        
        imagefill($image, 0, 0, $bgColor);
        imagestring($image, 3, 10, 30, $format, $textColor);
        imagestring($image, 2, 10, 50, substr($value, 0, 15), $textColor);
        
        ob_start();
        imagepng($image);
        imagedestroy($image);
        $pngData = ob_get_clean();
        
        return 'data:image/png;base64,' . base64_encode($pngData);
    }
}
