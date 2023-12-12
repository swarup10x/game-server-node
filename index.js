const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { organizedGames } = require('./lib/models/states/globals/games');
const { clients } = require('./lib/models/states/globals/clients');

const app = express();

app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));

const PORT = 3000;

const host = '0.0.0.0'; 


let facts = [];

app.listen(PORT,host, () => {
  console.log(`Facts Events service listening at http://localhost:${PORT}`);
});



setInterval(() => {
  console.log('interval called')
  Object.keys(organizedGames).forEach((game) => {
    organizedGames[game]['min1'].streamEvent()
  })

}, 1000 * 1);





app.get('/events', (req, res) => {
  const gameId = req.query.gameId;
  const userId = req.query.userId;
  const clientId= userId+'_'+gameId;

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



async function addBets(req, res, next) {
  const bets = req.body;
  let gameId=bets[0].gameId
  let gameName=gameId.split('_')[0]
  let timeId=gameId.split('_')[1]
  let game=organizedGames[gameName][timeId]

  console.log('adding bet',bets)
  game.addBets(bets)
  res.json({ success: 'bets added'})
}

async function getBets(req, res, next) {
  const { userId, gameId } = req.query;
  let gameName=gameId.split('_')[0]
  let timeId=gameId.split('_')[1]
  let game=organizedGames[gameName][timeId]

  if (!userId && !gameId) {
    // If neither userId nor gameId is provided in the query parameters
    return res.status(400).json({ error: 'Please provide userId or gameId' });
  }else {
    var userGameBets=game.bets.filter((b)=>b.userId===userId)
    return res.json(userGameBets);
  }
}
async function removeBets(req, res, next) {
  const { userId, gameId } = req.query;
  let gameName=gameId.split('_')[0]
  let timeId=gameId.split('_')[1]
  let game=organizedGames[gameName][timeId]

  if (!userId && !gameId) {
    // If neither userId nor gameId is provided in the query parameters
    return res.status(400).json({ error: 'Please provide userId or gameId' });
  }else {
    var res=await game.removeBets(userId)
    console.log('bets removed::',res)
    return res.json({message:'bets removed'});
  }
}

app.post('/bets', addBets);
app.get('/removeBets', removeBets);
app.get('/bets', getBets);

