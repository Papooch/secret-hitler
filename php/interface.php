<?php

require_once('access.php');

$response = [
    "status" => "nok",
    "payload" => []
];

if (isset($_GET['action'])) {
    if($_GET['action'] == 'get_games'){
        $response['status'] = "ok";
        $response['payload'] = getGameFileList();
    }elseif(isset($_GET['game'])){
        switch ($_GET['action']) {
            case 'create':
                if (createGame($_GET['game'])) {
                    $response['payload'] = "created";
                }
                break;
            
            case 'get_game':
                if(!isset($_GET['player'])){
                    $_GET['player'] = null;
                }
                $response['status'] = "ok";
                $response['payload'] = getGame($_GET['game'], $_GET['player']);
                break;
            
            case 'set_game':
                $response['status'] = "ok";
                saveGameFile($_GET['game']);
                break;

            case 'elect':
                if(!isset($_GET['player'])){
                    break;
                }
                if(!isset($_GET['id'])){
                    break;
                }
                $response['status'] = "ok";
                $response['payload'] = selectChancellor($_GET['game'], $_GET['player'], $_GET['id']);
                break;
            
            case 'draw':
                if(!isset($_GET['player'])){
                    break;
                }
                $response['status'] = "ok";
                $response['payload'] = draw3($_GET['game'], $_GET['player']);
                break;

            case 'vote':
                if(!isset($_GET['player'])){
                    break;
                }
                if(!isset($_GET['vote'])){
                    break;
                }
                $response['status'] = "ok";
                $response['payload'] = vote($_GET['game'], $_GET['player'], $_GET['vote']);
                break;

            case 'pass':
                if(!isset($_GET['player'])){
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
    }else{
        $response['payload'] = "game not specified";
    }
} else {
    $response['status'] = "nok";
}

echo json_encode($response);

?>