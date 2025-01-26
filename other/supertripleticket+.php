<?php
include('../config/db.php');

// Default values for initial load
$view = isset($_GET['view']) ? filter_var($_GET['view'], FILTER_SANITIZE_STRING) : 'latest';
$currentDate = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&nbsp;--::&nbsp;&nbsp;Welcome to Playwin&nbsp;&nbsp;--&nbsp;&nbsp;Khelo India Khelo&nbsp;&nbsp;::--&nbsp;&nbsp;&nbsp;&nbsp;</title>
    <link rel="icon" href="../assets/image/logo/playwin.jpg" type="image/x-icon" />

    <!-- Stylesheets -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
</head>

<body>

    <!--==================== HEADER ====================-->
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
                        <img src="../assets/image/logo/playwin.jpg" alt="PlayWin Logo" class="img-fluid playwin-logo" />
                    </div>

                    <div
                        class="khelo-text-container d-flex align-items-center justify-content-center">
                        <img src="../assets/image/logo/slogan.gif" alt="Khelo India Khelo" class="img-fluid khelo-logo" />
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
    <main class="container-fluid mt-5">
        <h3 id="tableTitle" class="text-center mb-4 text-light">Draw Results of:</h3>

        <div class="text-center mb-4">
            <img src="../assets/image/game/superTripleTicket+.jpg" alt="superTripleTicket+" class="img-fluid mx-auto d-block mb-3 mt-3" />
        </div>

        <div class="d-flex justify-content-between mb-3 text-light">
            <a href="#" id="latestResults" data-view="latest" class="btn btn-primary" style="cursor: pointer;">LATEST RESULTS</a>
            <a href="#" id="previousResults" data-view="previous" class="btn btn-secondary" style="cursor: pointer;">PREVIOUS RESULTS</a>
        </div>

        <table id="gameDetailsTable" class="table table-bordered table-striped" cellspacing="0" cellpadding="10">
            <thead>
                <tr>
                    <th>Game Time</th>
                    <th>Coupon Numbers</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>

        <div id="pagination" class="text-center mt-3 text-light"></div>
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
                        <img src="../assets/image/logo/google-play.png" alt="Get it on Google Play" class="img-fluid" />
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

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.marquee/jquery.marquee.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>

    <script>
        $(document).ready(function() {
            const loadGameDetails = (view, page = 1) => {
                $.ajax({
                    url: 'supertripleticket+_data.php',
                    type: 'GET',
                    data: {
                        view,
                        page
                    },
                    success: function(data) {
                        const {
                            rows,
                            totalPages,
                            currentPage,
                            title
                        } = JSON.parse(data);
                        $('#gameDetailsTable tbody').html(rows);
                        $('#pagination').html(renderPagination(view, currentPage, totalPages));
                        $('#tableTitle').text(title);
                    }
                });
            };

            const renderPagination = (view, currentPage, totalPages) => {
                let paginationHtml = '<nav><ul class="pagination justify-content-center">';

                if (currentPage > 1) {
                    paginationHtml += `
                    <li class="page-item">
                        <a href="#" class="page-link" data-view="${view}" data-page="1">First</a>
                    </li>
                    <li class="page-item">
                        <a href="#" class="page-link" data-view="${view}" data-page="${currentPage - 1}">Previous</a>
                    </li>`;
                } else {
                    paginationHtml += `
                    <li class="page-item disabled">
                        <span class="page-link">First</span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>`;
                }

                // Dynamic range of page numbers
                const range = 2; // Number of pages to display around the current page
                const startPage = Math.max(1, currentPage - range);
                const endPage = Math.min(totalPages, currentPage + range);

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a href="#" class="page-link" data-view="${view}" data-page="${i}">${i}</a>
                    </li>`;
                }

                if (currentPage < totalPages) {
                    paginationHtml += `
                    <li class="page-item">
                        <a href="#" class="page-link" data-view="${view}" data-page="${currentPage + 1}">Next</a>
                    </li>
                    <li class="page-item">
                        <a href="#" class="page-link" data-view="${view}" data-page="${totalPages}">Last</a>
                    </li>`;
                } else {
                    paginationHtml += `
                    <li class="page-item disabled">
                        <span class="page-link">Next</span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Last</span>
                    </li>`;
                }

                paginationHtml += '</ul></nav>';
                return paginationHtml;
            };

            $(document).on('click', '.page-link, #latestResults, #previousResults', function(e) {
                e.preventDefault();
                const view = $(this).data('view');
                const page = $(this).data('page') || 1;
                loadGameDetails(view, page);
            });

            // Load initial data
            loadGameDetails('latest');
        });
    </script>
</body>

</html>