<?php
require('fio.php');

/*Phase:
PH_START
PH_CHOOSE_CHANC
PH_VOTE
PH_DRAW3
PH_PASS2
PH_VETO
PH_ENFORCE
PH_REVEAL
PH_KILL
PH_SELECT_PRES
*/


function isGame($game) {
    return True;
}


function startGame($game) {

}


function isPresident($data, $player){
    if($player == $data['players'][$data['president']]){
        return true;
    }else{
        return false;
    }
}

function isChancellor($data, $player){
    if($player == $data['players'][$data['chancellor']]){
        return true;
    }else{
        return false;
    }
}

function isHitler($data, $player){
    if($player == $data['players'][$data['hitler']]){
        return true;
    }else{
        return false;
    }
}

function isFascist($data, $player){
    foreach ($data['fascists'] as $id) {
        if($player == $data['players'][$id]){
            return true;
        }
    }
    return false;
}

function isDead($data, $player){
    foreach ($data['dead'] as $id) {
        if($player == $data['players'][$id]){
            return true;
        }
    }
    return false;
}

function isPlayer($data, $player){
    foreach ($data['players'] as $pl) {
        if($player == $pl){
            return true;
        }
    }
    return false;
}

function hasVoted($data, $player){
    return $data['voting'][$player] != null;
}

function hasEveryoneVoted($data){
    foreach ($data['voting'] as $vote){
        if(is_null($vote)){
            return false;
        }
    }
    return true;
}


function resetVotes($data){
    $data['voting'] = [];
    foreach ($data['players'] as $player) {
        $data['voting'][$player] = nul;
    }
}

function addPlayerRoles($data, $player){
    $data['flags']['isDead'] = isDead($data, $player);
    $data['flags']['isPresident'] = isPresident($data, $player);
    $data['flags']['isChancellor'] = isChancellor($data, $player);
    $data['flags']['isHitler'] = isHitler($data, $player);
    $data['flags']['isFascist'] = isFascist($data, $player);
    $data['flags']['hasVoted'] = hasVoted($data, $player);
    return $data;
}

function getGame($game, $player=null){
    $data = loadGameFile($game);
    if(isPlayer($data, $player)){
        $data = addPlayerRoles($data, $player);
    }
    return $data;
}

function vote($game, $player, $vote) {
    $data = loadGameFile($game);
    if(!isPlayer($data, $player)){
        return "not a player!";
    }
    $data['voting'][$player] = $vote;
    if(hasEveryoneVoted($data)){
        #TODO: decide next phase
        echo "everybody has voted!";
    }
    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}


function draw3($game, $player) {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return "player not president";
    }
    if(!empty($data['presidentsHand'])){
        array_push($data['discardPile'], ...$data['presidentsHand']);
    }

    $hand = [];
    for($i=0; $i<3; $i++){
        if(empty($data['drawPile'])){
            $data['drawPile'] = $data['discardPile'];
            $data['discardPile'] = [];
            shuffle($data['drawPile']);
        }
        $hand[] = array_pop($data['drawPile']);
    }
    $data['presidentsHand'] = $hand;
    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}

function pass2chancellor($game, $player, $discard) {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return "player not president";
    }
    if(empty($data['presidentsHand'])){
        return "president has empty hand";
    }
    if(!empty($data['chancellorsHand'])){
        array_push($data['discardPile'], ...$data['chancellorsHand']);
    }

    array_push($data['discardPile'], $data['presidentsHand'][$discard]); // append to discard
    unset($data['presidentsHand'][$discard]); // remove from hand
    $data['chancellorsHand'] = array_values($data['presidentsHand']); // pass rest to chancellor
    $data['presidentsHand'] = []; // empty presidents hand

    #TODO: Check if veto unlocked

    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}

function enforcePolicy($game, $player, $enforce) {
    $data = loadGameFile($game);
    if(!isChancellor($data, $player)){
        return "player not chancellor";
    }
    if(empty($data['chancellorsHand'])){
        return "chancellor has empty hand";
    }

    if($data['chancellorsHand'][$enforce] == 0){ // which card was enforced
        $data['liberalPolicies']++;
    } else {
        $data['fascistPolicies']++;
    }
    array_push($data['discardPile'], $data['chancellorsHand'][1-$enforce]); // put the other card to discard
    $data['chancellorsHand'] = []; // empty chancellors hand

    #TODO: Check if effect triggered

    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}


function createGame($game) {
    if (is_dir(SH_GAME_DIR.gameHash($game))) return False;
    mkdir(SH_GAME_DIR.$game);
    return True;
}

function addPlayer($game, $player) {

}

function removePlayer($game, $player) {

}

?>