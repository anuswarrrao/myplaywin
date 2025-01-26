<?php
include('config/db.php');

// Fetch the latest weekly jackpot record
$jackpotResult = $conn->query("
    SELECT * FROM weekly_jackpot 
    ORDER BY updated_at DESC LIMIT 1
");

// Fetch the latest record for each game
$result = $conn->query("
    SELECT t1.*
    FROM daily_games t1
    INNER JOIN (
        SELECT game_name, MAX(id) AS latest_id
        FROM daily_games
        GROUP BY game_name
    ) t2 ON t1.id = t2.latest_id
    ORDER BY t1.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>&nbsp;--::&nbsp;&nbsp;Welcome to Playwin&nbsp;&nbsp;--&nbsp;&nbsp;Khelo India Khelo&nbsp;&nbsp;::--&nbsp;&nbsp;&nbsp;&nbsp;</title>
  <link rel="icon" href="assets/image/logo/playwin.jpg" type="image/x-icon" />

  <!-- Stylesheets -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />

  <!-- External Scripts -->
  <script defer src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/jquery.marquee/jquery.marquee.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="js/script.js"></script>
</head>

<body>
  <!--==================== HAEDER ====================-->
  <header class="text-white">
    <div class="container-fluid fdfwef">
      <!-- Top Message -->
      <div class="row py-1">
        <div class="col">
          <div class="marquee">
            myplaywin4.com and myplaywin4.net are only official websites of
            Playwin Group, we are not responsible for game results outside
            from myplaywin4.com, myplaywin4.net....... KING4 Sunday Jackpot a
            new Game Every Sunday, play and win more!........"Khelo India
            Khelo".
          </div>
        </div>
      </div>
      <!-- Navigation Menu -->
      <div class="row align-items-center py-2">
        <!-- Big Screen Logo and Text -->
        <div style="width: 100%" class="col-12 col-md-4 d-flex justify-content-between align-items-center">
          <div class="logo-container d-flex align-items-center">
            <img src="assets/image/logo/playwin.jpg" alt="PlayWin Logo" class="img-fluid playwin-logo" />
          </div>

          <div
            class="khelo-text-container d-flex align-items-center justify-content-center">
            <img src="assets/image/logo/slogan.gif" alt="Khelo India Khelo" class="img-fluid khelo-logo" />
          </div>

          <!-- Small Screen Logo and Collapsible Button -->
          <div class="col-6 col-md-8 text-end d-md-none">
            <button class="btn btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
              Menu
            </button>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <hr class="border-light my-3" />

      <div class="row">
        <div class="col-12">
          <!-- Collapsible Navigation -->
          <div id="navMenu" class="collapse d-md-block">
            <nav class="nav flex-column flex-md-row justify-content-center mt-2 mt-md-0 nav-menu">
              <a class="nav-link px-3 py-2" href="#">REGISTER NOW</a>
              <a class="nav-link px-3 py-2" href="#">MY ACCOUNT</a>
              <a class="nav-link px-3 py-2" href="#">PLAY ONLINE</a>
              <a class="nav-link px-3 py-2" href="#">GAME INFO</a>
              <a class="nav-link px-3 py-2" href="#">RESULTS</a>
              <a class="nav-link px-3 py-2" href="#">WINNER'S CLUB</a>
              <a class="nav-link px-3 py-2" href="#">PLAY USING SMS/PHONE</a>
              <a class="nav-link px-3 py-2" href="#">PLAYWIN FOUNDATION</a>
              <a class="nav-link px-3 py-2" href="#">WATCH RESULT ON TV</a>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </header>

  <!--==================== MAIN ====================-->
  <main class="container-fluid">
    <div class="row">
      <!-- Weekly Jackpot Section -->
      <div class="col-md-4 my-5">
        <a href="other/king4+.php" class="text-decoration-none">
          <div class="card text-white border-0 jackpot-card">
            <?php if ($jackpot = $jackpotResult->fetch_assoc()): ?>
              <div class="card-body text-center text-white text-decoration-none mt-5">
                <p class="mb-2"><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($jackpot['updated_at']))) ?></p>
                <img src="assets/image/game/king4+.jpg" alt="Weekly Jackpot" class="img-fluid mb-3" />
                <h3 class="fw-bold text-success"><?= htmlspecialchars($jackpot['coupon_number']) ?></h3>
                <p class="mb-2"><strong>2nd Winning Numbers:</strong></p>
                <?php
                $winningNumbers = explode(" ", $jackpot['second_winning_numbers']);
                foreach ($winningNumbers as $number) {
                  echo "<p class='fw-bold text-warning'>$number</p>";
                }
                ?>
                <p class="mt-4"><strong>Next Draw:</strong></p>
                <p><?= htmlspecialchars(date('l, F j, Y g:i A', strtotime($jackpot['next_draw_date']))) ?></p>
              </div>
            <?php else: ?>
              <p>No jackpot data available.</p>
            <?php endif; ?>
          </div>
        </a>
      </div>

      <!-- Daily Games Section -->
      <div class="col-md-8 my-5">
        <div class="card card-t text-white border-0" style="background-color: transparent;">
          <div class="card-header text-center border-0 d-flex align-items-center">
            <img src="assets/image/logo/dailly-games.png" alt="Daily Games" class="me-3" style="width: auto; height: 40px;">
          </div>

          <div class="card-body">
            <div class="row">
              <?php while ($game = $result->fetch_assoc()): ?>
                <!-- Game Item -->
                <a class="col-md-6 mb-3 text-white text-decoration-none" href="other/<?= strtolower(str_replace(' ', '', $game['game_name'])) ?>.php">
                  <div class="d-flex align-items-center">
                    <img src="assets/image/game/<?= strtolower(str_replace(' ', '', $game['game_name'])) ?>.jpg" alt="<?= htmlspecialchars($game['game_name']) ?>" class="img-fluid me-3" />
                    <div>
                      <p class="mb-1"><?= date('l, F j, Y g:i A', strtotime($game['game_time'])) ?></p>
                      <h6 class="fw-bold">
                        <?php
                        $coupon_parts = explode(" ", $game['coupon_number']);
                        foreach ($coupon_parts as $part) {
                          echo '<span class="number-box">' . htmlspecialchars($part) . '</span>';
                        }
                        ?>
                      </h6>
                    </div>
                  </div>
                </a>
              <?php endwhile; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!--==================== FOOTER ====================-->
  <footer class="text-white py-3">
    <div class="container-fluid">
      <!-- Top Row -->
      <div class="row align-items-center text-center mb-3">
        <div class="col-md-3">
          <small class="d-block mt-2"><span class="badge bg-danger">18+</span> You must be 18 or older to Play and Claim the prize</small>
        </div>
        <div class="col-md-6">
          <a href="#" class="btn">
            <img src="assets/image/logo/google-play.png" alt="Get it on Google Play" class="img-fluid" />
          </a>
        </div>
        <div class="col-md-3">
          <small>Gaming not allowed from the states where lottery is prohibited.</small>
        </div>
      </div>

      <!-- Divider -->
      <hr class="border-light my-3" />

      <!-- Bottom Row -->
      <div class="row">
        <div class="col text-center small">
          <p class="mb-0">
            Copyright © 2025 PlayWin | Designed By
            <a href="https://anuswarr.netlify.app/" target="_blank" class="text-white text-decoration-underline">Anuswar</a>
          </p>
        </div>
      </div>
    </div>
  </footer>
</body>

</html>