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
    <script type="text/javascript" src="js/components/base.js"></script>
    <script type="text/javascript" src="js/components/board.js"></script>
    <script type="text/javascript" src="js/components/dialogs.js"></script>
    <script type="text/javascript" src="js/components/players.js"></script>
    <script type="text/javascript" src="js/components/lobby.js"></script>
    <script type="text/javascript" src="js/resources/infotext.js"></script>
</head>
<body id="body">
<script>
// var gameid = "";
// var playername = "";
</script>
<!-- 
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
}
?> -->

<script>
    $(document).ready(main());
</script>

</body>
</html>