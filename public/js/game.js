class Game2048 {
    constructor(boardSize = 4) {
        this.boardSize = boardSize;
        this.board = Array(boardSize).fill().map(() => Array(boardSize).fill(0));
        this.score = 0;
        this.moves = [];
        this.moveCount = 0;
        this.startTime = Date.now();
        this.gameBoard = document.getElementById('game-board');
        this.scoreDisplay = document.getElementById('score');
        this.touchStartX = null;
        this.touchStartY = null;
        this.gameMode = 'classic'; // classic or challenge
        this.soundEnabled = localStorage.getItem('soundEnabled') === 'true';
        
        this.loadSounds();
        this.initializeBoard();
        this.setupEventListeners();
        this.loadGameState();
    }

    loadSounds() {
        this.moveSound = new Audio('/sounds/move.mp3');
        this.mergeSound = new Audio('/sounds/merge.mp3');
        this.gameOverSound = new Audio('/sounds/gameover.mp3');
        this.winSound = new Audio('/sounds/win.mp3');
    }

    playSound(sound) {
        if (this.soundEnabled && sound) {
            sound.currentTime = 0;
            sound.play().catch(() => {});
        }
    }

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        localStorage.setItem('soundEnabled', this.soundEnabled);
    }

    initializeBoard(initialBoard = null) {
        this.gameBoard.innerHTML = '';
        this.gameBoard.style.gridTemplateColumns = `repeat(${this.boardSize}, 1fr)`;
        
        if (initialBoard) {
            this.board = JSON.parse(JSON.stringify(initialBoard));
        } else {
            this.board = Array(this.boardSize).fill().map(() => Array(this.boardSize).fill(0));
            this.addRandomTile();
            this.addRandomTile();
        }
        
        for (let i = 0; i < this.boardSize; i++) {
            for (let j = 0; j < this.boardSize; j++) {
                const cell = document.createElement('div');
                cell.classList.add('tile');
                cell.dataset.row = i;
                cell.dataset.col = j;
                this.gameBoard.appendChild(cell);
            }
        }
        this.updateBoard();
    }

    setupEventListeners() {
        document.addEventListener('keydown', (e) => {
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
                this.handleMove(e.key);
            }
        });

        // Touch events for mobile
        this.gameBoard.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        });

        this.gameBoard.addEventListener('touchmove', (e) => {
            e.preventDefault();
        });

        this.gameBoard.addEventListener('touchend', (e) => {
            if (!this.touchStartX || !this.touchStartY) return;

            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;

            const deltaX = touchEndX - this.touchStartX;
            const deltaY = touchEndY - this.touchStartY;

            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                if (deltaX > 0) {
                    this.handleMove('ArrowRight');
                } else {
                    this.handleMove('ArrowLeft');
                }
            } else {
                if (deltaY > 0) {
                    this.handleMove('ArrowDown');
                } else {
                    this.handleMove('ArrowUp');
                }
            }

            this.touchStartX = null;
            this.touchStartY = null;
        });

        document.getElementById('new-game').addEventListener('click', () => {
            if (confirm('Are you sure you want to start a new game?')) {
                this.resetGame();
            }
        });

        document.getElementById('undo-move').addEventListener('click', () => {
            this.undoMove();
        });

        document.getElementById('save-game').addEventListener('click', () => {
            this.saveGameState();
        });

        document.getElementById('toggle-sound').addEventListener('click', () => {
            this.toggleSound();
        });
    }

    handleMove(direction) {
        // Save current state for undo
        this.moves.push({
            board: JSON.parse(JSON.stringify(this.board)),
            score: this.score
        });
        
        let moved = false;
        const rotatedBoard = this.rotateBoard(direction);

        for (let i = 0; i < this.boardSize; i++) {
            const row = rotatedBoard[i];
            const newRow = this.slideTiles(row);
            rotatedBoard[i] = newRow;
            
            if (JSON.stringify(row) !== JSON.stringify(newRow)) {
                moved = true;
            }
        }

        if (moved) {
            this.moveCount++;
            this.board = this.unrotateBoard(rotatedBoard, direction);
            this.addRandomTile();
            this.updateBoard();
            this.checkGameStatus();
            this.playSound(this.moveSound);
            
            // Update score in backend
            fetch('users/updateScore', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    score: this.score,
                    moves: this.moveCount,
                    time: Math.floor((Date.now() - this.startTime) / 1000)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.points) {
                    document.getElementById('userPoints').textContent = data.points;
                }
                if (data.achievements) {
                    this.showAchievements(data.achievements);
                }
            });
        }
    }

    slideTiles(row) {
        row = row.filter(val => val !== 0);
        let mergedInMove = false;
        
        for (let i = 0; i < row.length - 1; i++) {
            if (row[i] === row[i+1]) {
                row[i] *= 2;
                this.score += row[i];
                row.splice(i+1, 1);
                mergedInMove = true;
            }
        }
        
        if (mergedInMove) {
            this.playSound(this.mergeSound);
        }
        
        while (row.length < this.boardSize) {
            row.push(0);
        }
        
        return row;
    }

    rotateBoard(direction) {
        let rotated = JSON.parse(JSON.stringify(this.board));
        
        switch(direction) {
            case 'ArrowLeft':
                return rotated;
            case 'ArrowRight':
                return rotated.map(row => row.reverse());
            case 'ArrowUp':
                return rotated[0].map((val, index) => rotated.map(row => row[index]));
            case 'ArrowDown':
                const upRotated = rotated[0].map((val, index) => rotated.map(row => row[index]));
                return upRotated.map(row => row.reverse());
        }
    }

    unrotateBoard(rotatedBoard, direction) {
        switch(direction) {
            case 'ArrowLeft':
                return rotatedBoard;
            case 'ArrowRight':
                return rotatedBoard.map(row => row.reverse());
            case 'ArrowUp':
                return rotatedBoard[0].map((val, index) => rotatedBoard.map(row => row[index]));
            case 'ArrowDown':
                const upRotated = rotatedBoard[0].map((val, index) => rotatedBoard.map(row => row[index]));
                return upRotated.map(row => row.reverse());
        }
    }

    updateBoard() {
        this.scoreDisplay.textContent = this.score;
        
        const tiles = this.gameBoard.querySelectorAll('.tile');
        tiles.forEach((tile, index) => {
            const row = Math.floor(index / this.boardSize);
            const col = index % this.boardSize;
            const value = this.board[row][col];
            const previousValue = parseInt(tile.dataset.value) || 0;
            
            tile.textContent = value || '';
            tile.dataset.value = value;
            tile.className = 'tile';
            
            if (value) {
                tile.classList.add(`tile-${value}`);
                if (value !== previousValue) {
                    tile.classList.add('tile-new');
                    setTimeout(() => tile.classList.remove('tile-new'), 300);
                }
            }
        });
    }

    checkGameStatus() {
        const gameData = {
            won: false,
            score: this.score,
            moves_count: this.moveCount,
            time_spent: Math.floor((Date.now() - this.startTime) / 1000)
        };

        if (this.board.some(row => row.includes(2048))) {
            this.playSound(this.winSound);
            gameData.won = true;
            alert('Congratulations! You won!');
        } else if (!this.canMove()) {
            this.playSound(this.gameOverSound);
            alert('Game Over! No more moves possible.');
        }

        if (gameData.won || !this.canMove()) {
            fetch('users/updateGameStatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(gameData)
            });
        }
    }

    canMove() {
        if (this.board.some(row => row.includes(0))) return true;

        for (let i = 0; i < this.boardSize; i++) {
            for (let j = 0; j < this.boardSize; j++) {
                if (
                    (i > 0 && this.board[i][j] === this.board[i-1][j]) ||
                    (i < this.boardSize - 1 && this.board[i][j] === this.board[i+1][j]) ||
                    (j > 0 && this.board[i][j] === this.board[i][j-1]) ||
                    (j < this.boardSize - 1 && this.board[i][j] === this.board[i][j+1])
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    undoMove() {
        if (this.moves.length > 0) {
            const lastMove = this.moves.pop();
            this.board = lastMove.board;
            this.score = lastMove.score;
            this.updateBoard();
        }
    }

    saveGameState() {
        fetch('savedgames/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                board: this.board,
                score: this.score,
                boardSize: this.boardSize
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Game saved successfully!');
            }
        });
    }

    loadGameState() {
        fetch('savedgames/load')
        .then(response => response.json())
        .then(data => {
            if (data.board) {
                this.initializeBoard(data.board);
                this.score = data.score;
                this.boardSize = data.boardSize;
                this.updateBoard();
            }
        });
    }

    showAchievements(achievements) {
        achievements.forEach(achievement => {
            const toast = document.createElement('div');
            toast.classList.add('achievement-toast');
            toast.innerHTML = `
                <h4>Achievement Unlocked!</h4>
                <p>${achievement.name}</p>
                <p>+${achievement.points_reward} points</p>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        });
    }

    resetGame() {
        this.board = Array(this.boardSize).fill().map(() => Array(this.boardSize).fill(0));
        this.score = 0;
        this.moves = [];
        this.moveCount = 0;
        this.startTime = Date.now();
        this.initializeBoard();
    }

    addRandomTile() {
        const emptyCells = [];
        for (let i = 0; i < this.boardSize; i++) {
            for (let j = 0; j < this.boardSize; j++) {
                if (this.board[i][j] === 0) {
                    emptyCells.push({row: i, col: j});
                }
            }
        }

        if (emptyCells.length > 0) {
            const {row, col} = emptyCells[Math.floor(Math.random() * emptyCells.length)];
            this.board[row][col] = Math.random() < 0.9 ? 2 : 4;
        }
    }
}

// Initialize the game when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new Game2048();
});
