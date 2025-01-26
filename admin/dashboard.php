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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <p><a href="logout.php" class="btn btn-danger">Logout</a></p>

    <h3 class="mt-4">Add a New Game</h3>
    <form method="POST" class="form-group">
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

        <button type="submit" name="add_game" class="btn btn-primary">Add Game</button>
    </form>

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

                        <button type="submit" name="edit_game" class="btn btn-success">Update Game</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">All Games</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Game Name</th>
                <th>Game Time</th>
                <th>Coupon Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($game = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($game['game_name']) ?></td>
                    <td><?= date('l, F j, Y g:i A', strtotime($game['game_time'])) ?></td>
                    <td>
                        <?php
                        $coupon_parts = explode(" ", $game['coupon_number']);
                        foreach ($coupon_parts as $part) {
                            echo '<span class="badge bg-info text-dark">' . htmlspecialchars($part) . '</span> ';
                        }
                        ?>
                    </td>
                    <td>
                        <button onclick="editGame(<?= $game['id'] ?>, '<?= htmlspecialchars($game['game_name']) ?>', '<?= $game['game_time'] ?>', '<?= htmlspecialchars($game['coupon_number']) ?>')" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
                        <a href="?delete=<?= $game['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3 class="mt-5">Add New Weekly Jackpot</h3>
    <form method="POST" class="form-group">
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

        <button type="submit" name="add_jackpot" class="btn btn-primary">Add Jackpot</button>
    </form>

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

                        <button type="submit" name="edit_jackpot" class="btn btn-success">Update Jackpot</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">All Weekly Jackpots</h3>
    <table class="table table-bordered">
        <thead>
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
            <?php while ($row = $jackpotResults->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['coupon_number']) ?></td>
                    <td><?= htmlspecialchars($row['second_winning_numbers']) ?></td>
                    <td><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($row['next_draw_date']))) ?></td>
                    <td><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($row['updated_at']))) ?></td>
                    <td>
                        <button onclick="editJackpot(<?= $row['id'] ?>, '<?= htmlspecialchars($row['coupon_number']) ?>', '<?= htmlspecialchars($row['second_winning_numbers']) ?>', '<?= $row['next_draw_date'] ?>')" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editJackpotModal">Edit</button>
                        <a href="?delete_jackpot=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this jackpot?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    function editGame(id, name, time, coupon) {
        document.getElementById('edit_game_id').value = id;
        document.getElementById('edit_game_name').value = name;
        document.getElementById('edit_game_time').value = time;
        document.getElementById('edit_coupon_number').value = coupon;

        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>

<script>
    function editJackpot(id, coupon, second, drawDate) {
        document.getElementById('edit_jackpot_id').value = id;
        document.getElementById('edit_coupon_number').value = coupon;
        document.getElementById('edit_second_winning_numbers').value = second;
        document.getElementById('edit_next_draw_date').value = drawDate;

        document.getElementById('editJackpotModal').style.display = 'block';
    }

    function closeJackpotModal() {
        document.getElementById('editJackpotModal').style.display = 'none';
    }
</script>

</body>
</html>



