<?php
include('../config/db.php');

// Sanitize and validate input
$view = isset($_GET['view']) ? filter_var($_GET['view'], FILTER_SANITIZE_STRING) : 'latest';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$rowsPerPage = 10; 
$offset = ($page - 1) * $rowsPerPage;

$currentDate = date('Y-m-d');

// Prepare SQL query based on view
if ($view === 'previous') {
    // Show all results for previous results
    $stmt = $conn->prepare("SELECT * FROM weekly_jackpot ORDER BY updated_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $rowsPerPage, $offset);
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM weekly_jackpot");
} else {
    // Show today's results only
    $stmt = $conn->prepare("SELECT * FROM weekly_jackpot WHERE DATE(updated_at) = ? ORDER BY updated_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $currentDate, $rowsPerPage, $offset);
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM weekly_jackpot WHERE DATE(updated_at) = ?");
    $countStmt->bind_param("s", $currentDate);
}

// Execute queries
$stmt->execute();
$result = $stmt->get_result();

$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// Generate table rows
$rows = '';
while ($game = $result->fetch_assoc()) {
    // Display coupon numbers
    $couponParts = explode(" ", $game['coupon_number']);
    $coupons = '';
    foreach ($couponParts as $part) {
        $coupons .= '<span class="text-dark">' . htmlspecialchars($part) . '</span>';
    }
    
    // Display second winning numbers with "-" between them
    $secondWinningNumbers = explode(" ", $game['second_winning_numbers']);
    $secondWinningHTML = '';
    foreach ($secondWinningNumbers as $index => $num) {
        // Add "-" between numbers, except for the last number
        $secondWinningHTML .= '<span class="text-warning">' . htmlspecialchars($num) . '</span>';
        if ($index < count($secondWinningNumbers) - 1) {
            $secondWinningHTML .= ' - '; // Add "-" after each number except the last one
        }
    }

    // Format the next draw date
    $nextDrawDate = date('l, F j, Y g:i A', strtotime($game['next_draw_date']));

    // Format the updated_at timestamp
    $updatedAt = date('l, F j, Y g:i A', strtotime($game['updated_at']));

    $rows .= '<tr>
        <td>' . $updatedAt . '</td>
        <td>' . $coupons . '</td>
        <td>' . $secondWinningHTML . '</td>
        <td>' . $nextDrawDate . '</td>
    </tr>';
}

// If no rows were found, show a message
if (empty($rows)) {
    $rows = '<tr><td colspan="4">No king4+ games found.</td></tr>';
}

// Determine title based on the view
$title = $view === 'previous' ? 'All Time (Previous Results)' : 'Today';

// Output the data in JSON format for AJAX response
echo json_encode([
    'rows' => $rows,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'title' => "Showing details for $title (" . date('l, F j, Y', strtotime($currentDate)) . ")"
]);

// Close statements and connection
$stmt->close();
$countStmt->close();
$conn->close();
?>
