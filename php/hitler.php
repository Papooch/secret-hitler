<?php

require_once('fio.php');

$response = [
    "status" => "nok",
    "payload" => []
];

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            if (createGame($_GET['game'])) {
                $response['payload'] = "created";
            }
            break;
        
        case 'get_games':
            $response['status'] = "ok";
            $response['payload'] = getGameList();

        case 'get_game':
            if(!isset($_GET['game'])){
                break;
            }
            $response['status'] = "ok";
            $response['payload'] = getGame($_GET['game']);
            break;
        
        case 'set_game':
            if(!isset($_GET['game'])){
                break;
            }
            $response['status'] = "ok";
            setGame($_GET['game']);
            break;
        
        case 'draw':
            if(!isset($_GET['game'])){
                break;
            }
            $response['status'] = "ok";
            $response['payload'] = draw3($_GET['game'], $_GET['player']);
            break;

        case 'pass':
            if(!isset($_GET['game'])){
                break;
            }
            if(!isset($_GET['discard'])){
                break;
            }
            $response['status'] = "ok";
            $response['payload'] = pass2chancellor($_GET['game'], $_GET['player'], $_GET['discard']);
            break;

        case 'enforce':
            if(!isset($_GET['game'])){
                break;
            }
            if(!isset($_GET['enforce'])){
                break;
            }
            $response['status'] = "ok";
            $response['payload'] = enforcePolicy($_GET['game'], $_GET['player'], $_GET['enforce']);
            break;
        
        default:
            $response['status'] = "nok";
            $response['payload'] = "unknown command";
            break;
    }
} else {
    $response['status'] = "nok";
}

echo json_encode($response);

?>