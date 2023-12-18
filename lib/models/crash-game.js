const { apiRoot } = require("../constants");
const { clients } = require("./states/globals/clients");


class CrashGame {
    constructor() {

        this.gameName = 'crash'
        this.gameId = this.gameName.toLowerCase()
        this.gameNumber = 0
        this.bets = [];
        this.result = null;
        this.elaspedSeconds = 0
        this.elaspedMilliSeconds = 0
        this.isWaitingResult = true
        this.previousGame = null
        this.startedAt = Date.now()
        this.stoppedAt = 0
        this.lastBets = null
        this.currentMultiplier = 0
    }






    async addBet(bet) {
        //bet = {gameId,userId,gameNumber, betAmount,rewarded,rewardedAmount, winningMultiplier,cashedOut,userName};
        this.bets = [...this.bets, bet]
        let userId = bet.userId
        let totalAmount = bet.betAmount 
        await fetch(`${apiRoot}/subtractUserBalance?userId=${userId}&amount=${totalAmount}`)
        console.log('a crash bet added')
        return this.bets
    }
    async cashoutBet(userId) {
        //bet = {gameId,userId,gameNumber, betAmount,rewarded,rewardedAmount, winningMultiplier,cashedOut,userName};
        var bet=this.lastBets.find((b)=>b.userId===userId)
        if(!bet) return false
        console.log('cashing out on',bet,userId,this.lastBets)
        bet.cashedOut=true
        bet.rewarded=true
        let totalAmount = bet.betAmount
        bet.winningMultiplier=this.currentMultiplier
        bet.rewardedAmount=parseFloat(totalAmount* bet.winningMultiplier)
        fetch(`${apiRoot}/refundUserBalance?userId=${userId}&amount=${totalAmount}`)
        console.log('a crash bet cashedout')
        return true
    }



    //override
    generateMaxTime() {
        return Math.floor(Math.random() * (15000 - 5000 + 1)) + 5000;
    }

    //override
    getRewardMultiplier(bet) {
        console.log('getRewardMultiplier needs implementation')
    }

    rewardBets() {
        // Logic to reward bets based on the result
        //bet = {gameId,userId,gameNumber, betAmount,rewarded,rewardedAmount, winningMultiplier};

        this.lastBets.forEach((b) => {
            if(!b.rewarded){
                b.winningMultiplier = 0
                console.log('winning multiplier', b.betChoise, b.winningMultiplier)
                b.rewardedAmount = 0
                b.rewarded = true
            }
        })

    }



    saveGameToDB() {
        // Logic to save game data to the database
        var apiUrl = `${apiRoot}/gamedata`
        //bet = {gameId,userId,gameNumber, betAmount,betChoise,rewarded,rewardedAmount, winningMultiplier};
        this.previousGame = {
            gameName: this.gameName,
            gameId: this.gameId,
            result: this.currentMultiplier,
            startedAt: this.startedAt.toString(),
            stoppedAt: this.stoppedAt.toString(),
            gameNumber: this.gameNumber,

        }
        console.log('crash saveGameToDB', this.previousGame)
        
        console.log('crash bets', this.lastBets)


        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ game: this.previousGame, bets: this.lastBets }),
        }).then(response => response.json())
            .then(data => console.log('saved game result', this.gameId, data)) // Log the result of the fetch call
            .catch(error => console.error('saved game result Error:', this.gameId, error));
    }

    async streamWaitingEvent() {
        // console.log('previous game is null or not', this.previousGame)
        this.elaspedSeconds = this.elaspedSeconds + 1
        await this.loadPreviousGame()
        const gameUpdate = {
            eventId: this.gameId, data: {
                gameId: this.gameId,
                status:'waiting',
                elaspedSeconds: this.elaspedSeconds,
                timeStamp: Date.now(),
          
                gameNumber: this.gameNumber+1,
                previousGame: { result: this.previousGame.result, gameNumber: this.previousGame.gameNumber },
                bets:this.bets
            }
        };
        // console.log('streaming once ', gameUpdate)
        this.sendEvent(gameUpdate);


    }
    async streamOngoingEvent() {
        // this.elaspedSeconds = 0
        if(this.currentMultiplier===0){
            this.currentMultiplier=1
        }else{
            this.currentMultiplier =parseFloat(parseFloat(this.currentMultiplier + 0.03).toFixed(2)) 
        }
        this.elaspedMilliSeconds=this.elaspedMilliSeconds+100
        const gameUpdate = {
            eventId: this.gameId, data: {
                gameId: this.gameId,
                status:'ongoing',
                timeStamp: Date.now(),
                isWaitingResult: this.isWaitingResult,
                gameNumber: this.gameNumber,
                currentMultiplier: this.currentMultiplier,
                crashed:false,
                bets:this.lastBets
            }
        };
        // console.log('streaming once ', gameUpdate)
        this.sendEvent(gameUpdate);
        // start new game


    }
    finishGame() {
        this.result = this.currentMultiplier;
        this.isWaitingResult = false;
        this.stoppedAt=Date.now()
        this.rewardBets()
        this.saveGameToDB()
        const gameUpdate = {
            eventId: this.gameId, data: {
                gameId: this.gameId,
                status:'ongoing',
                timeStamp: Date.now(),
                isWaitingResult: this.isWaitingResult,
                gameNumber: this.gameNumber,
                currentMultiplier: this.currentMultiplier,
                crashed:true
            }
        };
        // console.log('streaming once ', gameUpdate)
        this.sendEvent(gameUpdate);
    }

    async loadPreviousGame() {
        if (!this.previousGame) {
            var g = await fetch(`${apiRoot}/lastgame?gameName=${this.gameName}&gameId=${this.gameId}`)
            var pgame
            try {
                pgame = await g.json()

            } catch (error) {
                var pgame = null
            }
            console.log('fetched previous game', pgame)
            if (!pgame?.lastGame) {
                this.previousGame = { result: 2.0, gameNumber: 0 }
            } else {
                this.previousGame = pgame.lastGame
                console.log('fetched previous game:', this.previousGame)
            }
        }
    }
    startNewGame() {

        this.gameNumber = parseInt(this.previousGame.gameNumber) + 1
        this.startedAt = Date.now()
        this.elaspedSeconds = 0
        this.currentMultiplier = 0
        this.isWaitingResult = true
        this.result=null
        this.maxTime = this.generateMaxTime()
        this.lastBets = this.bets
        this.bets=[]
    }

    sendEvent(data) {
        Object.keys(clients).forEach((clientId) => {
            let client = clients[clientId]
            // console.log('this.id ===', this.gameId, 'client.gameId ===', client.gameId)

            if (client.gameId === this.gameId) {
                client.response.write(`id: ${Date.now()}\ndata: ${JSON.stringify(data)}\n\n`);
            }
        });

    }
}

module.exports = {
    CrashGame
}