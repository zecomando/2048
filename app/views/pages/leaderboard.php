<?php require APPROOT . '/views/inc/header.php'; ?>

<div class="container">
    <h1 class="mb-4">Leaderboard</h1>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player</th>
                        <th>Score</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['scores'] as $index => $score): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($score->username); ?></td>
                            <td><?php echo $score->score; ?></td>
                            <td><?php echo date('M j, Y', strtotime($score->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>
