<?php

namespace App\Games;

use Illuminate\Support\Facades\DB;
use App\Games\Helpers;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\Console\Helper\Helper;

class Crash
{
    public $id;
    public $name;

    public $startTime;
    public $endTime;
    public $duration;

    public $multiplier;
    public $players;
    public $rewardComplete;


    public function __construct($id = null, $name = null, $startTime = '0', $endTime = '0', $duration = null, $multiplier = null, $rewardComplete = false)
    {
        $this->id = $id;
        $this->name = $name == null ? 'crash_' . date('d-m-y-H:i') : $name;
        $this->startTime = intval($startTime);
        $this->endTime = intval($endTime);
        $this->duration = $duration;
        $this->multiplier = $multiplier;
        $this->rewardComplete = $rewardComplete;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }



    public function getId()
    {
        return $this->id;
    }

    public function getPlayers()
    {
        return $this->players;
    }

    public function hasStarted()
    {
        $curTime = (int) round(microtime(true) * 1000);
        
        return $this->startTime < $curTime;
    }
    public function hasEnded()
    {
        $current_time = (int) round(microtime(true) * 1000);
        
        return $this->endTime < $current_time;
    }



    public function getStatus()
    {
        // return last $event based on current time in milliseconds 
        $current_time = (int) round(microtime(true) * 1000);
        $lastStatus = null;
        if ($this->hasEnded()) {
            $lastStatus = ['gameId' => $this->getId(), 'running' => false, 'multiplier' => $this->multiplier, 'startTime' => $this->startTime, 'endTime' => $this->endTime, 'timestamp' => $current_time];
        } else {
            $elaspedTime = $current_time - $this->startTime;
            $multiplier = $elaspedTime / 2000;
            if ($multiplier < 0) {
                $lastStatus = ['gameId' => $this->getId(), 'running' => false, 'multiplier' => null, 'startTime' => $this->startTime, 'willStartIn' => abs($multiplier), 'timestamp' => $current_time];
            } else {
                $lastStatus = ['gameId' => $this->getId(), 'running' => true, 'multiplier' => $multiplier, 'startTime' => $this->startTime, 'timestamp' => $current_time];
            }
        }
  

        return $lastStatus;
    }





    public static function loadCurrentGame()
    {
        try {
            $row = DB::table('crash_game')->orderBy('id', 'desc')->take(1)->get()->first();
            $crash = Helpers::makeCrashFromRow($row);
            return $crash;
        } catch (\Exception $e) {
            return null;
        }
    }


    public static function requestNewGame($startTime)
    {
        $newGame = self::createNewGame($startTime);
        Helpers::saveCrashGameToDB($newGame);
        info(' : saved (crash) to: ');
        return $newGame;
    }



    public static function createNewGame($startTime)
    {
        $game = new Crash();
        $game->setStartTime($startTime);
        $game->setDuration(Helpers::generateRandomDuration()); // can be 0-10000
        $game->setEndTime((int) round(microtime(true) * 1000) + $game->duration);
        $game->setMultiplier(number_format($game->duration / 2000,2));
        info(' : creating new game (crash) : ' . $game->startTime);
        return $game;
    }

    // '/cashin?userId=1&amount=100'
    public static  function cashInBet($request)
    {
        $userId = $request->userId;
        $amount = floatval($request->amount);

        $game = self::loadCurrentGame();
        if ($game->hasStarted() && !$game->hasEnded()) {
            return ['success' => false];
        }
        $bet = ['gameId' => $game->id + 1, 'userId' => $userId,  'bet-amount' => $amount, 'rewarded' => false, 'multiplier' => 0];
        DB::table('crash_game_bets')->insert($bet);

        $user = User::findorFail(5);
        $user->balance= $user->balance -$amount;
        $user->save();
        info('added bet successfully' . json_encode($bet));
        return ['success' => true];
    }

    // TODO: public function getUserBet($userId)

    public static function removeBet($request)
    {
        $userId = $request->userId;
        $game = self::loadCurrentGame();
        if (!$game->hasEnded()) {
            return ['success' => false];
        }

        DB::table('crash_game_bets')
            ->where('userId', $userId)
            ->where('gameId', $game->id + 1)
            ->delete();
        return ['success' => true];
    }

    public static function getUserBet($request)
    {
        $userId = $request->userId;
        $game = self::loadCurrentGame();
        $gameId = $game->id;
        if ($game->hasEnded()) {
            $gameId = $game->id + 1;
        }
        $bet = DB::table('crash_game_bets')
            ->where('userId', $userId)
            ->where('gameId', $gameId)
            ->get()->first();
        return $bet;
    }
    public static function cashOutBet($request)
    {
        $userId = $request->userId;
        $multiplier = floatval($request->multiplier);
        $gmae = self::loadCurrentGame();


        if (!$gmae->hasStarted() || $gmae->hasEnded()) {
            return ['success' => false];
        }

        $currentStatus = $gmae->getStatus();
        if ($multiplier > $currentStatus['multiplier']) {
            info('!!!!    scam alert x ' . $userId);
            return ['success' => false];
        }
        $bet = DB::table('crash_game_bets')
            ->where('userId', $userId)
            ->where('gameId', $currentStatus['gameId'])
            ->get()->first();
        DB::table('crash_game_bets')
            ->where('userId', $userId)
            ->where('gameId', $currentStatus['gameId'])
            ->where('rewarded', false)
            ->update([
                'rewarded' => true,
                'multiplier' => $multiplier
            ]);
        $amount = $bet->{'bet-amount'} * $multiplier;

        $user = User::findorFail(5);
        $user->balance= $user->balance +$amount;
        $user->save();
        return ['success' => true, 'rewardedAmount' => $amount];
    }
}
