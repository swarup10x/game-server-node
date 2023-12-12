const { clients } = require("./states/globals/clients");

var apiRoot='http://127.0.0.1:8000/api'
class OrganizedGame {
    constructor(gameName, gameMinutes) {

        this.gameName = gameName
        this.gameMinutes = gameMinutes; // 1, 3, 5, 10
        this.gameId = this.gameName + '_min' + this.gameMinutes;
        this.gameNumber = 0
        this.bets = [];
        this.result = null;
        this.elaspedSeconds = 0
        this.isWaitingResult = true
        this.previousGame = null
        this.startedAt = Date.now()
        this.lastBets = null
    }






    async addBets(bets) {
        //bet = {gameId,userId, amount,rewarded,betItem,winningMultiplier,orderedAt};
        this.bets = [...this.bets, ...bets]
        console.log('bets added', bets.length)
        let userId=bets[0].userId
        let totalAmount =bets[0].betAmount * bets.length
        await fetch(`${apiRoot}/subtractUserBalance?userId=${userId}&amount=${totalAmount}`)
        return this.bets
    }

    async removeBets(userId) {
        var userBets = this.bets.filter((e) => userId !== e.userId)
        this.bets = this.bets.filter((e) => userId !== e.userId)
        var totalAmount=userBets[0].betAmount*userBets.length
        await fetch(`${apiRoot}/refundUserBalance?userId=${userId}&amount=${totalAmount}`)
        return this.bets
    }

    updateGameResult() {
        this.result = this.generateResult()
        this.isWaitingResult = false
    }

    //override
    generateResult() {
        console.log('generateResult needs implementation')
    }


    //override
    getRewardMultiplier(bet) {
        console.log('getRewardMultiplier needs implementation')
    }

    rewardBets() {
        // Logic to reward bets based on the result
        //bet = {gameId,userId,gameNumber, betAmount,betChoise,rewarded,rewardedAmount, winningMultiplier};
        this.lastBets = this.bets
        this.lastBets.forEach((b) => {
            b.winningMultiplier = this.getRewardMultiplier(b)
            console.log('winning multiplier',b.betChoise,b.winningMultiplier)
            b.rewardedAmount = b.betAmount * b.winningMultiplier
            b.rewarded = true
        })

    }



    saveGameToDB() {
        // Logic to save game data to the database
        var apiUrl = 'http://127.0.0.1:8000/api/gamedata'
        //bet = {gameId,userId,gameNumber, betAmount,betChoise,rewarded,rewardedAmount, winningMultiplier};
        this.previousGame = {
            gameName: this.gameName,
            gameMinutes: this.gameMinutes,
            gameId: this.gameId,
            result: this.result,
            startedAt: this.startedAt.toString(),
            gameNumber: this.gameNumber
        }

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ game: this.previousGame, bets: this.bets }),
        }).then(response => response.json())
            .then(data => console.log('saved game result', this.gameId, data)) // Log the result of the fetch call
            .catch(error => console.error('saved game result Error:', this.gameId, error));
    }

    async streamEvent() {
        // console.log('previous game is null or not', this.previousGame)
        if (this.previousGame == null) {
            await this.startNewGame()
        }
        this.elaspedSeconds = this.elaspedSeconds + 1
        const gameUpdate = {
            eventId: this.gameId, data: {
                gameId: this.gameId,
                elaspedSeconds: this.elaspedSeconds,
                timeStamp: Date.now(),
                isWaitingResult: this.isWaitingResult,
                gameNumber: this.gameNumber,
                previousGame: { result: this.previousGame?.result, gameNumber: this.previousGame?.gameNumber }
            }
        };
        // console.log('streaming once ', gameUpdate)
        this.sendEvent(gameUpdate);
        // start new game
        if (this.isWaitingResult && this.elaspedSeconds == (60 * this.gameMinutes)) {
            this.finishGame();
        }

    }
    finishGame() {
        this.result = this.generateResult();
        this.isWaitingResult = false;
        this.rewardBets()
        this.saveGameToDB()
        this.startNewGame();
    }

    async loadPreviousGame() {
        if (!this.previousGame?.gameNumber) {
            var g = await fetch(`http://127.0.0.1:8000/api/lastgame?gameName=${this.gameName}&gameId=${this.gameId}`)
            var pgame
            try {
                pgame = await g.json()

            } catch (error) {
                var pgame = null
            }
            console.log('fetched previous game', pgame)
            if (!pgame?.lastGame) {
                this.previousGame = { result: this.generateResult(), gameNumber: 0 }
            } else {
                this.previousGame = pgame.lastGame
                console.log('fetched previous game:', this.previousGame)
            }
        }
    }
    async startNewGame() {
        await this.loadPreviousGame()
        this.gameNumber = this.previousGame.gameNumber + 1
        this.startedAt = Date.now()
        this.elaspedSeconds = 0
        this.isWaitingResult = true

        // this.result = this.generateResult()
        this.bets = []

    }

    sendEvent(data) {
        Object.keys(clients).forEach((clientId) => {
            let client = clients[clientId]
            console.log('this.id ===', this.gameId,'client.gameId ===', client.gameId)

            if (client.gameId === this.gameId) {
                client.response.write(`id: ${Date.now()}\ndata: ${JSON.stringify(data)}\n\n`);
            }
        });

    }
}

class RedGreenGame extends OrganizedGame {
    constructor(gameMinutes) {
        super('red-green', gameMinutes);
        // Specific properties or methods for RedGreenGame
    }

    generateResult() {
        const num = Math.floor(Math.random() * 10);
        return num.toString();
    }


    getRewardMultiplier(bet) {
        let multiplier = 0;
        const shouldReward = bet.betChoise === this.result;

        if (shouldReward) {
            multiplier = 9.75;
        } else {
            const onesCount = parseInt(this.result);
            if (bet.betChoise === 'red' && onesCount % 2 === 0) {
                multiplier = 1.95;
            }
            if (bet.betChoise === 'green' && onesCount % 2 !== 0) {
                multiplier = 1.95;
            }
            if (bet.betChoise === 'purple' && onesCount % 5 === 0) {
                multiplier = 1.95;
            }
        }
        return multiplier;
    }
}

class DiscsGame extends OrganizedGame {
    constructor(gameMinutes) {
        super('discs', gameMinutes);
    }

    generateResult() {
        const decimal = Math.floor(Math.random() * 16);
        const binaryStr = decimal.toString(2).padStart(4, '0');
        return binaryStr;
    }


    getRewardMultiplier(bet) {
        var multiplier = 0;
        const shouldReward = bet.betChoise === this.result;
        console.log('shouldReward ',bet.betChoise,'===',this.result,shouldReward)
        if (shouldReward) {
            multiplier = bet.betChoise === '1111' || bet.betChoise === '0000' ? 15.6 : 3.9;
            console.log('shouldReward multiplier',multiplier)
        } else {
            const onesCount = (this.result.match(/1/g) || []).length; // Count the number of '1' digits in the string
            console.log('ones-count',onesCount,bet.betChoise)
            if (bet.betChoise === 'even' && onesCount === 2) {
                multiplier = 1.95;
            }
            if (bet.betChoise === 'odd' && onesCount % 2 !== 0) {
                multiplier = 1.95;
            }
        }
        
        return multiplier;
    }


}

class RouletteGame extends OrganizedGame {
    constructor(gameMinutes) {
        super('roulette', gameMinutes);
    }

    generateResult() {
        const num = Math.floor(Math.random() * 36) + 1;
        return num.toString();
    }


    getRewardMultiplier(bet) {
        let multiplier = 0;
        const shouldReward = bet.betChoise === this.result;

        if (shouldReward) {
            multiplier = 35.1;
        } else {
            const resultCount = parseInt(game.result);
            if (bet.userBet === 'red' && resultCount % 2 !== 0) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === 'black' && resultCount % 2 === 0) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === 'even' && resultCount % 2 === 0) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === 'odd' && resultCount % 2 !== 0) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === 'small' && resultCount < 19) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === 'big' && resultCount > 18) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === '1-12' && resultCount < 13) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === '13-24' && resultCount > 12 && resultCount < 25) {
                shouldReward = true;
                multiplier = 1.95;
            }
            if (bet.userBet === '25-36' && resultCount > 24) {
                shouldReward = true;
                multiplier = 1.95;
            }
        }

        return multiplier;
    }
}
module.exports = { RedGreenGame, DiscsGame, RouletteGame }