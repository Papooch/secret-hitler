"use strict";

var refresher = ()=>{};

function updateGameList(){
    console.log("updating");
    AJAXgetGames()
    .then((r)=>{
        console.log(r.payload);
        lobby.games.update(r.payload);
    });
}

function handleErrors(r){
    if(r.status == "ok"){
        return;
    }
    console.log(r.payload);
}

function createGameList(){
    AJAXgetGames()
    .then((r)=>{
        clearInterval(refresher);
        lobby.games = new GamesList(r.payload).appendTo("body");
        refresher = setInterval(updateGameList, 3000);
    });
}

function updateLobby(){
    AJAXgetLobby(gameid, playername)
    .then((r)=>{
        handleErrors(r);
        if(Object.keys(r.payload.players).includes(playername)){
            lobby.players.update(r.payload.players);
        }else{
            lobby.players.destroy();
            createGameList();
        }
    });
}

function joinGame(game){
    gameid = game;
    AJAXjoinGame(game, playername)
    .then((r)=>{
        handleErrors(r);
        lobby.games.destroy();
        clearInterval(refresher);
        lobby.players = new PlayersList(r.payload.players).appendTo("body");
        refresher = setInterval(updateLobby, 2000);
    });
}