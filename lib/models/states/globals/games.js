const { RedGreenGame, DiscsGame, RouletteGame } = require("../../organized-game");



const organizedGames = {
    'red-green': {
        'min1': new RedGreenGame(1),
        'min3': new RedGreenGame(3),
        'min5': new RedGreenGame(5),
        'min10': new RedGreenGame(10)
    },
    'discs': {
        'min1': new DiscsGame(1),
        'min3': new DiscsGame(3),
        'min5': new DiscsGame(5),
        'min10': new DiscsGame(10)
    },
    'roulette': {
        'min1': new RouletteGame(1),
        'min3': new RouletteGame(3),
        'min5': new RouletteGame(5),
        'min10': new RouletteGame(10)
    }
};




module.exports={organizedGames}