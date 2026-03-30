<?php

class PaymentController extends Controller {
    private $security;
    
    public function __construct() {
        require_once APPROOT . '/middleware/SecurityMiddleware.php';
        $this->security = new SecurityMiddleware();
        $this->security->applySecurityHeaders();
        
        // Apply rate limiting to all payment endpoints
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '127.0.0.1';
        $this->security->checkRateLimit($clientIp);
        
        // Initialize secure session
        $this->initSecureSession();
    }
    
    private function initSecureSession() {
        // Only set cookie params if session is not active
        if (session_status() === PHP_SESSION_NONE) {
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $httponly = true;
            
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $cookieParams['lifetime'],
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }
    
    public function verifyPayment() {
        header('Content-Type: application/json');
        
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !function_exists('csrf_verify') || !csrf_verify($_POST['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF token']);
            return;
        }
        
        $reference = filter_input(INPUT_POST, 'reference', FILTER_SANITIZE_STRING);
        if (empty($reference)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment reference']);
            return;
        }
        
        try {
            // Verify payment with payment provider
            $verified = $this->verifyWithProvider($reference);
            
            if ($verified) {
                // Mark payment as completed
                $_SESSION['payment_completed'] = true;
                $_SESSION['payment_reference'] = $reference;
                
                // Transfer ownership of QR codes to logged-in user
                if (isset($_SESSION['user_id']) && isset($_SESSION['email']) && !empty($_SESSION['cart'])) {
                    $barcodeQrModel = $this->model('BarcodeQrModel');
                    
                    // Collect IDs of codes in the current cart
                    $codeIds = [];
                    foreach ($_SESSION['cart'] as $item) {
                        // First priority: Check for direct db_id in cart items
                        if (!empty($item['db_id'])) {
                            $codeIds[] = $item['db_id'];
                        }
                        
                        // Second priority: For QR code items, get saved ID from session mapping
                        if (($item['type'] ?? '') === 'qrcode' && !empty($item['qrCodeValue']) && !empty($_SESSION['qr_saved_ids']) && is_array($_SESSION['qr_saved_ids'])) {
                            $qrValue = $item['qrCodeValue'];
                            
                            // Try exact match first
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
                        
                        // For barcode items, get saved_ids directly
                        if (!empty($item['saved_ids']) && is_array($item['saved_ids'])) {
                            $codeIds = array_merge($codeIds, $item['saved_ids']);
                        }
                    }
                    
                    $codeIds = array_values(array_unique(array_filter($codeIds)));
                    
                    // Transfer ownership to logged-in user
                    if (!empty($codeIds)) {
                        $barcodeQrModel->attachCodesToUserAndEmailByIds($codeIds, $_SESSION['user_id'], $_SESSION['email']);
                        error_log("Transferred ownership of " . count($codeIds) . " codes to user {$_SESSION['user_id']} ({$_SESSION['email']})");
                    }
                }
                
                // Clear cart after successful payment
                if (isset($_SESSION['cart'])) {
                    unset($_SESSION['cart']);
                }
                
                // Regenerate session ID after payment
                $oldSessionId = session_id();
                $this->security->regenerateSessionId();
                $newSessionId = session_id();
                
                // Log session regeneration for debugging
                error_log("Payment successful - Session ID regenerated from: $oldSessionId to: $newSessionId");
                
                echo json_encode(['success' => true, 'message' => 'Payment verified successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Payment verification failed']);
            }
            
        } catch (Exception $e) {
            error_log('Payment verification error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while verifying payment']);
        }
    }
    
    private function verifyWithProvider($reference) {
        // Implement actual payment provider verification here
        // This is a placeholder implementation
        $apiKey = $_ENV['PAYSTACK_SECRET'];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $apiKey,
                "Cache-Control: no-cache"
            ],
            CURLOPT_SSL_VERIFYPEER => $_ENV['APP_ENV'] === 'production' ?? false,
            CURLOPT_SSL_VERIFYHOST => $_ENV['APP_ENV'] === 'production' ? 2 : 0
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        if ($err) {
            error_log("cURL Error #:" . $err);
            return false;
        }
        
        $result = json_decode($response);
        return $result && $result->status && $result->data->status === 'success';
    }
}
