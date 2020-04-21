<?php

$par = $_GET;

$response = [
    "status" => "nok",
    "payload" => []
];

$callbackMap = [
    'get_games' => ['getGameFileList'], // -> list
    'create'    => ['createGame', 'game', 'player'], // -> lobby
    'join'      => ['joinGame', 'game', 'player'], // -> lobby
    'leave'     => ['leaveGame', 'game', 'player'], // -> list
    'kick'      => ['kickPlayer', 'game', 'player', 'kick'], // -> lobby
    'get_lobby' => ['getLobby', 'game'], // -> lobby
    'get_game'  => ['getGame', 'game', 'player'], // -> game
    'elect'     => ['selectChancellor', 'game', 'player', 'id'], // -> game
    'draw'      => ['draw3', 'game', 'player'], // -> game
    'vote'      => ['vote', 'game', 'player', 'vote'], // -> game
    'pass'      => ['pass2chancellor', 'game', 'player', 'discard'], // -> game
    'veto'      => ['veto', 'game', 'player', 'wants'], // -> game
    'enforce'   => ['enforcePolicy', 'game', 'player', 'enforce'], // -> game
    'select_pres' => ['selectPresident', 'game', 'player', 'id'], // -> game
    'execute'   => ['execute', 'game', 'player', 'id'], // -> game
    'investigate' => ['investigate', 'game', 'player', 'id'], // -> game
    'peak'      => ['peak', 'game', 'player'], // -> game
    'peak_ok'   => ['peakOk', 'game', 'player'], // -> game
    'message'   => ['postMessage', 'game', 'player', 'message'] // -> game #FIXME: do not return game
];

function checkMissingParameters(array $params) : array{
    global $par;
    $errors = [];
    foreach ($params as $param) {
        if (!isset($par[$param])){
            $errors[] = "parameter ".$param." is missing.";
        }
    }
    return $errors;
}

function getResponse(callable $callback, string ...$param_names) : array {
    global $par;
    $errors = checkMissingParameters($param_names);
    if (count($errors) > 0){
        $response['status'] = "nok";
        $response['payload'] = $errors;
    } else {
        $params = [];
        foreach($param_names as $name){
            $params[] = $par[$name];
        }
        $response['status'] = "ok";
        $response['payload'] = $callback(...$params);
    }
    return $response;
};

if (isset($par['action'])) {
    if(array_key_exists($par['action'], $callbackMap)){
        require_once('public.php');
        $response = getResponse(...$callbackMap[$par['action']]);
    }else{
        $response['payload'] = "unknown action: ".$par['action'];
    }
} else {
    $response['payload'] = "action not set";
}

echo json_encode($response);

?>