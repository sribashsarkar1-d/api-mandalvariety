<?php

/**
 * Sends a push notification using Firebase Cloud Messaging (FCM) HTTP v1 API or legacy API.
 * 
 * NOTE: If using the new HTTP v1 API, you need to use an OAuth2 token instead of a server key.
 * Below is the implementation for the Legacy API (Server Key). If you have migrated to HTTP v1,
 * you will need to replace the Authorization header with a valid OAuth2 bearer token.
 *
 * @param string|array $to The FCM token(s) or topic (e.g., '/topics/all_users')
 * @param string $title The notification title
 * @param string $body The notification body
 * @param string|null $imageUrl The URL of the image to show in the notification
 * @param array $data Additional data payload
 * @return string|bool The response from FCM or false on failure
 */
function sendFCMNotification($to, $title, $body, $imageUrl = null, $data = []) {
    // TODO: Replace with your actual Firebase Server Key from Firebase Console
    // Project Settings -> Cloud Messaging -> Cloud Messaging API (Legacy)
    $serverKey = 'YOUR_FCM_SERVER_KEY_HERE';
    $url = 'https://fcm.googleapis.com/fcm/send';

    $notification = [
        'title' => $title,
        'body'  => $body,
        'sound' => 'default'
    ];

    if ($imageUrl) {
        $notification['image'] = $imageUrl; // Used by Android & iOS to show a rich notification image
    }

    $fields = [
        'notification' => $notification,
        'data'         => $data
    ];

    if (is_array($to)) {
        $fields['registration_ids'] = $to; // Multicast
    } else {
        $fields['to'] = $to; // Topic or single token
    }

    $headers = [
        'Authorization: key=' . $serverKey,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);
    
    if ($result === FALSE) {
        error_log('FCM Send Error: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    return $result;
}
?>
