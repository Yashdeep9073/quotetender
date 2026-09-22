<?php

    class AiSensyService
{
    private $db;
    private $baseUrl;
    private $apiKey;
    private $enabled;
    private $lastError = '';

    public function __construct($db)
    {
        $this->db = $db;
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $this->enabled = (getenv('AISENSY_ENABLED') === '1' || strtolower(getenv('AISENSY_ENABLED')) === 'true');
        $this->baseUrl = rtrim((string)getenv('AISENSY_API_URL'), '/');
        $this->apiKey = (string)getenv('AISENSY_API_KEY');
    }

    public function isEnabled()
    {
        return $this->enabled && !empty($this->baseUrl) && !empty($this->apiKey);
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Send an AiSensy notification for a task event.
     * Note: As per instructions, since the AiSensy API documentation/payload 
     * format is missing, this method will establish the architecture and 
     * logging without guessing the payload or template names.
     */
    public function sendTaskNotification($userId, $type, $taskData)
    {
        if (!$this->isEnabled()) {
            $this->lastError = 'AiSensy is not enabled or is missing API configuration.';
            return false;
        }

        // 1. Retrieve the employee's mobile number
        $stmt = $this->db->prepare("SELECT mobile, username FROM admin WHERE id = ? AND status = 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || empty($user['mobile'])) {
            $this->lastError = 'User has no valid mobile number.';
            $this->logDeliveryAttempt($taskData['id'] ?? null, $userId, 'no_mobile', $type, 'Failed', $this->lastError);
            return false;
        }

        $mobile = $this->normalizeMobile($user['mobile']);
        if (!$mobile) {
            $this->lastError = 'Invalid mobile number format.';
            $this->logDeliveryAttempt($taskData['id'] ?? null, $userId, $user['mobile'], $type, 'Failed', $this->lastError);
            return false;
        }

        // 2. Map notification type to AiSensy template structure
        // Without documentation, we mock this part to prevent guessing payloads.
        $payload = $this->buildPayload($type, $user, $taskData, $mobile);

        if (!$payload) {
            $this->lastError = 'WhatsApp template is not configured for ' . $type . '.';
            $this->logDeliveryAttempt($taskData['id'] ?? null, $userId, $mobile, $type, 'Failed', $this->lastError);
            return false;
        }

        // 3. Dispatch to AiSensy
        return $this->dispatch($taskData['id'] ?? null, $userId, $mobile, $type, $payload);
    }

    private function buildPayload($type, $user, $taskData, $mobile)
    {
        if ($type === 'TASK_ASSIGNED') {
            return [
                'campaignName' => 'ashish_01',
                'destination' => $mobile,
                'userName' => $user['username'],
                'templateParams' => [ $user['username'] ] // Deduced from Postman ["Ashish Raina"]
            ];
        }
        
        return null; // Missing configurations for other types
    }

    private function dispatch($taskId, $userId, $mobile, $type, $payload)
    {
        $endpoint = 'https://backend.aisensy.com/campaign/t1/api/v2';

        $payload['apiKey'] = $this->apiKey;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $status = 'Failed';
        $errorMsg = $curlError;

        if ($httpCode >= 200 && $httpCode < 300) {
            $status = 'Success';
            $errorMsg = '';
        } else if (!$errorMsg) {
            $errorMsg = "HTTP $httpCode: $response";
        }

        $this->lastError = $errorMsg;

        $this->logDeliveryAttempt($taskId, $userId, $mobile, $type, $status, $errorMsg);

        return $status === 'Success';
    }

    private function normalizeMobile($mobile)
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        if (strlen($mobile) === 10) {
            $mobile = '91' . $mobile;
        }

        // AiSensy standard Indian number is 12 digits starting with 91
        if (strlen($mobile) !== 12 || strpos($mobile, '91') !== 0) {
            return false;
        }
        
        return $mobile;
    }

    private function logDeliveryAttempt($taskId, $userId, $mobile, $type, $status, $errorMsg)
    {
        // Mask mobile for logging if needed
        $maskedMobile = strlen($mobile) > 4 ? substr_replace($mobile, '****', -4) : $mobile;
        if (strlen($mobile) === 12 && strpos($mobile, '91') === 0) {
            $maskedMobile = '+91 ' . substr($maskedMobile, 2);
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO whatsapp_delivery_logs (task_id, user_id, mobile, event_type, status, error_message) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt) {
            $stmt->bind_param('iissss', $taskId, $userId, $maskedMobile, $type, $status, $errorMsg);
            $stmt->execute();
        }
    }
}
