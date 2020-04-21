<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/hitler.css">

    <title>Secret Hitler</title>
    
    <script type="text/javascript" src="../jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/ajax.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript" src="js/components.js"></script>
    <script type="text/javascript" src="js/drawing.js"></script>
    <script type="text/javascript" src="js/infotext.js"></script>
</head>
<body id="body">
<script>
var gameid = "";
var playername = "";
</script>

<?php
$gameid = "";
$playername = "";
$issues = "";
if(!isset($_GET['game'])){
    $issues .= "no game id;";
}
if(!isset($_GET['player'])){
    $issues .= "no player name;";
}
if(empty($issues)){
    $gameid = $_GET['game'];
    $playername = $_GET['player'];
    //echo "game: ", $gameid, ", player: ", $playername;
    echo "
    <script>
        gameid='",$gameid,"';
        playername='",$playername,"';
    </script>";
}else{
    echo "issues: ", $issues;
}

?>
<div id="info">this is info</div>
<div id="content"></div>
<div id="chat"></div>
Chat:
<input type="text" name="" id="message-input">
<button id="send_message" onclick="sendMessage()">Send</button>

<script>
    $(document).ready(play());
</script>

</body>
</html>