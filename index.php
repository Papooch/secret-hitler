<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/hitler.css">

    <script type="text/javascript" src="jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/common.js"></script>


    <title>Secret Hitler</title>
    <script>
        
        function tryme(){
            console.log("aaa");
        }
    </script>
    <style>
        body{
            padding-top: 10%;
            text-align: center;
        }
        h1{
            border-radius: 5px;
            background-color: black;
            color: rgb(236, 236, 236);
            padding: 5px 15px;
        }
        a{
            border-radius: 3px;
            display: inline-block;
            background-color: rgb(243, 41, 41);
            padding: 2px 5px;
            transition: 0.2s ease-in-out;
            text-decoration: none;
            color: black;
            -webkit-box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
            -moz-box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
            box-shadow: 0px 1px 7px 0px rgba(0,0,0,0.75);
        }
        a.blue{
            background-color: rgb(3, 143, 230);
        }
        a:hover{
            transform: scale(1.2);
        }
        .login{
            font-size: 120%;
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
            background-color: rgb(236, 236, 236);
            padding: 5px 10px;
        }
        form{
            width: 100%;
        }
        .textfield{
            font-size: 110%;
            width: 80%;
            max-width: 300px;
            height: 50px;
            border: none;
            border-radius: 3px;
        }
    </style>
</head>

<body id="body">
<h1>SECRET HITLER</h1> <br>
<div class="login">
<form action="play.php" method="post">
  <input class=textfield type="text" pattern=".{3,}" required id="player" name="player" placeholder="Your name"><br>
  <input class="button clickable" type="submit" value="Enter">
</form>
</div>
<br>
<p>Implementation of the successfull <a class=red href="https://secrethitler.io/">board game</a>.
Source on <a class=blue href="https://github.com/Papooch/secret-hitler">GitHub</a>.
<br>Created by Ondra Švanda, Verunka and Monička</p>
<br>
<p>This is a work in progress... <br><small>
Be aware that there is almost no security
and anyone can log in as anyone else.
<br>Please act responsibly.
    </small>
</p>
<script>
    $("#player").on('input', restrictInput);
</script>
</body>
</html>