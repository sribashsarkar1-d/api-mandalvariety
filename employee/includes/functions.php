<?php
// includes/functions.php

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function json_response($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function format_currency($amount) {
    return '₹' . number_format((float)$amount, 2, '.', '');
}

function format_date($datetime) {
    if (!$datetime) return '-';
    return date('d-m-Y h:i A', strtotime($datetime));
}

function generate_invoice_number($pdo) {
    $prefix = "INV-" . date("Y") . "-";
    // Get last invoice number
    $stmt = $pdo->query("SELECT invoice_number FROM employee_sales ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();
    
    if ($row) {
        $last_inv = $row['invoice_number'];
        $parts = explode('-', $last_inv);
        $num = (int)end($parts);
        $new_num = str_pad($num + 1, 5, '0', STR_PAD_LEFT);
        return $prefix . $new_num;
    } else {
        return $prefix . "00001";
    }
}
?>
