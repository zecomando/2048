<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2048 Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">2048</a>
            <div class="navbar-text text-white">
                Welcome, <?php echo htmlspecialchars($user['username']); ?> | 
                Points: <span id="userPoints"><?php echo $user['points']; ?></span>
            </div>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="score-container mb-3">
                    <div class="score-label">Score</div>
                    <div id="score">0</div>
                </div>
                <div id="game-board" class="game-board mx-auto"></div>
                <div class="controls mt-3">
                    <button id="new-game" class="btn btn-primary">New Game</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="game.js"></script>
    <script>
        // Add score update functionality
        function updateScore(score) {
            fetch('update_score.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'score=' + score
            })
            .then(response => response.json())
            .then(data => {
                if (data.points) {
                    document.getElementById('userPoints').textContent = data.points;
                }
            });
        }
    </script>
</body>
</html>
