<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

// Mock stats for demonstration to match the design exactly
$completed_deliveries = 8;
$base_charge = 300;
$cod_handling = 100;
$incentives = 20;
$total_earnings = $base_charge + $cod_handling + $incentives;

?>
<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        padding: 20px 20px 10px 20px;
        background: #fff;
    }
    
    .back-btn {
        color: var(--text-dark);
        font-size: 1.2rem;
        margin-right: 15px;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .earnings-container {
        padding: 20px;
    }

    /* Tabs */
    .tabs-wrapper {
        background: #f1f5f9;
        border-radius: 30px;
        display: flex;
        padding: 4px;
        margin-bottom: 24px;
    }

    .tab-btn {
        flex: 1;
        text-align: center;
        padding: 10px 0;
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        border-radius: 30px;
        cursor: pointer;
    }
    
    .tab-btn.active {
        background: var(--mandal-green);
        color: white;
        box-shadow: 0 4px 10px rgba(7, 161, 88, 0.2);
    }

    /* Main Earnings Block */
    .earnings-block {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .earnings-label {
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .earnings-amount {
        font-size: 42px;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0;
        line-height: 1;
    }

    .deliveries-count {
        font-size: 13px;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 8px;
    }

    /* Breakdown */
    .breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .breakdown-row:last-child {
        border-bottom: none;
    }

    .breakdown-label {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
    }
    
    .breakdown-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .btn-view-details {
        background: var(--mandal-green);
        color: white;
        text-align: center;
        border-radius: 12px;
        padding: 16px;
        font-weight: 700;
        font-size: 16px;
        display: block;
        text-decoration: none;
        margin-top: 30px;
        box-shadow: 0 4px 15px rgba(7, 161, 88, 0.2);
    }
</style>

<div class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
    <h1 class="page-title">My Earnings</h1>
</div>

<div class="earnings-container">
    
    <div class="tabs-wrapper">
        <div class="tab-btn active">Daily</div>
        <div class="tab-btn">Weekly</div>
        <div class="tab-btn">Monthly</div>
    </div>

    <div class="earnings-block">
        <h2 class="earnings-amount">₹<?= $total_earnings ?></h2>
        <div class="deliveries-count">Completed Deliveries: <?= $completed_deliveries ?></div>
    </div>

    <div style="margin-top: 40px;">
        <div class="breakdown-row">
            <span class="breakdown-label">Base Delivery Charge</span>
            <span class="breakdown-value">₹<?= $base_charge ?></span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">COD Handling</span>
            <span class="breakdown-value">₹<?= $cod_handling ?></span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Incentives</span>
            <span class="breakdown-value">₹<?= $incentives ?></span>
        </div>
    </div>

    <a href="#" class="btn-view-details">View Details</a>

</div>

<?php include 'includes/footer.php'; ?>
