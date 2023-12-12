<?php

namespace App\Games;

use Illuminate\Support\Facades\DB;
use App\Games\Helpers;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\Console\Helper\Helper;

class RedGreen3s
{
    public $id;
    public $name;

    public $startTime;
    public $endTime;
    public $duration;

    public $multiplier;

    public $rewardComplete;
    public $result;


    public function __construct($id = null, $name = null, $startTime = '0', $endTime = '0', $duration = null, $multiplier = null, $rewardComplete = false, $result = null)
    {
        $this->id = $id;
        $this->name = $name == null ? 'red-green3s_' . date('d-m-y-H:i') : $name;
        $this->startTime = intval($startTime);
        $this->endTime = intval($endTime);
        $this->duration = $duration;
        $this->multiplier = $multiplier;
        $this->rewardComplete = $rewardComplete;
        $this->result = $result;
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
    public function setResult($result)
    {
        $this->result = $result;
    }



    public function getId()
    {
        return $this->id;
    }
    public function isRewardComplete()
    {
        return $this->rewardComplete;
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
            if (!$this->isRewardComplete()) {
                self::rewardAllBets();
            }
            $lastStatus = ['gameId' => $this->getId(), 'running' => false, 'multiplier' => $this->multiplier, 'startTime' => $this->startTime, 'endTime' => $this->endTime, 'timestamp' => $current_time, 'result' => $this->result];
        } else {
            $lastStatus = ['gameId' => $this->getId(), 'running' => false, 'multiplier' => $this->multiplier, 'startTime' => $this->startTime, 'endTime' => $this->endTime, 'timestamp' => $current_time];
        }
        return $lastStatus;
    }





    public static function loadCurrentGame()
    {
        try {

            $row = DB::table('red_green3s_game')->orderBy('id', 'desc')->take(1)->get()->first();
            $game = Helpers::makeRedGreen3sFromRow($row);
            return $game;
        } catch (\Exception $e) {
            return null;
        }
    }


    public static function requestNewGame($startTime)
    {
        $newGame = self::createNewGame($startTime);
        Helpers::saveRedGreen3sGameToDB($newGame);
        info(' : saved (red-green3s) to db: ');
        return $newGame;
    }



    public static function createNewGame($startTime)
    {
        $game = new RedGreen3s();
        $game->setStartTime($startTime);
        $game->setDuration(5000); // can be 0-10000
        $game->setEndTime((int) round(microtime(true) * 1000) + $game->duration);
        $game->setMultiplier(0);
        $game->setResult(Helpers::generateRandomRedGreenResult());
        info(' : creating new game (red-green3s) : ' . $game->startTime);
        return $game;
    }

    // '/cashin?userId=1&amount=100'
    public static  function cashInBet($request)
    {
        $userId = $request->userId;
        $amount = floatval($request->amount);
        $userBet = floatval($request->userBet);

        $game = self::loadCurrentGame();
        if ($game->hasStarted() && !$game->hasEnded()) {
            return ['success' => false];
        }
        $bet = ['gameId' => $game->id + 1, 'userId' => $userId,  'bet-amount' => $amount, 'rewarded' => false, 'multiplier' => 0, 'userBet' => $userBet];
        DB::table('red_green3s_game_bets')->insert($bet);

        $user = User::findorFail(5);
        $user->balance = $user->balance - $amount;
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

        DB::table('red_green3s_game_bets')
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
        $bet = DB::table('red_green3s_game_bets')
            ->where('gameId', $gameId)
            ->where('userId', $userId)
            ->get()->first();
        return $bet;
    }
    public static function rewardAllBets()
    {
        $game = self::loadCurrentGame();
        if ($game == null) return ['success' => false];
        if ($game->isRewardComplete()) return ['success' => true, 'alresdy-reward_complete' => true];


        if (!$game->hasEnded()) {
            return ['success' => false];
        }
        $bets = DB::table('red_green3s_game_bets')
            ->where('gameId', $game->id)
            ->get();
        // if bets not empty
        if (count($bets) == 0) {
            return ['success' => false];
        }


        foreach ($bets as $bet) {
            $userId = $bet->userId;
            // Do something with the $userId value
            $multiplier = 0;
            $shouldReward = $bet->userBet == $game->result;
            if ($shouldReward) $multiplier = 9.75;
            //parseINt
            $onesCount = intval($game->result);
            if (!$shouldReward) {
                if ($bet->userBet == 'red' && $onesCount % 2 == 0) {
                    $shouldReward = true;
                    $multiplier = 1.95;
                }
                if ($bet->userBet == 'green' && $onesCount % 2 != 0) {
                    $shouldReward = true;
                    $multiplier = 1.95;
                }
                if ($bet->userBet == 'purple' && $onesCount % 5 == 0) {
                    $shouldReward = true;
                    $multiplier = 1.95;
                }
            }
            if ($shouldReward) {
                DB::table('red_green3s_game_bets')
                    ->where('userId', $userId)
                    ->where('gameId', $game->id)
                    ->where('rewarded', false)
                    ->update([
                        'rewarded' => true,
                        'multiplier' => $multiplier
                    ]);

                $amount = $bet->{'bet-amount'} * $multiplier;

                $user = User::findorFail(5);
                $user->balance = $user->balance + $amount;
                $user->save();

            
            }
        }

        DB::table('red_green3s_game')
            ->where('id', $game->id)
            ->update([
                'reward_complete' => true
            ]);

        return ['success' => true];
    }
}
