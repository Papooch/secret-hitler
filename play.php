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
    <script type="text/javascript" src="js/common.js"></script>
    <script type="text/javascript" src="js/handlers.js"></script>
    <script type="text/javascript" src="js/components/base.js"></script>
    <script type="text/javascript" src="js/components/board.js"></script>
    <script type="text/javascript" src="js/components/dialogs.js"></script>
    <script type="text/javascript" src="js/components/players.js"></script>
    <script type="text/javascript" src="js/components/lobby.js"></script>
    <script type="text/javascript" src="js/resources/infotext.js"></script>
</head>
<body id="body">
<script>
var gameid = "sample";
var playername = "verunka";
</script>

<?php
if(isset($_POST['player'])){
    echo "
    <script>
        playername='",$_POST['player'],"';
    </script>";
}
?>

<script>
    playername = playername.toUpperCase();
    $(document).ready(main());
</script>

</body>
</html>