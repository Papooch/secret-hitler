"use strict";

function play() {
    if(gameid){
        refreshGame();
        var refresher = setInterval(refreshGame, 2000);
        $("#message-input").keyup(
            (e)=>{
                if(e.key == "Enter") sendMessage();
            }
        );
    }else{
        list();
        //data.then((r)=>console.log(r));
        //$("body").text(data);
    }
}

function list() {
    $('#content').empty();
    AJAXgetGames();
    $("#name-input").keyup((e)=>{
        playername = $("#name-input").val();
    });
}


function createGame() {
    playername = $("#name-input").val();
    gameid = $("#game-input").val();
    if(playername.length == 0) return;
    if(gameid.length == 0) return;
    AJAXcreateGame(gameid, playername);
}

function joinGame(game) {
    playername = $("#name-input").val();
    if(playername.length == 0) return;
    gameid = game;
    AJAXjoinGame(game, playername);
}