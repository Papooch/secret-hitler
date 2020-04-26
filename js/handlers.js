"use strict";

var refresher = ()=>{};

function updateGameList(r){
    console.log(r);
    console.log(r.payload);
    g_lobby.games.update(r.payload);
}

function createGameList(r){
    $("body")[0].className = "";
    clearInterval(refresher);
    g_lobby.games = new GamesList(r.payload).appendTo("body");
    refresher = setInterval(()=>AJAXgetGames(updateGameList), 3000);
}

function updateLobby(r){
    console.log("updating lobby", r);
    if(Object.keys(r.payload.players).includes(g_playername)){
        g_lobby.players.update(r.payload);
    }else{
        g_lobby.players.destroy();
        createGameList();
    }
}

function joinGame(game){
    $("body")[0].className = "";
    g_gameid = game;
    AJAXjoinGame(game, g_playername, (r)=>{
        g_lobby.games.destroy();
        clearInterval(refresher);
        g_lobby.players = new PlayersList(r.payload).appendTo("body");
        refresher = setInterval(()=>AJAXgetLobby(g_gameid, g_playername, updateLobby), 2000);
    });
}

function updateGame(r){
    g_game.update(r.payload);
}

function createGame(r){
    $("body")[0].className = "";
    g_lobby.players.destroy();
    clearInterval(refresher);
    g_game = new GameObject(r.payload);
    refresher = setInterval(()=>AJAXgetGame(g_gameid, g_playername, updateGame), 2000);
}

function createGovernmentDialog(r, isPresident){
    return new GovernmentDialog(r.payload.thisPlayer.hand, isPresident).appendTo("body");
}