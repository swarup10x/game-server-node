class CrashGame {
    constructor() {
        this.bets = []
        this.timer = null
        this.isGameOver = false
        this.isRewarded = false
        this.result = null
        this.isWaiting = true
    }
    addBet(bet) { }
    removeBet(betId) { }
    createTimer() { }
    calculateResult() { }
    rewardBets() { }
    saveGameToDB() { }
}

module.exports={CrashGame}