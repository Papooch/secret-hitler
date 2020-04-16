<?php

require('common.php');

define("SH_GAME_DIR", "./../games/");

function isGame($game) {
    return True;
}


function startGame($game) {

}


function isPresident($data, $player){
    if($player != $data['president']){
        return false;
    }else{
        return true;
    }
}

function isChancellor($data, $player){
    if($player != $data['chancellor']){
        return false;
    }else{
        return true;
    }
}

function isHitler($data, $player){
    if($player != $data['hitler']){
        return false;
    }else{
        return true;
    }
}

function getGame($game) {
    $path = SH_GAME_DIR.gameHash($game)."/".$game.".json";
    $game = "";
    if(is_file($path)){
        $game = file_get_contents($path);
    }
    return json_decode($game, true);
}

function setGame($game, $data) {
    $path = SH_GAME_DIR.gameHash($game)."/".$game.".json";
    $datajson = json_encode($data);
    if(is_file($path)){
        file_put_contents($path, $datajson);
    }
}

function draw3($game, $player) {
    $data = getGame($game);
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
    setGame($game, $data);
    return $data;
}

function pass2chancellor($game, $player, $discard) {
    $data = getGame($game);
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
    setGame($game, $data);
    return $data;
}

function enforcePolicy($game, $player, $enforce) {
    $data = getGame($game);
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
    setGame($game, $data);
    return $data;
}

function getGameList() {
    $gamehashes = array_slice(scandir(SH_GAME_DIR),2);
    $games = [];
    foreach ($gamehashes as $hash) {
        $games[] = basename(scandir(SH_GAME_DIR.$hash)[2], ".json");
    }
    return $games;
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