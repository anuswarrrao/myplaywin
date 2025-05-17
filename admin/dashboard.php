<?php
session_start();

// Enforce session protection
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Set HTTP Security Headers
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

include('../config/db.php');

// Handle form submission for adding a game
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_game'])) {
    $game_name = $_POST['game_name'];
    $game_time = $_POST['game_time'];
    $coupon_number = $_POST['coupon_number'];

    // Insert new game into the database
    $stmt = $conn->prepare("INSERT INTO daily_games (game_name, game_time, coupon_number) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $game_name, $game_time, $coupon_number);
    $stmt->execute();
    $stmt->close();
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_game'])) {
    $id = $_POST['game_id'];
    $game_name = $_POST['game_name'];
    $game_time = $_POST['game_time'];
    $coupon_number = $_POST['coupon_number'];

    $stmt = $conn->prepare("UPDATE daily_games SET game_name = ?, game_time = ?, coupon_number = ? WHERE id = ?");
    $stmt->bind_param("sssi", $game_name, $game_time, $coupon_number, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM daily_games WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all games, ordered by the latest on top
$result = $conn->query("SELECT * FROM daily_games ORDER BY id DESC");

// Count games by type
$gameCounts = [];
$gameTypesQuery = $conn->query("SELECT game_name, COUNT(*) as count FROM daily_games GROUP BY game_name");
while ($row = $gameTypesQuery->fetch_assoc()) {
    $gameCounts[$row['game_name']] = $row['count'];
}

// Add new Weekly Jackpot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_jackpot'])) {
    $coupon_number = $_POST['coupon_number'];
    $second_winning_numbers = $_POST['second_winning_numbers'];
    $next_draw_date = $_POST['next_draw_date'];

    $stmt = $conn->prepare("
        INSERT INTO weekly_jackpot (coupon_number, second_winning_numbers, next_draw_date, updated_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("sss", $coupon_number, $second_winning_numbers, $next_draw_date);
    $stmt->execute();
    $stmt->close();
}

// Handle Edit for Weekly Jackpot
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_jackpot'])) {
    $id = $_POST['jackpot_id'];
    $coupon_number = $_POST['coupon_number'];
    $second_winning_numbers = $_POST['second_winning_numbers'];
    $next_draw_date = $_POST['next_draw_date'];

    $stmt = $conn->prepare("UPDATE weekly_jackpot SET coupon_number = ?, second_winning_numbers = ?, next_draw_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sssi", $coupon_number, $second_winning_numbers, $next_draw_date, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle Delete for Weekly Jackpot
if (isset($_GET['delete_jackpot'])) {
    $id = $_GET['delete_jackpot'];
    $stmt = $conn->prepare("DELETE FROM weekly_jackpot WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all jackpots (latest first)
$jackpotResults = $conn->query("SELECT * FROM weekly_jackpot ORDER BY updated_at DESC");
$jackpotCount = $jackpotResults->num_rows;

// Count total games
$totalGamesResult = $conn->query("SELECT COUNT(*) as total FROM daily_games");
$totalGames = $totalGamesResult->fetch_assoc()['total'];

// Get upcoming games
$upcomingGamesResult = $conn->query("SELECT * FROM daily_games WHERE game_time > NOW() ORDER BY game_time ASC LIMIT 5");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lottery Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .content {
            padding: 20px;
        }

        .badge-custom {
            font-size: 0.9rem;
            margin: 2px;
            padding: 5px 8px;
        }

        .dashboard-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .dashboard-stats {
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .tab-content {
            padding: 20px 0;
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark d-lg-none">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Lottery Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#dashboard" data-bs-toggle="pill" role="tab">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#daily-games" data-bs-toggle="pill" role="tab">Daily Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#weekly-jackpot" data-bs-toggle="pill" role="tab">Weekly Jackpot</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation (visible on medium devices and up) -->
            <div class="col-md-3 col-lg-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <h3>Lottery Admin</h3>
                    <p class="text-light opacity-75">Management System</p>
                </div>
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="dashboard-tab" data-bs-toggle="pill" href="#dashboard" role="tab" aria-controls="dashboard" aria-selected="true">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link" id="daily-games-tab" data-bs-toggle="pill" href="#daily-games" role="tab" aria-controls="daily-games" aria-selected="false">
                        <i class="fas fa-gamepad"></i> Daily Games
                    </a>
                    <a class="nav-link" id="weekly-jackpot-tab" data-bs-toggle="pill" href="#weekly-jackpot" role="tab" aria-controls="weekly-jackpot" aria-selected="false">
                        <i class="fas fa-trophy"></i> Weekly Jackpot
                    </a>
                    <a class="nav-link text-danger mt-auto" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                        <div class="dashboard-header d-flex justify-content-between align-items-center">
                            <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
                            <div>
                                <span class="text-muted"><?= date('l, F j, Y') ?></span>
                            </div>
                        </div>

                        <div class="row dashboard-stats">
                            <div class="col-md-3">
                                <div class="stat-card text-center bg-primary text-white">
                                    <i class="fas fa-calendar-day fa-3x mb-3"></i>
                                    <h4>Total Games</h4>
                                    <h2><?= $totalGames ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center bg-success text-white">
                                    <i class="fas fa-trophy fa-3x mb-3"></i>
                                    <h4>Jackpots</h4>
                                    <h2><?= $jackpotCount ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center bg-warning text-dark">
                                    <i class="fas fa-clock fa-3x mb-3"></i>
                                    <h4>Upcoming Games</h4>
                                    <h2><?= $upcomingGamesResult->num_rows ?></h2>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center bg-info text-white">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <h4>Admin Users</h4>
                                    <h2>1</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Games Tab -->
                    <div class="tab-pane fade" id="daily-games" role="tabpanel" aria-labelledby="daily-games-tab">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-gamepad"></i> Daily Games Management</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGameModal">
                                <i class="fas fa-plus"></i> Add New Game
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Game Name</th>
                                                <th>Game Time</th>
                                                <th>Coupon Number</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Reset result pointer
                                            $result->data_seek(0);
                                            while ($game = $result->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($game['game_name']) ?></td>
                                                    <td><?= date('l, F j, Y g:i A', strtotime($game['game_time'])) ?></td>
                                                    <td>
                                                        <?php
                                                        $coupon_parts = explode(" ", $game['coupon_number']);
                                                        foreach ($coupon_parts as $part) {
                                                            echo '<span class="badge bg-info text-dark badge-custom">' . htmlspecialchars($part) . '</span> ';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <button onclick="editGame(<?= $game['id'] ?>, '<?= htmlspecialchars($game['game_name']) ?>', '<?= $game['game_time'] ?>', '<?= htmlspecialchars($game['coupon_number']) ?>')" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <a href="?delete=<?= $game['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this game?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Jackpot Tab -->
                    <div class="tab-pane fade" id="weekly-jackpot" role="tabpanel" aria-labelledby="weekly-jackpot-tab">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-trophy"></i> Weekly Jackpot Management</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJackpotModal">
                                <i class="fas fa-plus"></i> Add New Jackpot
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Coupon Number</th>
                                                <th>Second Winning Numbers</th>
                                                <th>Next Draw Date</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Reset jackpot results pointer
                                            $jackpotResults->data_seek(0);
                                            while ($row = $jackpotResults->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td>
                                                        <span class="badge bg-success badge-custom"><?= htmlspecialchars($row['coupon_number']) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $second_numbers = explode(" ", $row['second_winning_numbers']);
                                                        foreach ($second_numbers as $num) {
                                                            echo '<span class="badge bg-primary badge-custom">' . htmlspecialchars($num) . '</span> ';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($row['next_draw_date']))) ?></td>
                                                    <td><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($row['updated_at']))) ?></td>
                                                    <td>
                                                        <button onclick="editJackpot(<?= $row['id'] ?>, '<?= htmlspecialchars($row['coupon_number']) ?>', '<?= htmlspecialchars($row['second_winning_numbers']) ?>', '<?= $row['next_draw_date'] ?>')" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editJackpotModal">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <a href="?delete_jackpot=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this jackpot?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Game Modal -->
    <div class="modal fade" id="addGameModal" tabindex="-1" aria-labelledby="addGameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGameModalLabel">Add New Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="game_name" class="form-label">Game Name:</label>
                            <select name="game_name" id="game_name" class="form-select" required>
                                <option value="Lucky3">Lucky3</option>
                                <option value="Super Triple Ticket">Super Triple Ticket</option>
                                <option value="Lucky3+">Lucky3+</option>
                                <option value="Super Triple Ticket +">Super Triple Ticket +</option>
                                <option value="Max3+">Max3+</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="game_time" class="form-label">Game Time:</label>
                            <input type="datetime-local" name="game_time" id="game_time" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="coupon_number" class="form-label">Coupon Number:</label>
                            <input type="text" name="coupon_number" id="coupon_number" class="form-control" placeholder="Enter numbers/symbols separated by spaces" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="add_game" class="btn btn-primary">Add Game</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Game Modal -->
    <div id="editModal" class="modal fade" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="game_id" id="edit_game_id">
                        <div class="mb-3">
                            <label for="edit_game_name" class="form-label">Game Name:</label>
                            <select name="game_name" id="edit_game_name" class="form-select" required>
                                <option value="Lucky3">Lucky3</option>
                                <option value="Super Triple Ticket">Super Triple Ticket</option>
                                <option value="Lucky3+">Lucky3+</option>
                                <option value="Super Triple Ticket +">Super Triple Ticket +</option>
                                <option value="Max3+">Max3+</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_game_time" class="form-label">Game Time:</label>
                            <input type="datetime-local" name="game_time" id="edit_game_time" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_coupon_number" class="form-label">Coupon Number:</label>
                            <input type="text" name="coupon_number" id="edit_coupon_number" class="form-control" placeholder="Enter numbers/symbols separated by spaces" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" name="edit_game" class="btn btn-success">Update Game</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Jackpot Modal -->
    <div class="modal fade" id="addJackpotModal" tabindex="-1" aria-labelledby="addJackpotModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addJackpotModalLabel">Add New Weekly Jackpot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="coupon_number" class="form-label">Coupon Number:</label>
                            <input type="text" name="coupon_number" id="coupon_number" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="second_winning_numbers" class="form-label">2nd Winning Numbers:</label>
                            <input type="text" name="second_winning_numbers" id="second_winning_numbers" class="form-control" placeholder="e.g., 1234 5678 9101" required>
                        </div>

                        <div class="mb-3">
                            <label for="next_draw_date" class="form-label">Next Draw Date:</label>
                            <input type="datetime-local" name="next_draw_date" id="next_draw_date" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="add_jackpot" class="btn btn-primary">Add Jackpot</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Jackpot Modal -->
    <div id="editJackpotModal" class="modal fade" tabindex="-1" aria-labelledby="editJackpotModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJackpotModalLabel">Edit Jackpot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="jackpot_id" id="edit_jackpot_id">

                        <div class="mb-3">
                            <label for="edit_coupon_number" class="form-label">Coupon Number:</label>
                            <input type="text" name="coupon_number" id="edit_coupon_number" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_second_winning_numbers" class="form-label">2nd Winning Numbers:</label>
                            <input type="text" name="second_winning_numbers" id="edit_second_winning_numbers" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_next_draw_date" class="form-label">Next Draw Date:</label>
                            <input type="datetime-local" name="next_draw_date" id="edit_next_draw_date" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" name="edit_jackpot" class="btn btn-success">Update Jackpot</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        function editGame(id, name, time, coupon) {
            document.getElementById('edit_game_id').value = id;
            document.getElementById('edit_game_name').value = name;
            // Format the datetime-local input value
            const gameTime = new Date(time);
            const formattedTime = gameTime.toISOString().slice(0, 16);
            document.getElementById('edit_game_time').value = formattedTime;
            document.getElementById('edit_coupon_number').value = coupon;
        }

        function editJackpot(id, coupon, second, drawDate) {
            document.getElementById('edit_jackpot_id').value = id;
            document.getElementById('edit_coupon_number').value = coupon;
            document.getElementById('edit_second_winning_numbers').value = second;
            // Format the datetime-local input value
            const jackpotDate = new Date(drawDate);
            const formattedDate = jackpotDate.toISOString().slice(0, 16);
            document.getElementById('edit_next_draw_date').value = formattedDate;
        }

        // Set current datetime as default for new entries
        document.addEventListener('DOMContentLoaded', function() {
            // Set default datetime for new game
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const isoNow = now.toISOString().slice(0, 16);

            const gameTimeInputs = document.querySelectorAll('input[type="datetime-local"]');
            gameTimeInputs.forEach(input => {
                if (input.id === 'game_time' || input.id === 'next_draw_date') {
                    input.value = isoNow;
                }
            });
        });

        // Confirmation for form submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Only confirm for forms with add_game, edit_game, add_jackpot, edit_jackpot
                    if (form.querySelector('[name="add_game"]') ||
                        form.querySelector('[name="edit_game"]') ||
                        form.querySelector('[name="add_jackpot"]') ||
                        form.querySelector('[name="edit_jackpot"]')) {

                        const isConfirmed = confirm('Are you sure you want to save these changes?');
                        if (!isConfirmed) {
                            e.preventDefault();
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
