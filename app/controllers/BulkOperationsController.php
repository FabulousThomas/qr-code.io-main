<?php

class BulkOperationsController
{
    private $db;
    private $bulkOpsModel;
    private $maxConcurrentOperations = 3;
    private $retryDelay = 300; // 5 minutes in seconds

    public function __construct()
    {
        $this->db = new Database();
        $this->bulkOpsModel = new BulkOperationsModel();
    }

    /**
     * Process queued bulk operations with retry logic
     */
    public function processQueue()
    {
        // Get pending operations that are due
        $query = "SELECT * FROM bulk_queue 
                  WHERE status = 'pending' AND scheduled_at <= NOW() 
                  ORDER BY priority DESC, created_at ASC 
                  LIMIT " . (int)$this->maxConcurrentOperations;
        
        $operations = $this->db->query($query);
        
        foreach ($operations as $operation) {
            $this->processQueuedOperation($operation);
        }
    }

    /**
     * Process a single queued operation with comprehensive error handling
     */
    private function processQueuedOperation($operation)
    {
        try {
            // Mark as processing
            $this->updateQueueStatus($operation->id, 'processing', null, date('Y-m-d H:i:s'));
            
            $userId = $operation->user_id;
            $type = $operation->operation_type;
            $data = json_decode($operation->operation_data, true);
            
            // Validate operation data
            $validation = $this->bulkOpsModel->validateOperationData($data, $type);
            if (!$validation['valid']) {
                throw new Exception('Validation failed: ' . implode(', ', $validation['errors']));
            }
            
            // Process the operation
            $result = $this->bulkOpsModel->processBulkOperation($userId, $type, $data);
            
            if ($result['success']) {
                $this->updateQueueStatus($operation->id, 'completed', null, date('Y-m-d H:i:s'));
                
                // Log successful operation
                error_log("Bulk operation completed successfully: ID {$operation->id}, Type: {$type}");
            } else {
                // Retry logic with exponential backoff
                if ($operation->retry_count < $operation->max_retries) {
                    $this->retryOperation($operation->id, $result['errors']);
                } else {
                    $errorMsg = 'Operation failed after ' . $operation->max_retries . ' retries. Errors: ' . implode(', ', $result['errors']);
                    $this->updateQueueStatus($operation->id, 'failed', $errorMsg, date('Y-m-d H:i:s'));
                    
                    // Log final failure
                    error_log("Bulk operation failed permanently: ID {$operation->id}, Type: {$type}, Errors: {$errorMsg}");
                }
            }
            
        } catch (Exception $e) {
            $errorMsg = 'System error: ' . $e->getMessage();
            $this->updateQueueStatus($operation->id, 'failed', $errorMsg, date('Y-m-d H:i:s'));
            
            // Log system error
            error_log("System error in bulk operation: ID {$operation->id}, Error: {$errorMsg}");
        }
    }

    /**
     * Schedule a bulk operation
     */
    public function scheduleOperation($userId, $type, $data, $scheduledAt, $priority = 0)
    {
        try {
            // Validate input
            if (empty($userId) || empty($type) || empty($data)) {
                throw new Exception('Missing required parameters');
            }
            
            // Validate operation data
            $validation = $this->bulkOpsModel->validateOperationData($data, $type);
            if (!$validation['valid']) {
                throw new Exception('Invalid operation data: ' . implode(', ', $validation['errors']));
            }
            
            // Insert into queue
            $query = "INSERT INTO bulk_queue 
                      (user_id, operation_type, operation_data, priority, scheduled_at) 
                      VALUES (" . (int)$userId . ", '" . addslashes($type) . "', '" . addslashes(json_encode($data)) . "', " . (int)$priority . ", '" . addslashes($scheduledAt) . "')";
            
            $result = $this->db->query($query);
            
            if ($result) {
                error_log("Bulk operation scheduled: User {$userId}, Type: {$type}, Scheduled: {$scheduledAt}");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Failed to schedule bulk operation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create operation template
     */
    public function createTemplate($userId, $name, $description, $templateData, $isPublic = false)
    {
        try {
            // Validate template data
            if (empty($name) || empty($templateData)) {
                throw new Exception('Template name and data are required');
            }
            
            // Check if template already exists
            $existingQuery = "SELECT id FROM bulk_templates WHERE user_id = " . (int)$userId . " AND name = '" . addslashes($name) . "'";
            $existing = $this->db->query($existingQuery);
            
            if (!empty($existing)) {
                // Update existing template
                $query = "UPDATE bulk_templates 
                          SET description = '" . addslashes($description) . "', 
                              template_data = '" . addslashes(json_encode($templateData)) . "', 
                              is_public = " . ($isPublic ? 1 : 0) . ",
                              usage_count = usage_count + 1,
                              updated_at = CURRENT_TIMESTAMP
                          WHERE user_id = " . (int)$userId . " AND name = '" . addslashes($name) . "'";
            } else {
                // Create new template
                $query = "INSERT INTO bulk_templates 
                          (user_id, name, description, template_data, is_public) 
                          VALUES (" . (int)$userId . ", '" . addslashes($name) . "', '" . addslashes($description) . "', '" . addslashes(json_encode($templateData)) . "', " . ($isPublic ? 1 : 0) . ")";
            }
            
            $result = $this->db->query($query);
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Failed to create template: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user templates with error handling
     */
    public function getUserTemplates($userId, $includePublic = false)
    {
        try {
            $sql = "SELECT * FROM bulk_templates WHERE user_id = " . (int)$userId;
            
            if ($includePublic) {
                $sql .= " OR is_public = TRUE";
            }
            
            $sql .= " ORDER BY usage_count DESC, created_at DESC";
            
            return $this->db->query($sql);
            
        } catch (Exception $e) {
            error_log("Failed to get templates: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get queued operations for user
     */
    public function getQueuedOperations($userId)
    {
        try {
            $query = "SELECT * FROM bulk_queue 
                      WHERE user_id = " . (int)$userId . " 
                      ORDER BY scheduled_at ASC";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Failed to get queued operations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cancel queued operation
     */
    public function cancelQueuedOperation($queueId, $userId)
    {
        try {
            $query = "UPDATE bulk_queue 
                      SET status = 'cancelled', completed_at = NOW() 
                      WHERE id = " . (int)$queueId . " AND user_id = " . (int)$userId . " AND status = 'pending'";
            
            $result = $this->db->query($query);
            
            if ($result) {
                error_log("Operation cancelled: ID {$queueId} by User {$userId}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Failed to cancel operation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update queue status with error handling
     */
    private function updateQueueStatus($queueId, $status, $errorMessage = null, $completedAt = null)
    {
        try {
            $query = "UPDATE bulk_queue 
                      SET status = '" . addslashes($status) . "', error_message = '" . addslashes($errorMessage) . "', completed_at = " . ($completedAt ? "'" . addslashes($completedAt) . "'" : "NULL") . " 
                      WHERE id = " . (int)$queueId;
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Failed to update queue status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retry failed operation with exponential backoff
     */
    private function retryOperation($queueId, $errors = [])
    {
        try {
            // Calculate retry delay with exponential backoff
            $query = "SELECT retry_count FROM bulk_queue WHERE id = " . (int)$queueId;
            $result = $this->db->query($query);
            
            if (empty($result)) {
                return false;
            }
            
            $retryCount = $result[0]->retry_count;
            $delay = min($this->retryDelay * pow(2, $retryCount), 3600); // Max 1 hour delay
            $scheduledAt = date('Y-m-d H:i:s', time() + $delay);
            
            $query = "UPDATE bulk_queue 
                      SET status = 'pending', 
                          retry_count = retry_count + 1,
                          scheduled_at = '" . addslashes($scheduledAt) . "',
                          error_message = '" . addslashes('Retry #' . ($retryCount + 1) . ' scheduled. Errors: ' . implode(', ', $errors)) . "'
                      WHERE id = " . (int)$queueId;
            
            $result = $this->db->query($query);
            
            if ($result) {
                error_log("Operation retry scheduled: ID {$queueId}, Retry #" . ($retryCount + 1) . ", Delay: {$delay}s");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Failed to schedule retry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get operation statistics with error handling
     */
    public function getOperationStats($userId = null)
    {
        try {
            $whereClause = $userId ? "WHERE user_id = " . (int)$userId : "";
            
            $query = "SELECT 
                        type,
                        status,
                        COUNT(*) as count,
                        AVG(processing_time) as avg_time,
                        SUM(count) as total_items
                      FROM bulk_operations 
                      {$whereClause}
                      GROUP BY type, status
                      ORDER BY type, status";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Failed to get operation stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export operation history with error handling
     */
    public function exportHistory($userId, $format = 'csv')
    {
        try {
            $query = "SELECT * FROM bulk_operations 
                      WHERE user_id = " . (int)$userId . " 
                      ORDER BY created_at DESC";
            
            $operations = $this->db->query($query);
            
            if (empty($operations)) {
                throw new Exception('No operations found to export');
            }
            
            if ($format === 'csv') {
                $this->exportToCsv($operations);
            } elseif ($format === 'json') {
                $this->exportToJson($operations);
            } else {
                throw new Exception('Unsupported export format');
            }
            
        } catch (Exception $e) {
            error_log("Failed to export history: " . $e->getMessage());
            header('HTTP/1.0 500 Internal Server Error');
            echo 'Export failed: ' . $e->getMessage();
        }
    }

    /**
     * Export to CSV format with error handling
     */
    private function exportToCsv($operations)
    {
        try {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="bulk_operations_history.csv"');
            
            $output = fopen('php://output', 'w');
            
            if ($output === false) {
                throw new Exception('Failed to open output stream');
            }
            
            // Header
            fputcsv($output, [
                'ID', 'Type', 'Count', 'Status', 'Processing Time', 
                'Created At', 'Error Details', 'File Path'
            ]);
            
            // Data
            foreach ($operations as $op) {
                $errorDetails = $op->error_details ? json_decode($op->error_details, true) : [];
                fputcsv($output, [
                    $op->id,
                    $op->type,
                    $op->count,
                    $op->status,
                    $op->processing_time,
                    $op->created_at,
                    json_encode($errorDetails),
                    $op->file_path
                ]);
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            error_log("Failed to export to CSV: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export to JSON format with error handling
     */
    private function exportToJson($operations)
    {
        try {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="bulk_operations_history.json"');
            
            echo json_encode($operations, JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            error_log("Failed to export to JSON: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get system-wide queue status with error handling
     */
    public function getQueueStatus()
    {
        try {
            $query = "SELECT 
                        status,
                        COUNT(*) as count
                      FROM bulk_queue
                      GROUP BY status";
            
            $results = $this->db->query($query);
            
            $status = [
                'pending' => 0,
                'processing' => 0,
                'completed' => 0,
                'failed' => 0,
                'cancelled' => 0
            ];
            
            foreach ($results as $row) {
                $status[$row->status] = (int) $row->count;
            }
            
            return (object) $status;
            
        } catch (Exception $e) {
            error_log("Failed to get queue status: " . $e->getMessage());
            return (object) [
                'pending' => 0,
                'processing' => 0,
                'completed' => 0,
                'failed' => 0,
                'cancelled' => 0
            ];
        }
    }

    /**
     * Cleanup old operations with error handling
     */
    public function cleanupOldOperations($daysOld = 30)
    {
        try {
            $query = "DELETE FROM bulk_operations 
                      WHERE created_at < DATE_SUB(NOW(), INTERVAL " . (int)$daysOld . " DAY)";
            
            $result = $this->db->query($query);
            
            error_log("Cleaned up operations older than {$daysOld} days");
            return $result;
            
        } catch (Exception $e) {
            error_log("Failed to cleanup old operations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get performance metrics with error handling
     */
    public function getPerformanceMetrics($userId = null, $days = 7)
    {
        try {
            $whereClause = $userId ? "WHERE user_id = " . (int)$userId : "";
            
            $query = "SELECT 
                        DATE(created_at) as date,
                        type,
                        COUNT(*) as operations,
                        SUM(count) as total_items,
                        AVG(processing_time) as avg_time,
                        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                      FROM bulk_operations 
                      {$whereClause}
                      AND created_at >= DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
                      GROUP BY DATE(created_at), type
                      ORDER BY date DESC, type";
            
            return $this->db->query($query);
            
        } catch (Exception $e) {
            error_log("Failed to get performance metrics: " . $e->getMessage());
            return [];
        }
    }
}
