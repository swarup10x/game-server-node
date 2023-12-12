<?php

namespace App\Games;

use Illuminate\Support\Facades\Schema;
use App\Games\Crash;
use App\Games\Discs;
use App\Games\RedGreen;
use Illuminate\Support\Facades\DB;

class Helpers
{


    public static function generateRandomDiscsResult()
    {
        $decimal = mt_rand(0, 15);
        $binaryStr = str_pad(decbin($decimal), 4, '0', STR_PAD_LEFT);
        return $binaryStr;
    }
    public static function generateRandomRedGreenResult()
    {
        $num = mt_rand(0, 9);
        
        return strval($num);
    }
    public static function generateRandomRouletteResult(){
        $num = mt_rand(1, 36);
        
        return strval($num);
    }
    public static function generateRandomDuration()
    {
        // return 40000; //TODO: remove this(test only)
        $rand = mt_rand(1, 10000) / 10000; // Generate a random number between 0 and 1

        if ($rand <= 0.03) {
            // 3% chance of being 0
            return 0;
        } elseif ($rand <= 0.51) {
            // 48% chance of being 1-200
            return mt_rand(1, 4000);
        } else {
            // 4% chance of being 200-999
            return mt_rand(4000, 20000); //these are supposedly milliseconds
        }
    }
    public static function initCrashGameTables()
    {
        if (!Schema::hasTable('crash_game')) {
            Schema::create('crash_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
            });
        }
        if (!Schema::hasTable('crash_game_bets')) {
            Schema::create('crash_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
            });
        }
    }
    public static function initDiscsGameTables()
    {
 
        if (!Schema::hasTable('discs_game')) {
            Schema::create('discs_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('discs_game_bets')) {
            Schema::create('discs_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('discs3s_game')) {
            Schema::create('discs3s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('discs3s_game_bets')) {
            Schema::create('discs3s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('discs5s_game')) {
            Schema::create('discs5s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('discs5s_game_bets')) {
            Schema::create('discs5s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
    }
    public static function initRedGreenGameTables()
    {
 
        if (!Schema::hasTable('red_green_game')) {
            Schema::create('red_green_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('red_green_game_bets')) {
            Schema::create('red_green_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('red_green3s_game')) {
            Schema::create('red_green3s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('red_green3s_game_bets')) {
            Schema::create('red_green3s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('red_green5s_game')) {
            Schema::create('red_green5s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('red_green5s_game_bets')) {
            Schema::create('red_green5s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
    }

    public static function initRouletteGameTables()
    {
 
        if (!Schema::hasTable('roulette_game')) {
            Schema::create('roulette_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('roulette_game_bets')) {
            Schema::create('roulette_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('roulette3s_game')) {
            Schema::create('roulette3s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('roulette3s_game_bets')) {
            Schema::create('roulette3s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
        if (!Schema::hasTable('roulette5s_game')) {
            Schema::create('roulette5s_game', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('start_time');
                $table->string('end_time');
                $table->integer('duration');
                $table->float('multiplier');
                $table->boolean('reward_complete');
                $table->string('result');
            });
        }
        if (!Schema::hasTable('roulette5s_game_bets')) {
            Schema::create('roulette5s_game_bets', function ($table) {
                $table->increments('id');
                $table->string('gameId');
                $table->string('userId');
                $table->float('bet-amount');
                $table->float('multiplier');
                $table->boolean('rewarded');
                $table->string('userBet');
            });
        }
    }

    public static function saveCrashGameToDB($game)
    {

        DB::table('crash_game')->insert([
            'name' => $game->name,
            'start_time' =>strval( $game->startTime),
            'end_time' =>strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete
        ]);
    }
    public static function saveDiscsGameToDB($game)
    {

        DB::table('discs_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveDiscs3sGameToDB($game)
    {

        DB::table('discs3s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveDiscs5sGameToDB($game)
    {

        DB::table('discs5s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRedGreenGameToDB($game)
    {

        DB::table('red_green_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRedGreen5sGameToDB($game)
    {

        DB::table('red_green5s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRedGreen3sGameToDB($game)
    {

        DB::table('red_green3s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRouletteGameToDB($game)
    {

        DB::table('roulette_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRoulette3sGameToDB($game)
    {

        DB::table('roulette3s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }
    public static function saveRoulette5sGameToDB($game)
    {

        DB::table('roulette5s_game')->insert([
            'name' => $game->name,
            'start_time' => strval( $game->startTime),
            'end_time' => strval( $game->endTime),
            'duration' => $game->duration,
            'multiplier' => $game->multiplier,
            'reward_complete' => $game->rewardComplete,
            'result' => $game->result,
        ]);
    }

    public  static function makeCrashFromRow($row)
    {
        $crash = new Crash(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete
        );
     
        return $crash;
    }
    public  static function makeDiscsFromRow($row)
    {
        $discs = new Discs(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $discs;
    }
    public  static function makeDiscs3sFromRow($row)
    {
        $discs = new Discs3s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $discs;
    }
    public  static function makeDiscs5sFromRow($row)
    {
        $discs = new Discs5s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $discs;
    }
    public  static function makeRedGreenFromRow($row)
    {
        $game = new RedGreen(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
    public  static function makeRedGreen3sFromRow($row)
    {
        $game = new RedGreen3s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
    public  static function makeRedGreen5sFromRow($row)
    {
        $game = new RedGreen5s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
    public  static function makeRouletteFromRow($row)
    {
        $game = new Roulette(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
    public  static function makeRoulette3sFromRow($row)
    {
        $game = new Roulette3s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
    public  static function makeRoulette5sFromRow($row)
    {
        $game = new Roulette5s(
            $row->id,
            $row->name,
            $row->start_time,
            $row->end_time,
            $row->duration,
            $row->multiplier,
            $row->reward_complete,
            $row->result,
        );
       
        return $game;
    }
}
