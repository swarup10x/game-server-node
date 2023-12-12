const http = require('http');

// Game class with SSE functionality
class Game {
    constructor(gameName, duration) {
        this.gameName = gameName;
        this.duration = duration;
        this.isGameOver = false;
        this.eventStream = null;
        this.gameTimer = null;
    }

    startGameStream(response) {
        this.eventStream = response;
        this.sendEvent('Game started');
        this.gameTimer = setInterval(() => {
            this.sendEvent('Game update');
        }, this.duration * 1000); // Convert duration to milliseconds for setInterval
    }

    sendEvent(data) {
        if (this.eventStream) {
            this.eventStream.write(`data: ${JSON.stringify(data)}\n\n`);
        }
    }

    stopGameStream() {
        if (this.gameTimer) {
            clearInterval(this.gameTimer);
        }
        this.sendEvent('Game ended');
        this.eventStream.end();
    }
}

// Create instances of games
const games = [
    new Game('RedGreenGame', 60), // 1 min
    new Game('RedGreenGame', 180), // 3 min
    new Game('RedGreenGame', 300), // 5 min
    new Game('RedGreenGame', 600), // 10 min
    // Similarly for other games (DiscsGame, RouletteGame)
];

// HTTP server
const server = http.createServer((req, res) => {
    if (req.url === '/game-stream') {
        res.writeHead(200, {
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache',
            'Connection': 'keep-alive'
        });

        const urlParts = req.url.split('/');
        const gameId = parseInt(urlParts[urlParts.length - 1], 10);
        
        // Find and start the game stream
        const game = games[gameId];
        if (game) {
            game.startGameStream(res);
        }

        req.on('close', () => {
            // When the client closes the connection
            if (game) {
                game.stopGameStream();
            }
        });
    }
});

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
