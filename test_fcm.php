<?php
require_once __DIR__ . '/admin/includes/fcm_helper.php';

$token = 'fCA2BcFgQxW6fUvrRj79li:APA91bHp2O3yM3zj8CJcbixWFyx_yijLFWTKBG-Hk9Z4Vnjd--hHPdtFFUrDechrihzQEqE317i7oNApxdIyrhoxomPe0wbETY4VyW-0AGC_57nyz3LTWF8';
$title = 'Test Notification';
$body = 'This is a test notification from your API';
$data = ['test' => true];

$result = sendFCMNotification($token, $title, $body, null, $data);

echo "FCM Result:\n";
print_r(json_decode($result, true));
?>
