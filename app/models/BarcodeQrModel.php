<?php
/*
 * Barcode and QR Code Model
 * Handles saving and retrieving barcodes and QR codes from database
 */
class BarcodeQrModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    // Save barcode or QR code to database
    public function save($data)
    {
        $type = $data['type'] ?? 'barcode'; // 'barcode' or 'qrcode'
        $value = $data['value'] ?? '';
        $format = $data['format'] ?? '';
        $imageData = $data['imageData'] ?? '';
        $imagePath = $data['imagePath'] ?? '';
        $customization = isset($data['customization']) && !empty($data['customization']) ? json_encode($data['customization']) : null;
        $userId = $data['user_id'] ?? null;
        $email = $data['email'] ?? null;
        $sessionId = session_id();

        $dataUriForStorage = $this->prepareDataUriForStorage($imageData, null, $format);

        if (!empty($imageData) && empty($imagePath)) {
            if (strpos($imageData, 'data:image') === 0) {
                $imagePath = $this->saveImageToFile($imageData, $type, $value);
            } else {
                $imagePath = $imageData;
            }
        }

        if ($dataUriForStorage === null && !empty($imagePath)) {
            $dataUriForStorage = $this->prepareDataUriForStorage(null, $imagePath, $format);
        }

        $this->db->query('INSERT INTO codes (type, value, format, image_data, image_path, customization_options, user_id, email, session_id, created_at) 
                         VALUES (:type, :value, :format, :image_data, :image_path, :customization, :user_id, :email, :session_id, NOW())');

        $this->db->bind(':type', $type);
        $this->db->bind(':value', $value);
        $this->db->bind(':format', $format);
        $this->db->bind(':image_data', $dataUriForStorage);
        $this->db->bind(':image_path', $imagePath);
        $this->db->bind(':customization', $customization);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':email', $email);
        $this->db->bind(':session_id', $sessionId);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Save multiple barcodes
    public function saveMultiple($items)
    {
        $savedIds = [];
        foreach ($items as $item) {
            $id = $this->save($item);
            if ($id) {
                $savedIds[] = $id;
            }
        }
        return $savedIds;
    }

    private function saveImageToFile($imageData, $type, $value)
    {
        if (empty($imageData)) {
            return null;
        }

        $extension = 'png';
        if (preg_match('/^data:image\/([\w+\-.]+);base64,/', $imageData, $matches)) {
            $extension = strtolower($matches[1] ?? 'png');
        }

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if ($extension === 'svg+xml') {
            $extension = 'svg';
        }

        $parts = explode(',', $imageData, 2);
        $imageDataClean = $parts[1] ?? '';
        $imageDataClean = str_replace(["\r", "\n"], '', $imageDataClean);
        $imageDataClean = str_replace(' ', '+', $imageDataClean);
        $decoded = base64_decode($imageDataClean, true);

        if ($decoded === false) {
            return null;
        }

        $storage = $this->getStorageConfig($type);
        $directory = $storage['directory'];
        $publicPrefix = $storage['public_prefix'];

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $type . '_' . microtime(true) . '_' . md5($value . microtime(true)) . '.' . $extension;
        $filename = str_replace([' ', ':'], '_', $filename);
        $filePath = rtrim($directory, '\/');
        $filePath .= DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($filePath, $decoded) !== false) {
            $relativePath = $publicPrefix . $filename;
            $relativePath = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

            if (defined('URLROOT')) {
                return rtrim(URLROOT, '/') . $relativePath;
            }

            return $relativePath;
        }

        return null;
    }

    private function getStorageConfig($type)
    {
        $type = strtolower($type);

        $basePublicPath = APPROOT . '/../public/images/';

        switch ($type) {
            case 'barcode':
                return [
                    'directory' => $basePublicPath . 'barimage/',
                    'public_prefix' => 'images/barimage/'
                ];
            case 'qrcode':
                return [
                    'directory' => $basePublicPath . 'qrimage/',
                    'public_prefix' => 'images/qrimage/'
                ];
            default:
                return [
                    'directory' => $basePublicPath . 'codes/',
                    'public_prefix' => 'images/codes/'
                ];
        }
    }

    // Get codes by session
    public function getBySession($sessionId)
    {
        $this->db->query('SELECT * FROM codes WHERE session_id = :session_id ORDER BY created_at DESC');
        $this->db->bind(':session_id', $sessionId);
        return $this->db->resultSet();
    }

    // Get codes by user
    public function getByUser($userId)
    {
        $this->db->query('SELECT * FROM codes WHERE user_id = :user_id ORDER BY created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function attachSessionCodesToUser($sessionId, $userId)
    {
        if (empty($sessionId) || empty($userId)) {
            return false;
        }

        $this->db->query('UPDATE codes SET user_id = :user_id WHERE session_id = :session_id AND (user_id IS NULL OR user_id = 0)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':session_id', $sessionId);

        return $this->db->execute();
    }

    public function attachCodesToUserAndEmailByIds(array $ids, $userId, $email)
    {
        if (empty($ids) || empty($email)) {
            return false;
        }

        // Build a dynamic IN (...) clause with named placeholders
        $placeholders = [];
        foreach ($ids as $index => $id) {
            $placeholders[] = ':id' . $index;
        }

        // Fixed: Always update user_id and email, don't use COALESCE
        $sql = 'UPDATE codes SET user_id = :user_id, email = :email WHERE id IN (' . implode(',', $placeholders) . ')';
        $this->db->query($sql);
        $this->db->bind(':user_id', (int) $userId);
        $this->db->bind(':email', $email);

        foreach ($ids as $index => $id) {
            $this->db->bind(':id' . $index, (int) $id);
        }

        return $this->db->execute();
    }

    public function attachSessionCodesToUserAndEmail($sessionId, $userId, $email)
    {
        if (empty($sessionId) || empty($email)) {
            return false;
        }

        // Fixed: Always update user_id and email, don't use COALESCE
        $this->db->query('UPDATE codes SET user_id = :user_id, email = :email WHERE session_id = :session_id');
        $this->db->bind(':user_id', (int) $userId);
        $this->db->bind(':email', $email);
        $this->db->bind(':session_id', $sessionId);

        return $this->db->execute();
    }

    // Get code by ID
    public function getById($id)
    {
        $this->db->query('SELECT * FROM codes WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->singleSet();
    }

    // Delete code
    public function delete($id)
    {
        $code = $this->getById($id);
        if ($code && $code->image_path) {
            $this->unlinkPhysicalFile($code->image_path);
        }

        $this->db->query('DELETE FROM codes WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getQrCodeById($id)
    {
        $this->db->query('SELECT * FROM codes WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->singleSet();
    }

    public function getByValueAndUser($value, $userId)
    {
        $this->db->query('SELECT * FROM codes WHERE value = :value AND user_id = :user_id');
        $this->db->bind(':value', $value);
        $this->db->bind(':user_id', $userId);
        return $this->db->singleSet();
    }

    public function getByValue($value)
    {
        $this->db->query('SELECT * FROM codes WHERE value = :value');
        $this->db->bind(':value', $value);
        return $this->db->singleSet();
    }

    public function updateImage($id, $imageData)
    {
        if (empty($imageData)) {
            return false;
        }

        $code = $this->getById($id);
        if (!$code) {
            return false;
        }

        $this->unlinkPhysicalFile($code->image_path ?? null);

        $dataUriForStorage = $this->prepareDataUriForStorage($imageData, null, $code->format ?? null);

        if (strpos($imageData, 'data:image') === 0) {
            $absolutePath = $this->saveImageToFile($imageData, $code->type ?? 'barcode', $code->value ?? $id);
            if (!$absolutePath) {
                return false;
            }

            $imagePath = $this->normalizeSavedPath($absolutePath);
        } else {
            $imagePath = $imageData;
        }

        if ($dataUriForStorage === null && !empty($imagePath)) {
            $dataUriForStorage = $this->prepareDataUriForStorage(null, $imagePath, $code->format ?? null);
        }

        if (empty($imagePath) && $dataUriForStorage === null) {
            return false;
        }

        $this->db->query('UPDATE codes SET image_path = :image_path, image_data = :image_data WHERE id = :id');
        $this->db->bind(':image_path', $imagePath);
        $this->db->bind(':image_data', $dataUriForStorage);
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return $imagePath;
        }

        return false;
    }

    private function normalizeSavedPath($path)
    {
        if (empty($path)) {
            return null;
        }

        if (defined('URLROOT') && strpos($path, URLROOT) === 0) {
            $path = substr($path, strlen(URLROOT));
            return '/' . ltrim($path, '/');
        }

        if (strpos($path, APPROOT) === 0) {
            $relative = substr($path, strlen(APPROOT . '/../public'));
            return '/' . ltrim(str_replace('\\', '/', $relative), '/');
        }

        return $path;
    }

    public function unlinkImage($id)
    {
        $code = $this->getById($id);
        if (!$code) {
            return false;
        }

        $this->unlinkPhysicalFile($code->image_path ?? null);

        $this->db->query('UPDATE codes SET image_path = NULL WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getImageDataUri($code)
    {
        if (!$code) {
            return null;
        }

        if (!empty($code->image_data)) {
            $normalized = $this->normalizeDataUri($code->image_data);
            if (!empty($normalized)) {
                return $normalized;
            }
        }

        if (empty($code->image_path)) {
            return null;
        }

        $filePath = $this->resolvePhysicalPath($code->image_path);
        if (!$filePath || !file_exists($filePath)) {
            return null;
        }

        $binary = file_get_contents($filePath);
        if ($binary === false) {
            return null;
        }

        $base64 = base64_encode($binary);
        if ($base64 === false) {
            return null;
        }

        $mime = $this->detectMimeFromPath($filePath, $code->format ?? null);

        return 'data:' . $mime . ';base64,' . $base64;
    }

    private function unlinkPhysicalFile($imagePath)
    {
        if (empty($imagePath)) {
            return;
        }

        $filePath = $this->resolvePhysicalPath($imagePath);

        if ($filePath && file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    private function resolvePhysicalPath($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        $filePath = $imagePath;

        if (strpos($filePath, URLROOT) === 0) {
            $relativePath = substr($filePath, strlen(URLROOT));
            $filePath = APPROOT . '/../public' . $relativePath;
        } elseif (strpos($filePath, APPROOT) !== 0) {
            $filePath = APPROOT . '/../public/' . ltrim($filePath, '/');
        }

        return $filePath;
    }

    private function detectMimeFromPath($filePath, $format)
    {
        if (!empty($format)) {
            $format = strtolower($format);
            if (in_array($format, ['png', 'jpeg', 'jpg', 'gif', 'bmp', 'svg', 'pdf'], true)) {
                return 'image/' . ($format === 'jpg' ? 'jpeg' : $format);
            }
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($extension, ['png', 'jpeg', 'jpg', 'gif', 'bmp', 'svg', 'pdf'], true)) {
            return 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
        }

        return 'image/png';
    }

    private function prepareDataUriForStorage($imageData, $imagePath, $format)
    {
        if (!empty($imageData) && strpos($imageData, 'data:image') === 0) {
            return $this->normalizeDataUri($imageData);
        }

        $binary = null;
        $mime = null;

        if (!empty($imagePath)) {
            $absolute = $this->resolvePhysicalPath($imagePath);
            if ($absolute && file_exists($absolute)) {
                $binary = file_get_contents($absolute);
                if ($binary === false) {
                    $binary = null;
                }
                $mime = $this->detectMimeFromPath($absolute, $format);
            }
        }

        if ($binary === null) {
            return null;
        }

        if ($mime === null) {
            $fallbackFormat = $format;
            if (empty($fallbackFormat) && !empty($imagePath)) {
                $fallbackFormat = pathinfo($imagePath, PATHINFO_EXTENSION);
            }

            if (!empty($fallbackFormat)) {
                $fallbackFormat = strtolower($fallbackFormat);
                if ($fallbackFormat === 'jpg') {
                    $fallbackFormat = 'jpeg';
                }
                $mime = 'image/' . $fallbackFormat;
            }
        }

        if ($mime === null) {
            $mime = 'image/png';
        }

        $base64 = base64_encode($binary);
        if ($base64 === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . $base64;
    }

    private function normalizeDataUri($dataUri)
    {
        if (empty($dataUri)) {
            return null;
        }

        $parts = explode(',', $dataUri, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $header = $parts[0];
        $body = $parts[1];

        $body = str_replace(["\r", "\n", ' '], '', $body);
        $decoded = base64_decode($body, true);
        if ($decoded === false) {
            return null;
        }

        $base64 = base64_encode($decoded);
        if ($base64 === false) {
            return null;
        }

        return $header . ',' . $base64;
    }
    
    // Update QR code value
    public function updateValue($id, $newValue)
    {
        $this->db->query('UPDATE codes SET value = :value WHERE id = :id');
        $this->db->bind(':value', $newValue);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
}
