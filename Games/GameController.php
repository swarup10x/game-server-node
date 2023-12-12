<?php

namespace App\Games;

use App\Games\Crash;
use App\Games\Discs;
use App\Games\RedGreen;
use App\Games\Roulette;
use App\Games\Discs3s;
use App\Games\RedGreen3s;
use App\Games\Roulette3s;
use App\Games\Discs5s;
use App\Games\RedGreen5s;
use App\Games\Roulette5s;
use App\Games\Helpers;
use App\User;
use Illuminate\Support\Facades\Schema;

class GameController
{
    static $crash;



    public static function schedule()
    {

        info(' : scheduling : ');
        self::initDatabase();
        self::rewardLastGames();
        $curTime = time() * 1000;
        info(' : Current time : ' . $curTime . ' : ');
        $inThreeSec = $curTime + 3499;
        $crash = Crash::requestNewGame($inThreeSec);
        info(' : Crash scheduled : ' . $crash->getId() . ' : ' . $crash->name);
        $discs = Discs::requestNewGame($inThreeSec);
        info(' : Discs scheduled : ' . $discs->getId() . ' : ' . $discs->name . ' : ' . $discs->result);
        $redGreen = RedGreen::requestNewGame($inThreeSec);
        info(' : Red-Green scheduled : ' . $redGreen->getId() . ' : ' . $redGreen->name . ' : ' . $redGreen->result);
        $roulette = Roulette::requestNewGame($inThreeSec);
        info(' : Roulette scheduled : ' . $roulette->getId() . ' : ' . $roulette->name . ' : ' . $roulette->result);
        // $crash3s = Crash::requestNewGame($inThreeSec,'crash3s');
        // info(' : Crash scheduled : ' . $crash3s->getId() . ' : ' . $crash3s->name);

        $game3s = Discs3s::loadCurrentGame();
        $lastStartTime3s =$game3s==null?0: intval($game3s->startTime);
        if ($curTime > $lastStartTime3s + (60000 * 3) - 10000) {
            $discs3s = Discs3s::requestNewGame($inThreeSec);
            info(' : Discs3s scheduled : ' . $discs3s->getId() . ' : ' . $discs3s->name . ' : ' . $discs3s->result);
            $redGreen3s = RedGreen3s::requestNewGame($inThreeSec);
            info(' : Red-Green3s scheduled : ' . $redGreen3s->getId() . ' : ' . $redGreen3s->name . ' : ' . $redGreen3s->result);
            $roulette3s = Roulette3s::requestNewGame($inThreeSec);
            info(' : Roulette3s scheduled : ' . $roulette3s->getId() . ' : ' . $roulette3s->name . ' : ' . $roulette3s->result);
        }


        $game5s = Discs5s::loadCurrentGame();
        
        $lastStartTime5s =$game5s==null?0: intval($game5s->startTime);
        if ($curTime > $lastStartTime5s + (60000 * 5) - 10000 ) {
            $discs5s = Discs5s::requestNewGame($inThreeSec);
            info(' : Discs5s scheduled : ' . $discs5s->getId() . ' : ' . $discs5s->name . ' : ' . $discs5s->result);
            $redGreen5s = RedGreen5s::requestNewGame($inThreeSec);
            info(' : Red-Green5s scheduled : ' . $redGreen5s->getId() . ' : ' . $redGreen5s->name . ' : ' . $redGreen5s->result);
            $roulette5s = Roulette5s::requestNewGame($inThreeSec);
            info(' : Roulette5s scheduled : ' . $roulette5s->getId() . ' : ' . $roulette5s->name . ' : ' . $roulette5s->result);
        }
    }

    public static function initDatabase()
    {
        // Schema::dropIfExists('crash_game');
        // Schema::dropIfExists('discs_game');
        // Schema::dropIfExists('red_green_game');
        // Schema::dropIfExists('roulette_game');
        Helpers::initCrashGameTables();
        Helpers::initDiscsGameTables();
        Helpers::initRedGreenGameTables();
        Helpers::initRouletteGameTables();
        info(' : Database initialized : ');
    }
    public static function rewardLastGames()
    {
        Discs::rewardAllBets();
        RedGreen::rewardAllBets();
        Roulette::rewardAllBets();
        Discs3s::rewardAllBets();
        RedGreen3s::rewardAllBets();
        Roulette3s::rewardAllBets();
        Discs3s::rewardAllBets();
        RedGreen3s::rewardAllBets();
        Roulette3s::rewardAllBets();
        info(' :rewarded users : ');
    }



    public static function getCrashStatus()
    {
        $crash = Crash::loadCurrentGame();
        if ($crash == null) return ["waiting" => true];
        return $crash->getStatus();
    }
    public static function getDiscsStatus()
    {
        $game = Discs::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRedGreenStatus()
    {
        $game = RedGreen::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRouletteStatus()
    {
        $game = Roulette::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }


    public static function getDiscs3sStatus()
    {
        $game = Discs3s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRedGreen3sStatus()
    {
        $game = RedGreen3s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRoulette3sStatus()
    {
        $game = Roulette3s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }

    public static function getDiscs5sStatus()
    {
        $game = Discs5s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRedGreen5sStatus()
    {
        $game = RedGreen5s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    public static function getRoulette5sStatus()
    {
        $game = Roulette5s::loadCurrentGame();
        if ($game == null) return ["waiting" => true];
        return $game->getStatus();
    }
    
    //addCrashBet
    //removeCrashBet
}
