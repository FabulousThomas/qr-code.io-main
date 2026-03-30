<?php

require_once APPROOT . '/models/AnalyticsModel.php';

class AdvancedAnalytics extends Controller
{
    private $analyticsModel;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        
        $this->analyticsModel = $this->model('AnalyticsModel');
    }

    /**
     * Display advanced analytics dashboard
     */
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $dateRange = isset($_GET['range']) ? (int)$_GET['range'] : 30;
        
        // Validate date range
        if ($dateRange < 1 || $dateRange > 365) {
            $dateRange = 30;
        }
        
        $analyticsData = $this->analyticsModel->getDashboardData($userId, $dateRange);
        
        $data = [
            'analytics' => $analyticsData,
            'dateRange' => $dateRange,
            'title' => 'Advanced Analytics'
        ];
        
        $this->view('analytics/advanced_dashboard', $data);
    }

    /**
     * Get analytics data via AJAX
     */
    public function getAnalyticsData()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $dateRange = isset($_GET['range']) ? (int)$_GET['range'] : 30;
        $type = isset($_GET['type']) ? $_GET['type'] : 'overview';
        
        try {
            switch ($type) {
                case 'overview':
                    echo json_encode($this->analyticsModel->getOverviewStats($userId, $dateRange));
                    break;
                    
                case 'top_codes':
                    echo json_encode($this->analyticsModel->getTopPerformingCodes($userId, 10, $dateRange));
                    break;
                    
                case 'geo':
                    echo json_encode($this->analyticsModel->getGeographicData($userId, $dateRange));
                    break;
                    
                case 'devices':
                    echo json_encode($this->analyticsModel->getDeviceAnalytics($userId, $dateRange));
                    break;
                    
                case 'time':
                    echo json_encode($this->analyticsModel->getTimeBasedAnalytics($userId, $dateRange));
                    break;
                    
                case 'conversions':
                    echo json_encode($this->analyticsModel->getConversionAnalytics($userId, $dateRange));
                    break;
                    
                default:
                    echo json_encode(['error' => 'Invalid type']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Export analytics data
     */
    public function export()
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        
        $userId = $_SESSION['user_id'];
        $dateRange = isset($_GET['range']) ? (int)$_GET['range'] : 30;
        $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
        
        $analyticsData = $this->analyticsModel->getDashboardData($userId, $dateRange);
        
        if ($format === 'csv') {
            $this->exportCSV($analyticsData);
        } elseif ($format === 'json') {
            $this->exportJSON($analyticsData);
        } else {
            // Default to PDF
            $this->exportPDF($analyticsData);
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV($data)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="analytics_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Overview section
        fputcsv($output, ['Overview Statistics']);
        fputcsv($output, ['Metric', 'Value']);
        
        foreach ($data['overview'] as $key => $value) {
            fputcsv($output, [ucwords(str_replace('_', ' ', $key)), $value]);
        }
        
        fputcsv($output, []);
        
        // Top performing codes
        fputcsv($output, ['Top Performing QR Codes']);
        fputcsv($output, ['Name', 'Type', 'Content', 'Scans', 'Last Scanned', 'Scan Rate']);
        
        foreach ($data['topCodes'] as $code) {
            fputcsv($output, [
                $code['name'],
                $code['type'],
                substr($code['content'], 0, 50) . '...',
                $code['scan_count'],
                $code['last_scanned'],
                round($code['scan_rate'], 2)
            ]);
        }
        
        fputcsv($output, []);
        
        // Geographic data
        fputcsv($output, ['Geographic Distribution']);
        fputcsv($output, ['Country', 'City', 'Scans', 'Unique Visitors']);
        
        foreach ($data['geoData'] as $geo) {
            fputcsv($output, [
                $geo['country'],
                $geo['city'],
                $geo['scans'],
                $geo['unique_visitors']
            ]);
        }
        
        fclose($output);
    }

    /**
     * Export data as JSON
     */
    private function exportJSON($data)
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="analytics_export_' . date('Y-m-d') . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Export data as PDF
     */
    private function exportPDF($data)
    {
        // This would require a PDF library like TCPDF or DomPDF
        // For now, redirect to CSV export
        $this->exportCSV($data);
    }

    /**
     * Get real-time scan data
     */
    public function getRealtimeData()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get scans in the last hour
        $sql = "SELECT 
                    COUNT(*) as recent_scans,
                    COUNT(DISTINCT qa.ip_address) as unique_visitors
                FROM qr_analytics qa
                JOIN codes c ON qa.qr_code_id = c.id
                WHERE c.user_id = ? 
                AND qa.last_scanned >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $result = $this->analyticsModel->db->prepare($sql)->execute([$userId])->fetch();
        
        echo json_encode([
            'recentScans' => (int)$result['recent_scans'],
            'uniqueVisitors' => (int)$result['unique_visitors'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Compare analytics between date ranges
     */
    public function compare()
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $currentRange = isset($_GET['current_range']) ? (int)$_GET['current_range'] : 30;
        $previousRange = isset($_GET['previous_range']) ? (int)$_GET['previous_range'] : 30;
        
        $currentData = $this->analyticsModel->getOverviewStats($userId, $currentRange);
        $previousData = $this->analyticsModel->getOverviewStats($userId, $previousRange);
        
        $comparison = [];
        
        foreach ($currentData as $key => $currentValue) {
            $previousValue = $previousData[$key] ?? 0;
            $change = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;
            
            $comparison[$key] = [
                'current' => $currentValue,
                'previous' => $previousValue,
                'change' => round($change, 2),
                'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
            ];
        }
        
        echo json_encode($comparison);
    }
}
