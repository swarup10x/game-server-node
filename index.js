const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');

const fs = require('fs');
const { organizedGames, crashGame } = require('./lib/models/states/globals/games');
const { clients } = require('./lib/models/states/globals/clients');

const app = express();

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

const PORT = 3000;

// const host = 'localhost';
const host = '0.0.0.0'; 


let facts = [];

app.listen(PORT, host, () => {
  console.log(`Facts Events service listening at http://${host}:${PORT}`);
});



// setInterval(() => {
//   console.log('interval called')
//   Object.keys(organizedGames).forEach((game) => {
//     organizedGames[game]['min1'].streamEvent()
//     organizedGames[game]['min3'].streamEvent()
//     organizedGames[game]['min5'].streamEvent()
//     organizedGames[game]['min10'].streamEvent()
//   })

// }, 1000 * 1);

////for crash
let intervalId;

function runIntervals() {
  let count = 0;

  // Run the 1000ms interval for 10 seconds
  const firstInterval = setInterval(() => {
    if (count < 10) {

      count++;
      console.log(`1000ms interval: ${count}s`);
      crashGame.streamWaitingEvent()
    } else {
      clearInterval(firstInterval);
      crashGame.startNewGame()

      // Start the 100ms interval for a random duration between 5000 and 15000 milliseconds
      setTimeout(() => {
        clearInterval(intervalId);
        intervalId = setInterval(() => {
          crashGame.streamOngoingEvent()
          // console.log("100ms interval");
        }, 100);

        setTimeout(() => {
          clearInterval(intervalId);
          crashGame.finishGame()
          runIntervals(); // Restart the process
        }, crashGame.maxTime); // Stop the 100ms interval after the random duration
      }, 1000); // Wait 1 second before starting the 100ms interval
    }
  }, 1000);

  // Start the initial 1000ms interval
  intervalId = firstInterval;
}

// Start the intervals
runIntervals();

////for crash




app.get('/events', (req, res) => {
  const gameId = req.query.gameId;
  const userId = req.query.userId;
  const clientId = userId + '_' + gameId;

  const headers = {
    'Content-Type': 'text/event-stream',
    'Connection': 'keep-alive',
    'Cache-Control': 'no-cache'
  };
  res.writeHead(200, headers);

  const newClient = {
    userId: userId,
    response: res,
    gameId: gameId
  };

  clients[clientId] = newClient;

  req.on('close', () => {
    console.log(`${clientId} Connection closed`);
    delete clients[clientId]
  });
});



async function addCrashBet(req, res, next) {
  const bet = req.body;
  let gameId = bet.gameId
  crashGame.addBet(bet)
  console.log('adding bet', bet)
  res.json({ success: 'bet added' })

}
async function cashoutCrashBet(req, res, next) {
  const userId = req.query.userId;
  console.log('crash gamebets before',crashGame.bets)
  var result=crashGame.cashoutBet(userId)
  console.log('caching out bet for user', userId)
  res.json({ success:result  })
}
async function addBets(req, res, next) {
  const bets = req.body;
  let gameId = bets[0].gameId
  let gameName = gameId.split('_')[0]
  let timeId = gameId.split('_')[1]
  let game = organizedGames[gameName][timeId]

  console.log('adding bet', bets)
  game.addBets(bets)
  res.json({ success: 'bets added' })
  
}

async function getCrashBet(req, res, next) {
  const { userId } = req.query;


  if (!userId) {
    // If neither userId nor gameId is provided in the query parameters
    return res.status(400).json({ error: 'Please provide userId or gameId' });
  } else {
    var userGameBet = crashGame.bets.find((b) => b.userId === userId)
    return res.json(userGameBet);
  }
}
async function getBets(req, res, next) {
  const { userId, gameId } = req.query;
  let gameName = gameId.split('_')[0]
  let timeId = gameId.split('_')[1]
  let game = organizedGames[gameName][timeId]

  if (!userId && !gameId) {
    // If neither userId nor gameId is provided in the query parameters
    return res.status(400).json({ error: 'Please provide userId or gameId' });
  } else {
    var userGameBets = game.bets.filter((b) => b.userId === userId)
    return res.json(userGameBets);
  }
}
async function removeBets(req, res, next) {
  const { userId, gameId } = req.query;
  let gameName = gameId.split('_')[0]
  let timeId = gameId.split('_')[1]
  let game = organizedGames[gameName][timeId]

  if (!userId && !gameId) {
    // If neither userId nor gameId is provided in the query parameters
    return res.status(400).json({ error: 'Please provide userId or gameId' });
  } else {
    var res = await game.removeBets(userId)
    console.log('bets removed::', res)
    return res.json({ message: 'bets removed' });
  }
}

app.post('/bets', addBets);
app.get('/removeBets', removeBets);
app.get('/bets', getBets);
app.get('/bet/crash', getCrashBet);
app.post('/bet/crash', addCrashBet);
app.get('/cashout/crash', cashoutCrashBet);

