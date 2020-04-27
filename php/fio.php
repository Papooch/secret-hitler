<?php

define("SH_GAME_DIR", "./../games/");

function isGame(string $game) : bool {
    return in_array($game, getGameFileList());
}

function isGameActive(string $game) : bool {
    $game = loadGameFile($game);
    if($game == []) return false;
    if($game['phase'] == 'PH_FASCISTS_WON' || $game['phase'] == 'PH_LIBERALS_WON'){
        return false;
    }
    # TODO: more checks about age of game
    return true;
}


function gameHash(string $game) : string {
    $h = substr(md5($game), 10, 8);
    return $h;
}

function canCreateGame(string $game) : bool {
    if (is_dir(SH_GAME_DIR.gameHash($game))) return false;
    # allow max games: FIXME:
    if (count(getGameFileList()) > 5) return false;
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

function getChatPath(string $game) : string {
    return SH_GAME_DIR.gameHash($game)."/".$game."_chat.json";
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


function loadLobbyFile(string $game) : array {
    return loadFile($game, "lobby");
}

function saveLobbyFile(string $game, array $data) : void {
    saveFile($game, $data, "lobby");
}

function appendChatFile(string $game, string $message) : void {
    $path = getChatPath($game);
    $mes = ",".$message;
    file_put_contents($path, $mes, FILE_APPEND);
}

function loadChatFile(string $game) : array {
    $path = getChatPath($game);
    $chat = file_get_contents($path)."]}";
    return json_decode($chat, true);
}


function getGameFileList() : array {
    $gamehashes = array_slice(scandir(SH_GAME_DIR),2);
    $games = [];
    foreach ($gamehashes as $hash) {
        $dir = SH_GAME_DIR.$hash;
        if(!is_dir($dir)) continue;
        $games[] = basename(scandir($dir)[2], ".json");
    }
    return $games;
}

function clearChatFile(string $game) : void {
    $chatStart = '{"messages":[{"time":"","player":"","message":""}';
    file_put_contents(getChatPath($game), $chatStart);
}


function createLobby(string $game, string $player) : bool {
    if (!canCreateGame($game)) return false;

    mkdir(SH_GAME_DIR.gameHash($game));

    file_put_contents(getLobbyPath($game), "");
    file_put_contents(getGamePath($game), "");

    clearChatFile($game);

    $lobby['game'] = $game;
    $lobby['players'] = [];
    $lobby['creator'] = $player;

    saveFile($game, $lobby, "lobby");
    saveFile($game, [], "game");
    return true; 
}

function deleteGameFiles(string $game) {
    rrmdir(SH_GAME_DIR.gameHash($game));
}


// remove directory with files
function rrmdir($dir) {
    if (is_dir($dir)) { 
      $objects = scandir($dir);
      foreach ($objects as $object) { 
        if ($object != "." && $object != "..") { 
          if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
            rrmdir($dir. DIRECTORY_SEPARATOR .$object);
          else
            unlink($dir. DIRECTORY_SEPARATOR .$object); 
        } 
      }
      rmdir($dir); 
    } 
  }

?>