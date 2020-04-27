<?php

// Set $par to $_GET or $_POST
$par = $_GET;

// Default response
$response = [
    "status" => "nok",
    "payload" => []
];

// (-> someting) is a type of json object that will be returned.
// Samples of these files are in js/sample_jsons
$callbackMap = [
    'get_games' => ['getGames'], // -> list
    'create'    => ['createGame', 'game', 'player'], // -> lobby
    'delete'    => ['deleteGame', 'game', 'player'], // -> list
    'join'      => ['joinGame', 'game', 'player'], // -> lobby
    'ready'     => ['ready', 'game', 'player', 'ready'], // -> lobby
    'leave'     => ['leaveGame', 'game', 'player'], // -> list
    'kick'      => ['kickPlayer', 'game', 'player', 'kick'], // -> lobby
    'get_lobby' => ['getLobby', 'game', 'player'], // -> lobby
    'start'     => ['startGame', 'game', 'player'], // -> game
    'get_game'  => ['getGame', 'game', 'player'], // -> game
    'get_chat'  => ['getChat', 'game'], // -> chat
    'message'   => ['postMessage', 'game', 'player', 'message'], // -> chat
    'get_game_spectate'  => ['getGame', 'game'], // -> game
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
    'peak_ok'   => ['peakOk', 'game', 'player'] // -> game
];

/**
 * Checks if all parameters were supplied with the request
 * 
 * @param array[string] $params
 * @return array[string] string of information about missing parameters
 * # TODO: only include param names in return
 */
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

/**
 * Checks if all params were supplied in the request and calls the
 * callback function with values of those
 * 
 * @param callable $callback the function to be called
 * @param string[,string[,..]] $param_names variable number of parameter names
 * @return array the response
 */
function getResponse(callable $callback, string ...$param_names) : array {
    global $par;
    global $response;
    $errors = checkMissingParameters($param_names);
    if (count($errors) > 0){
        $response['payload'] = $errors;
    } else {
        $params = [];
        foreach($param_names as $name){
            $params[] = $par[$name];
        }
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