<?php
require_once __DIR__ . '/../includes/config.php';

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
          || (isset($_GET['ajax']) && $_GET['ajax'] == 1);

if ($id <= 0) {
    if ($isAjax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid ID provided.']);
        exit;
    }
    $_SESSION['flash_error'] = 'Invalid Terms & Conditions ID.';
    header('Location: list.php');
    exit;
}

try {
    // Check if record exists
    $stmtCheck = $conn->prepare("SELECT title FROM terms_and_conditions WHERE id = ?");
    $stmtCheck->execute([$id]);
    $title = $stmtCheck->fetchColumn();

    if ($title === false) {
        if ($isAjax) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Section not found.']);
            exit;
        }
        $_SESSION['flash_error'] = 'Terms & Conditions section not found.';
        header('Location: list.php');
        exit;
    }

    $stmtDelete = $conn->prepare("DELETE FROM terms_and_conditions WHERE id = ?");
    $stmtDelete->execute([$id]);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => "Section \"$title\" deleted successfully."]);
        exit;
    }

    $_SESSION['flash_success'] = "Section \"$title\" deleted successfully.";
    header('Location: list.php');
    exit;

} catch (Exception $e) {
    if ($isAjax) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
    $_SESSION['flash_error'] = 'Failed to delete section: ' . $e->getMessage();
    header('Location: list.php');
    exit;
}
