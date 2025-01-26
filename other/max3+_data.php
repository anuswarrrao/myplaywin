<?php
include('../config/db.php');

// Sanitize and validate input
$view = isset($_GET['view']) ? filter_var($_GET['view'], FILTER_SANITIZE_STRING) : 'latest';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = 10; 
$offset = ($page - 1) * $rowsPerPage;

$currentDate = date('Y-m-d');

// Prepare SQL query
if ($view === 'previous') {
    $stmt = $conn->prepare("SELECT * FROM daily_games WHERE game_name = 'max3+' LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $rowsPerPage, $offset);
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM daily_games WHERE game_name = 'max3+'");
} else {
    $stmt = $conn->prepare("SELECT * FROM daily_games WHERE game_name = 'max3+' AND DATE(game_time) = ? LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $currentDate, $rowsPerPage, $offset);
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM daily_games WHERE game_name = 'max3+' AND DATE(game_time) = ?");
    $countStmt->bind_param("s", $currentDate);
}

$stmt->execute();
$result = $stmt->get_result();

$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// Generate table rows
$rows = '';
while ($game = $result->fetch_assoc()) {
    $couponParts = explode(" ", $game['coupon_number']);
    $coupons = '';
    foreach ($couponParts as $part) {
        $coupons .= '<span class="text-dark">' . htmlspecialchars($part) . '</span>';
    }
    $rows .= '<tr>
        <td>' . htmlspecialchars(date('g:i A', strtotime($game['game_time']))) . '</td>
        <td>' . $coupons . '</td>
    </tr>';
}

if (empty($rows)) {
    $rows = '<tr><td colspan="2">No max3+ games found.</td></tr>';
}

$title = $view === 'previous' ? 'All Time (Previous Results)' : 'Today';

echo json_encode([
    'rows' => $rows,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'title' => "Showing details for $title (" . date('l, F j, Y', strtotime($currentDate)) . ")"
]);

$stmt->close();
$countStmt->close();
$conn->close();
?>
