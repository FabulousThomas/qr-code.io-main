<?php

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

class Analytics extends Controller
{
    private $analyticsModel;
    private $barcodeQrModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        
        $this->analyticsModel = $this->model('AnalyticsModel');
        $this->barcodeQrModel = $this->model('BarcodeQrModel');
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        
        // Get analytics data with null checks
        $overallStats = $this->analyticsModel->getOverallStats($userId) ?? (object)[
            'total_qr_codes' => 0,
            'total_scans' => 0,
            'avg_scans_per_qr' => 0,
            'max_scans' => 0,
            'unique_countries' => 0
        ];
        
        $recentScans = $this->analyticsModel->getRecentScans($userId);
        $topQrCodes = $this->analyticsModel->getTopPerformingQrCodes($userId);
        $scanTrends = $this->analyticsModel->getScanTrends($userId);
        $locationStats = $this->analyticsModel->getLocationStats($userId);
        
        $data = [
            'overall_stats' => $overallStats,
            'recent_scans' => $recentScans,
            'top_qr_codes' => $topQrCodes,
            'scan_trends' => $scanTrends,
            'location_stats' => $locationStats,
            'page_title' => 'QR Code Analytics Dashboard'
        ];

        $this->view('users/analytics', $data);
    }

    public function qrCode($qrCodeId)
    {
        $userId = $_SESSION['user_id'];
        
        // Verify user owns this QR code
        $qrCode = $this->barcodeQrModel->getQrCodeById($qrCodeId);
        if (!$qrCode || $qrCode->user_id != $userId) {
            redirect('analytics');
        }

        $qrStats = $this->analyticsModel->getQrCodeStats($qrCodeId);
        $scanEvents = $this->analyticsModel->getScanEvents($qrCodeId);
        
        $data = [
            'qr_code' => $qrCode,
            'qr_stats' => $qrStats,
            'scan_events' => $scanEvents,
            'page_title' => 'QR Code Analytics - ' . substr($qrCode->value, 0, 30) . '...'
        ];

        $this->view('analytics/qr-details', $data);
    }

    public function export()
    {
        $userId = $_SESSION['user_id'];
        $format = $_GET['format'] ?? 'csv';
        
        if ($format === 'csv') {
            $this->exportCsv($userId);
        } elseif ($format === 'json') {
            $this->exportJson($userId);
        } else {
            redirect('analytics');
        }
    }

    private function exportCsv($userId)
    {
        $qrStats = $this->analyticsModel->getUserQrCodeStats($userId);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="qr_analytics_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, ['QR Code ID', 'QR Code Value', 'Type', 'Scan Count', 'Last Scanned', 'First Scanned', 'Country', 'City']);
        
        // CSV data
        foreach ($qrStats as $stat) {
            fputcsv($output, [
                $stat->qr_code_id,
                $stat->qr_value,
                $stat->qr_type,
                $stat->scan_count,
                $stat->last_scanned,
                $stat->first_scanned,
                $stat->country ?? 'Unknown',
                $stat->city ?? 'Unknown'
            ]);
        }
        
        fclose($output);
        exit;
    }

    private function exportJson($userId)
    {
        $data = [
            'overall_stats' => $this->analyticsModel->getOverallStats($userId),
            'qr_codes' => $this->analyticsModel->getUserQrCodeStats($userId),
            'scan_trends' => $this->analyticsModel->getScanTrends($userId),
            'location_stats' => $this->analyticsModel->getLocationStats($userId),
            'export_date' => date('Y-m-d H:i:s')
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="qr_analytics_' . date('Y-m-d') . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function track()
    {
        // This endpoint will be called when QR codes are scanned
        $qrCodeId = $_GET['id'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $ipAddress = $this->getClientIp();
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;

        if ($qrCodeId) {
            // Use recordScanEvent to save to qr_code_scans table (consistent with analytics dashboard)
            $this->analyticsModel->recordScanEvent($qrCodeId, 'scan', $userAgent, $ipAddress, null, $referrer);
        }

        // Redirect to the actual QR code destination
        $qrCode = $this->barcodeQrModel->getQrCodeById($qrCodeId);
        if ($qrCode && !empty($qrCode->value)) {
            // For QR codes, redirect to the value (URL)
            // For barcodes, redirect to a barcode info page
            if ($qrCode->type === 'barcode') {
                redirect(URLROOT . "/analytics/barcodeInfo?id=" . $qrCodeId);
            } else {
                redirect($qrCode->value);
            }
        } else {
            redirect(URLROOT);
        }
    }

    // Manual barcode scan tracking (for when users manually record barcode scans)
    public function recordBarcodeScan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $barcodeId = $_POST['barcode_id'] ?? null;
            $scanLocation = $_POST['scan_location'] ?? 'Unknown';
            $scanNotes = $_POST['scan_notes'] ?? '';
            
            if ($barcodeId) {
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                $ipAddress = $this->getClientIp();
                $referrer = "Manual scan - Location: " . $scanLocation . " Notes: " . $scanNotes;
                
                $this->analyticsModel->recordScan($barcodeId, $userAgent, $ipAddress, $referrer);
                
                // Return success response
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Barcode scan recorded successfully']);
                exit;
            }
        }
        
        // Return error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to record barcode scan']);
        exit;
    }

    // Show barcode information page
    public function barcodeInfo()
    {
        $barcodeId = $_GET['id'] ?? null;
        
        if (!$barcodeId) {
            redirect(URLROOT);
        }
        
        $barcode = $this->barcodeQrModel->getQrCodeById($barcodeId);
        
        if (!$barcode || $barcode->type !== 'barcode') {
            redirect(URLROOT);
        }
        
        // Get scan analytics for this barcode
        $analyticsModel = $this->model('AnalyticsModel');
        $scanStats = $analyticsModel->getCodeScanStats($barcodeId);
        $recentScans = $analyticsModel->getRecentScansForCode($barcodeId);
        
        $data = [
            'barcode' => $barcode,
            'scan_stats' => $scanStats,
            'recent_scans' => $recentScans
        ];
        
        // Load a simple barcode info view
        $this->view('analytics/barcodeInfo', $data);
    }

    // Generate QR code for barcode tracking
    public function generateBarcodeQr()
    {
        $barcodeId = $_GET['id'] ?? null;
        
        if (!$barcodeId) {
            http_response_code(404);
            exit;
        }
        
        $barcode = $this->barcodeQrModel->getQrCodeById($barcodeId);
        
        if (!$barcode || $barcode->type !== 'barcode') {
            http_response_code(404);
            exit;
        }
        
        // Generate QR code with tracking URL
        $trackingUrl = URLROOT . "/analytics/track?id=" . $barcodeId;
        
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
            
            // Set headers and output QR code
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
            echo $result->getString();
            exit;
            
        } catch (Exception $e) {
            // Return default QR code if generation fails
            header('Content-Type: image/png');
            echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
            exit;
        }
    }

    private function getClientIp()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
