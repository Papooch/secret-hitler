<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/hitler.css">

    <title>Secret Hitler</title>
    
    <script type="text/javascript" src="../jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js_old/ajax.js"></script>
    <script type="text/javascript" src="js_old/main.js"></script>
    <script type="text/javascript" src="js_old/components.js"></script>
    <script type="text/javascript" src="js_old/drawing.js"></script>
    <script>
        var $_GET = <?php echo json_encode($_GET); ?>;
    </script>
</head>
<script>
    var gameid = "";
    var playername = "";
</script>
<body id="body">
    SECRET HITLER <br>
    Your name:
    <input type="text" name="" id="name-input">
    <div id="content">
        Loading...
    </div>
<script>
    $(document).ready(list())
</script>
</body>
</html>