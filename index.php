<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/hitler.css">

    <script type="text/javascript" src="../jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/common.js"></script>


    <title>Secret Hitler</title>
    <script>
        
        function tryme(){
            console.log("aaa");
        }
    </script>
    <style>
        body{
            padding-top: 20%;
        }
        .login{
            font-size: 120%;
            border: solid black 1px;
            border-radius: 3px;
            width: 50%;
            max-width: 600px;
            display: flex;
            flex-direction: collumn;
            justify-content: center;
            align-items: center;
            text-align: center;
            -webkit-box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
            -moz-box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
            box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
        }
        .login *{
            text-align: center;
            margin: 10px;
            display: inline;
            font-size: 100%;
            font-family: serif;
            font-weight: bold;
            text-transform: uppercase;
        }
        .login .button{
            border: none;
        }
        form{
            width: 100%;
        }
        .textfield{
            width: 80%;
            max-width: 300px;
            border: none;
            border-radius: 3px;
        }
    </style>
</head>

<body id="body">
<div>SECRET HITLER</div> <br>
<div class="login">
<form action="play.php" method="post">
  <input class=textfield type="text" pattern=".{3,}" required id="player" name="player" placeholder="Your name"><br>
  <input class="button clickable" type="submit" value="Enter">
</form>
</div>
<script>
    $("#player").on('input', restrictInput);
</script>
</body>
</html>