<?php

define("SH_GAME_DIR", "./../games/");

function gameHash(string $game) : string {
    $h = substr(md5($game), 10, 8);
    return $h;
}
function canCreateGame(string $game) : bool {
    if (is_dir(SH_GAME_DIR.gameHash($game))) return false;
    return true;
}

function getPath(string $game, $type) : string {
    if($type == "game") return getGamePath($game);
    elseif($type == "lobby") return getLobbyPath($game);
    else return "";
}

function getGamePath(string $game) : string {
    return SH_GAME_DIR.gameHash($game)."/".$game.".json";
}

function getLobbyPath(string $game) : string {
    return SH_GAME_DIR.gameHash($game)."/".$game."_lobby.json";
}

function loadFile(string $game, string $type) : array {
    $path = getPath($game, $type);
    $game = "";
    if(is_file($path)){
        $game = file_get_contents($path);
        return json_decode($game, true);
    }
    return [];
}

function saveFile(string $game, array $data, string $type) : void {
    $path = getPath($game, $type);
    $datajson = json_encode($data);
    if(is_file($path)){
        file_put_contents($path, $datajson);
    }
}

function loadGameFile(string $game) : array {
    return loadFile($game, "game");
}

function saveGameFile(string $game, array $data) : void {
    saveFile($game, $data, "game");
}

function getGameFileList() : array {
    $gamehashes = array_slice(scandir(SH_GAME_DIR),2);
    $games = [];
    foreach ($gamehashes as $hash) {
        $games[] = basename(scandir(SH_GAME_DIR.$hash)[2], ".json");
    }
    return $games;
}


function createLobby(string $game) : bool {
    if (!canCreateGame($game)) return false;

    mkdir(SH_GAME_DIR.gameHash($game));

    file_put_contents(getLobbyPath($game), "");
    file_put_contents(getGamePath($game), "");

    $lobby['players'] = [];

    saveFile($game, $lobby, "lobby");
    saveFile($game, [], "game");
    return true; 
}

function getLobby() : array {

}

?>