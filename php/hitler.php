<?php

require_once('fio.php');

$response = [
    "status" => "ok",
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
        
        default:
            # code...
            break;
    }
} else {
    $response['status'] = "nok";
}

echo json_encode($response);

?>