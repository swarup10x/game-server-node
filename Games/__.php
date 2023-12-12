getRewardMultiplierDiscs(bet) {
$userId = $bet->userId;
// Do something with the $userId value
$multiplier = 0;
$shouldReward = $bet->choosenItem == $game->result;
if ($shouldReward) $multiplier = $bet->choosenItem == '1111' || $bet->choosenItem == '0000' ? 15.6 : 3.9;
$onesCount = substr_count($game->result, '1'); // Count the number of '1' digits in the string
if (!$shouldReward) {
if ($bet->choosenItem == 'even' && $onesCount == 2) {
$shouldReward = true;
$multiplier = 1.95;
}
if ($bet->choosenItem == 'odd' && $$onesCount % 2 != 0) {
$shouldReward = true;
$multiplier = 1.95;
}
}
return multiplier
}