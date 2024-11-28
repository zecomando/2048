<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-3">
                <div class="score-container">
                    <div class="score-label">Score</div>
                    <div id="score">0</div>
                </div>
                <div class="high-score mt-2">
                    High Score: <?php echo $data['high_score']; ?>
                </div>
            </div>
            <div id="game-board" class="game-board mx-auto"></div>
            <div class="controls mt-3">
                <button id="new-game" class="btn btn-primary">New Game</button>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Top Scores</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach($data['top_scores'] as $score): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($score->username); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $score->score; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>
