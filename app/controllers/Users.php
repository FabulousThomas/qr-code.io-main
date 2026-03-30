<?php
class Users extends Controller
{
   private $data = [];
   private $userModel = '';
   private $formData = [];
   public function __construct()
   {
      $this->userModel = $this->model('User');
   }

   public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        
        // Load analytics model if available
        $analyticsData = null;
        if (file_exists(APPROOT . '/models/AnalyticsModel.php') && isset($_SESSION['user_id'])) {
            $analyticsModel = $this->model('AnalyticsModel');
            $userId = $_SESSION['user_id'] ?? null;
            
            // Get analytics data with null checks
            $overallStats = $analyticsModel->getOverallStats($userId) ?? (object)[
                'total_qr_codes' => 0,
                'total_scans' => 0,
                'avg_scans_per_qr' => 0,
                'max_scans' => 0,
                'unique_countries' => 0
            ];
            
            $recentScans = $analyticsModel->getRecentScans($userId);
            $topQrCodes = $analyticsModel->getTopPerformingQrCodes($userId);
            
            $analyticsData = [
                'overall_stats' => $overallStats,
                'recent_scans' => $recentScans,
                'top_qr_codes' => $topQrCodes
            ];
        }
        
        // Get user's QR codes (only if user is logged in)
        $codes = [];
        $codesWithImages = [];
        if (isset($_SESSION['user_id'])) {
            $barcodeQrModel = $this->model('BarcodeQrModel');
            $codes = $barcodeQrModel->getByUser($_SESSION['user_id'] ?? null);
            
            if (!empty($codes)) {
                foreach ($codes as $code) {
                    $imageDataUri = $barcodeQrModel->getImageDataUri($code);
                    if (empty($imageDataUri)) {
                        continue;
                    }
                    $codesWithImages[] = [
                        'id' => $code->id,
                        'value' => $code->value,
                    'format' => $code->format,
                    'image' => $imageDataUri,
                    'created_at' => $code->created_at
                ];
            }
        }
        }
        
        $this->data = [
            'analytics' => $analyticsData,
            'recent_codes' => array_slice($codesWithImages, 0, 5), // Show 5 most recent
            'total_codes' => count($codesWithImages),
            'page_title' => 'User Dashboard'
        ];
        
        $this->view('users/index', $this->data);
    }

   // REGISTER USERS
   public function register()
   {
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
         // Sanitize input
         $this->formData = filteration($_POST);
         // Initialize this->data array
         $this->data = [
            'title' => 'Create an Account',
            'description' => 'Please, Register to Login',
            'name' => $this->formData['name'] ?? '',
            'username' => $this->formData['username'] ?? '',
            'email' => $this->formData['email'] ?? '',
            'password' => $this->formData['password'] ?? '',
            'name_err' => '',
            'username_err' => '',
            'email_err' => '',
            'password_err' => '',
         ];

         // Validate Inputs
         if (empty($this->data['name'])) {
            $this->data['name_err'] = 'Please, enter your full name';
         }
         if (empty($this->data['username'])) {
            $this->data['username_err'] = 'Please, enter username';
         } elseif ($this->data['username'] == $this->userModel->checkUsername($this->data['username'])) {
            $this->data['username_err'] = 'This username already exist';
         }
         if (empty($this->data['email'])) {
            $this->data['email_err'] = 'Please, enter email';
         } elseif ($this->data['email'] == $this->userModel->checkEmail($this->data['email'])) {
            $this->data['email_err'] = 'This email already exist';
         }
         // if (empty($this->data['password'])) {
         //    $this->data['password_err'] = 'Please, enter password';
         // } elseif (strlen($this->data['password']) < 6) {
         //    $this->data['password_err'] = 'password must be at least 6 characters';
         // }

         // Make sure errors are empty
         if (empty($this->data['name_err']) && empty($this->data['username_err']) && empty($this->data['email_err'])) {

            // Hash password
            // $this->data['password'] = password_hash($this->data['password'], PASSWORD_DEFAULT);

            if ($this->userModel->register($this->data)) {
               flashMsg('users_msg', '<strong>Success!</strong> You can login');
               redirect('users/login');
            } else {
               die('Something went wrong');
            }
         } else {
            // Load views with error
            $this->view('users/register', $this->data);
         }
      } else {
         $this->data = [
            'title' => 'Create an Account',
            'description' => 'Please, Register to Login',
            'name_err' => '',
            'username_err' => '',
            'email_err' => '',
            'password_err' => '',
         ];
         $this->view('users/register', $this->data);
      }
   }

   // LOGIN USERS
   public function login()
   {

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {

         // Sanitize inputs
         $this->formData = filteration($_POST);
         // Initialize data array
         $this->data = [
            'title' => 'Login',
            'description' => 'Please, Login to proceed',
            'email' => $this->formData['email'],
            'email_err' => '',
            'password_err' => '',
         ];

         // Validate login inputs
         // Validate email
         if ($this->userModel->checkEmail($this->data['email'])) {
            // Success
         } elseif (empty($this->data['email'])) {
            $this->data['email_err'] = 'Please, enter email';
         } else {
            // Failed
            $this->data['email_err'] = 'No user for this account';
         }

         // Email-only login: if email is valid and exists, log the user in without password

         // Make sure errors are empty
         if (empty($this->data['email_err'])) {
            $loginUsers = $this->userModel->getUserByEmail($this->data['email']);

            if ($loginUsers) {
               $this->createLoggedinSession($loginUsers);
            } else {
               $this->data['email_err'] = 'No user for this email';
               $this->view('users/login', $this->data);
            }
         } else {
            // Load views with error
            $this->view('users/login', $this->data);
         }
      } else {
         $this->data = [
            'title' => 'Login',
            'description' => 'Please, Login to proceed',
            'email' => '',
            'email_err' => '',
            'password_err' => '',
         ];
         $this->view('users/login', $this->data);
      }
   }

   // CHECK LOGGED IN USER
   public function createLoggedinSession($users)
   {
      if (is_object($users)) {
         if (isset($users->id)) {
            $_SESSION['user_id'] = $users->id;
         }
         if (isset($users->email)) {
            $_SESSION['email'] = $users->email;
         }
         if (isset($users->username)) {
            $_SESSION['username'] = $users->username;
         }
         if (isset($users->name)) {
            $_SESSION['name'] = $users->name;
         }
      }

      // redirect URL on SUCCESS ->>>  
      redirect('users/index');
   }

   // USER LOGOUT SESSION
   public function logout()
   {
      unset($_SESSION['user_id']);
      unset($_SESSION['email']);
      unset($_SESSION['name']);
      session_destroy();
      redirect('users/login');
   }

   // Download individual QR code
   public function downloadCode($codeId)
   {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $barcodeQrModel = $this->model('BarcodeQrModel');
        $code = $barcodeQrModel->getById($codeId);

        // Verify user owns this code
        if (!$code || $code->user_id != ($_SESSION['user_id'] ?? null)) {
            redirect('users/index');
        }

        // Get image data
        $imageDataUri = $barcodeQrModel->getImageDataUri($code);
        if (empty($imageDataUri)) {
            redirect('users/index');
        }

        // Convert data URI to image
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageDataUri));
        
        // Set headers for download
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="qrcode-' . $code->id . '.png"');
        header('Content-Length: ' . strlen($imageData));
        
        echo $imageData;
        exit;
   }

   // Analytics dashboard
   public function analytics()
   {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $userId = $_SESSION['user_id'] ?? null;
        
        // Double-check user ID is valid
        if (!$userId) {
            redirect('users/login');
        }
        
        // Load analytics model
        $analyticsModel = $this->model('AnalyticsModel');
        
        // Get analytics data with null checks
        $overallStats = $analyticsModel->getOverallStats($userId) ?? (object)[
            'total_qr_codes' => 0,
            'total_scans' => 0,
            'avg_scans_per_qr' => 0,
            'max_scans' => 0,
            'unique_countries' => 0
        ];
        
        $recentScans = $analyticsModel->getRecentScans($userId);
        $topQrCodes = $analyticsModel->getTopPerformingQrCodes($userId);
        $scanTrends = $analyticsModel->getScanTrends($userId);
        $locationStats = $analyticsModel->getLocationStats($userId);
        
        $data = [
            'overall_stats' => $overallStats,
            'recent_scans' => $recentScans,
            'top_qr_codes' => $topQrCodes,
            'scan_trends' => $scanTrends,
            'location_stats' => $locationStats,
            'page_title' => 'Analytics Dashboard'
        ];

        $this->view('users/analytics', $data);
   }

   // My Codes page
   public function myCodes()
   {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $userId = $_SESSION['user_id'] ?? null;
        
        // Double-check user ID is valid
        if (!$userId) {
            redirect('users/login');
        }
        
        // Get user's QR codes
        $barcodeQrModel = $this->model('BarcodeQrModel');
        $codes = $barcodeQrModel->getByUser($userId);
        $codesWithImages = [];
        
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $imageDataUri = $barcodeQrModel->getImageDataUri($code);
                if (empty($imageDataUri)) {
                    continue;
                }
                $codesWithImages[] = [
                    'id' => $code->id,
                    'value' => $code->value,
                    'type' => $code->type ?? 'qrcode',
                    'format' => $code->format,
                    'image_data_uri' => $imageDataUri,
                    'created_at' => $code->created_at
                ];
            }
        }
        
        $data = [
            'codes' => $codesWithImages,
            'page_title' => 'My Codes'
        ];

        $this->view('users/myCodes', $data);
   }

   // QR code details
   public function qrDetails($qrCodeId)
   {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $userId = $_SESSION['user_id'] ?? null;
        
        // Double-check user ID is valid
        if (!$userId) {
            redirect('users/login');
        }
        
        // Verify user owns this QR code
        $barcodeQrModel = $this->model('BarcodeQrModel');
        $qrCode = $barcodeQrModel->getQrCodeById($qrCodeId);
        if (!$qrCode || $qrCode->user_id != $userId) {
            redirect('users/analytics');
        }

        // Load analytics model
        $analyticsModel = $this->model('AnalyticsModel');
        
        // Get detailed analytics for this QR code
        $stats = $analyticsModel->getQrCodeStats($qrCodeId) ?? (object)[
            'total_scans' => 0,
            'unique_scans' => 0,
            'unique_countries' => 0
        ];
        
        $recentScans = $analyticsModel->getQrCodeRecentScans($qrCodeId);
        $scanTimeline = $analyticsModel->getQrCodeScanTimeline($qrCodeId);
        $deviceStats = $analyticsModel->getQrCodeDeviceStats($qrCodeId);
        $countryStats = $analyticsModel->getQrCodeCountryStats($qrCodeId);
        
        $data = [
            'qr_code' => $qrCode,
            'stats' => $stats,
            'recent_scans' => $recentScans,
            'scan_timeline' => $scanTimeline,
            'device_stats' => $deviceStats,
            'country_stats' => $countryStats,
            'page_title' => 'QR Code Details'
        ];

        $this->view('users/qr-details', $data);
   }

   // Export analytics data
   public function exportAnalytics($format = 'csv')
   {
        if (!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }

        $userId = $_SESSION['user_id'] ?? null;
        
        // Double-check user ID is valid
        if (!$userId) {
            redirect('users/login');
        }
        
        $analyticsModel = $this->model('AnalyticsModel');
        
        // Get export data
        $exportData = $analyticsModel->getExportData($userId);
        
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="analytics-' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($output, ['QR Code Value', 'Total Scans', 'Unique Scans', 'Created At']);
            
            // CSV data
            foreach ($exportData as $row) {
                fputcsv($output, [
                    $row->qr_value,
                    $row->total_scans,
                    $row->unique_scans,
                    $row->created_at
                ]);
            }
            
            fclose($output);
        } elseif ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="analytics-' . date('Y-m-d') . '.json"');
            
            echo json_encode($exportData, JSON_PRETTY_PRINT);
        }
        
        exit;
   }
}
