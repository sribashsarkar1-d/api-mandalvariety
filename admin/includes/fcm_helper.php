<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

/**
 * Sends a push notification using Firebase Cloud Messaging (FCM) HTTP v1 API.
 * 
 * @param string|array $to The FCM token(s)
 * @param string $title The notification title
 * @param string $body The notification body
 * @param string|null $imageUrl The URL of the image to show in the notification
 * @param array $data Additional data payload
 * @return string|array|bool The response from FCM or false on failure
 */
function sendFCMNotification($to, $title, $body, $imageUrl = null, $data = []) {
    // Check local path (XAMPP) and live server path (Hostinger)
    $localKeyFilePath = __DIR__ . '/../../config/firebase_credentials.json';
    $serverKeyFilePath = '/home/u391326945/firebase-secrets/firebase_credentials.json';
    
    if (file_exists($serverKeyFilePath)) {
        $keyFilePath = $serverKeyFilePath;
    } elseif (file_exists($localKeyFilePath)) {
        $keyFilePath = $localKeyFilePath;
    } else {
        error_log('FCM Send Error: Service account JSON file not found at local or server path.');
        return false;
    }

    try {
        // Read project ID from the JSON file
        $keyData = json_decode(file_get_contents($keyFilePath), true);
        $projectId = $keyData['project_id'] ?? null;

        if (!$projectId) {
            error_log('FCM Send Error: Project ID not found in Service Account JSON.');
            return false;
        }

        // Initialize Google Client for Auth
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $keyFilePath);
        $tokenInfo = $credentials->fetchAuthToken();
        $accessToken = $tokenInfo['access_token'];

        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

        $notification = [
            'title' => $title,
            'body'  => $body,
        ];
        
        if ($imageUrl) {
            $notification['image'] = $imageUrl;
        }

        // HTTP v1 API structure
        $message = [
            'notification' => $notification,
        ];
        
        if (!empty($data)) {
            // Data values MUST be strings in HTTP v1 API
            $stringData = [];
            foreach ($data as $key => $value) {
                // If it's an array or object, json_encode it. Otherwise cast to string.
                if (is_array($value) || is_object($value)) {
                    $stringData[$key] = json_encode($value);
                } else {
                    $stringData[$key] = (string) $value;
                }
            }
            $message['data'] = $stringData;
        }

        $results = [];
        $tokens = is_array($to) ? $to : [$to];

        $client = new Client();

        // Since v1 API does not support multicast directly in messages:send, we loop over tokens
        foreach ($tokens as $token) {
            if (empty($token)) continue;

            $message['token'] = $token;

            $payload = [
                'message' => $message
            ];

            try {
                $response = $client->post($url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $payload
                ]);

                $results[] = json_decode($response->getBody()->getContents(), true);
            } catch (\Exception $e) {
                error_log('FCM Send Error for token ' . $token . ': ' . $e->getMessage());
                $results[] = false;
            }
        }

        // Maintain backward compatibility with the expected return format
        return is_array($to) ? $results : json_encode($results[0] ?? false);

    } catch (\Exception $e) {
        error_log('FCM Auth/Init Error: ' . $e->getMessage());
        return false;
    }
}
?>
