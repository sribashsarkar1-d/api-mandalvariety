<?php
require '../vendor/autoload.php';
include '../includes/config.php';

use Dompdf\Dompdf;

$id = $_GET['id'];

$order = $conn->query("
SELECT orders.*, users.name, users.email 
FROM orders 
JOIN users ON orders.user_id = users.id
WHERE orders.id = $id
")->fetch();

$html = "
<h2>Invoice</h2>
<p>Name: {$order['name']}</p>
<p>Email: {$order['email']}</p>
<p>Total: ₹{$order['total_amount']}</p>
<p>Status: {$order['status']}</p>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("invoice_$id.pdf");