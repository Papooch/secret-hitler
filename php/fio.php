<?php

require('common.php');

define("SH_GAME_DIR", "./../games/");

function isGame($game) {
    return True;
}


function startGame($game) {

}


function getGame($game) {
    $path = SH_GAME_DIR.gameHash($game)."/".$game.".json";
    $game = "";
    if(is_file($path)){
        $game = file_get_contents($path);
    }
    return json_decode($game);
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