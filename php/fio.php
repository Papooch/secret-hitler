<?php

require('common.php');

define("SH_GAME_DIR", "./../games/");

function gameHash(string $game) : string {
    $h = substr(md5($game), 10, 8);
    return $h;
}

function loadGameFile(string $game) : array {
    $path = SH_GAME_DIR.gameHash($game)."/".$game.".json";
    $game = "";
    if(is_file($path)){
        $game = file_get_contents($path);
        return json_decode($game, true);
    }
    return false;
}

function saveGameFile(string $game, array $data) : void {
    $path = SH_GAME_DIR.gameHash($game)."/".$game.".json";
    $datajson = json_encode($data);
    if(is_file($path)){
        file_put_contents($path, $datajson);
    }
}

function getGameFileList() : array {
    $gamehashes = array_slice(scandir(SH_GAME_DIR),2);
    $games = [];
    foreach ($gamehashes as $hash) {
        $games[] = basename(scandir(SH_GAME_DIR.$hash)[2], ".json");
    }
    return $games;
}


?>