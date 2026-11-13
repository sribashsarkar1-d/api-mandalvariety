<?php
/**
 * Store Operating Hours & Off-Day Helper
 * Enforces business operating hours and weekly off-days based on server time.
 */

if (!function_exists('getStoreOperatingStatus')) {
    function getStoreOperatingStatus(PDO $pdo) {
        $defaults = [
            'timezone' => 'Asia/Kolkata',
            'store_order_status' => 'auto', // 'auto', 'open', 'closed'
            'store_open_time' => '07:00',
            'store_close_time' => '20:00',
            'store_off_days' => 'Monday',
            'store_closed_message' => 'রাত ৮টা থেকে সকাল ৭টা পর্যন্ত কোনো অর্ডার গ্রহণ করা হবে না। অনুগ্রহ করে সকাল ৭টা থেকে রাত ৮টার মধ্যে অর্ডার করুন।',
            'store_off_day_message' => 'আজ সোমবার আমাদের সাপ্তাহিক ছুটি। কোনো অর্ডার গ্রহণ করা হবে না। অনুগ্রহ করে পরবর্তী কার্যদিবসে অর্ডার করুন।',
        ];

        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN (
                'timezone', 'store_order_status', 'store_open_time', 'store_close_time',
                'store_off_days', 'store_closed_message', 'store_off_day_message'
            )");
            $dbRows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($dbRows)) {
                foreach ($dbRows as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $defaults[$k] = $v;
                    }
                }
            }
        } catch (Exception $e) {
            // fallback to defaults if table query fails
        }

        $tzName = !empty($defaults['timezone']) ? $defaults['timezone'] : 'Asia/Kolkata';
        try {
            $tz = new DateTimeZone($tzName);
        } catch (Exception $e) {
            $tz = new DateTimeZone('Asia/Kolkata');
            $tzName = 'Asia/Kolkata';
        }

        $now = new DateTime('now', $tz);
        $currentDay = $now->format('l'); // e.g., "Monday", "Friday"
        $currentTime = $now->format('H:i'); // 24-hr format "14:30"
        $currentTimestamp = $now->getTimestamp();

        $openTime = !empty($defaults['store_open_time']) ? trim($defaults['store_open_time']) : '07:00';
        $closeTime = !empty($defaults['store_close_time']) ? trim($defaults['store_close_time']) : '20:00';
        $storeOrderStatus = strtolower(trim($defaults['store_order_status'] ?? 'auto'));

        // Parse off days (comma-separated, case-insensitive)
        $rawOffDays = explode(',', (string)$defaults['store_off_days']);
        $offDays = [];
        foreach ($rawOffDays as $d) {
            $trimmed = trim($d);
            if ($trimmed !== '') {
                $offDays[] = ucfirst(strtolower($trimmed));
            }
        }
        if (empty($offDays)) {
            $offDays = ['Monday'];
        }

        $isOffDay = in_array($currentDay, $offDays);

        $isOpen = false;
        $reason = 'open';
        $message = 'স্টোর খোলা আছে। সকাল ৭টা থেকে রাত ৮টার মধ্যে যেকোনো সময় অর্ডার করুন।';
        $bannerTitle = 'খোলা আছে';
        $bannerSubtitle = 'সকাল ৭:০০ - রাত ৮:০০';

        if ($storeOrderStatus === 'closed') {
            $isOpen = false;
            $reason = 'manual_closed';
            $message = 'দোকান বর্তমানে সাময়িকভাবে বন্ধ আছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।';
            $bannerTitle = 'অর্ডার গ্রহণ বন্ধ';
            $bannerSubtitle = 'দোকান সাময়িকভাবে বন্ধ রাখা হয়েছে';
        } elseif ($storeOrderStatus === 'open') {
            $isOpen = true;
            $reason = 'manual_open';
            $message = 'স্টোর খোলা আছে। আপনার পছন্দের পণ্য অর্ডার করুন।';
            $bannerTitle = 'খোলা আছে';
            $bannerSubtitle = 'অর্ডার গ্রহণ চলছে';
        } else {
            // Automatic check based on weekly off-day and operating hours
            if ($isOffDay) {
                $isOpen = false;
                $reason = 'off_day';
                $message = $defaults['store_off_day_message'];
                $bannerTitle = 'আজ ' . $currentDay . ' সাপ্তাহিক ছুটি';
                $bannerSubtitle = 'আজ কোনো নতুন অর্ডার নেওয়া হবে না';
            } elseif ($currentTime < $openTime || $currentTime >= $closeTime) {
                $isOpen = false;
                $reason = 'night_closed';
                $message = $defaults['store_closed_message'];
                $bannerTitle = 'রাত ৮:০০ - সকাল ৭:০০ অর্ডার গ্রহণ বন্ধ';
                $bannerSubtitle = 'সকাল ৭:০০ টায় আবার অর্ডার গ্রহণ শুরু হবে';
            } else {
                $isOpen = true;
                $reason = 'open';
                $message = 'স্টোর খোলা আছে। সকাল ৭:০০ থেকে রাত ৮:০০ এর মধ্যে অর্ডার করুন।';
                $bannerTitle = 'খোলা আছে';
                $bannerSubtitle = 'সকাল ৭:০০ - রাত ৮:০০ ডেলিভারি সার্ভিস সক্রিয়';
            }
        }

        return [
            'is_open' => (bool)$isOpen,
            'reason' => $reason,
            'current_day' => $currentDay,
            'current_time' => $currentTime,
            'server_time' => $now->format('Y-m-d H:i:s'),
            'server_timestamp' => $currentTimestamp,
            'timezone' => $tzName,
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'off_days' => $offDays,
            'is_off_day' => (bool)$isOffDay,
            'message' => $message,
            'banner_title' => $bannerTitle,
            'banner_subtitle' => $bannerSubtitle,
        ];
    }
}

