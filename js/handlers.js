"use strict";

var refresher = ()=>{};

function updateGameList(r){
    console.log(r);
    console.log(r.payload);
    g_lobby.games.update(r.payload);
}

function createGameList(r){
    $("body")[0].className = "";
    if(g_chat) g_chat.destroy();
    clearInterval(refresher);
    g_lobby.games = new GamesList(r.payload).appendTo("body");
    refresher = setInterval(()=>AJAXgetGames(updateGameList), 3000);
}

function updateLobby(r){
    checkChat(r);
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
        if(g_lobby.games) g_lobby.games.destroy();
        clearInterval(refresher);
        g_lobby.players = new PlayersList(r.payload).prependTo("body");
        AJAXgetChat(g_gameid, createChat);
        refresher = setInterval(()=>AJAXgetLobby(g_gameid, g_playername, updateLobby), 2000);
    });
}

function updateGame(r){
    checkChat(r);
    g_game.update(r.payload);
}

function createGame(r){
    checkChat(r);
    $("body")[0].className = "";
    if(g_lobby.players) g_lobby.players.destroy();
    clearInterval(refresher);
    g_game = new GameObject(r.payload);
    refresher = setInterval(()=>AJAXgetGame(g_gameid, g_playername, updateGame), 2000);
}


//helper
function createGovernmentDialog(r, isPresident){
    return new GovernmentDialog(r.payload.thisPlayer.hand, isPresident).appendTo("body");
}


function createChat(r){
    g_chat = new ChatBox(r.payload.messages).appendTo("body");
    g_chat.scrollDown()
}


function checkChat(r){
    if(r.payload.chatMessageCount != g_chat.message_count){
        AJAXgetChat(g_gameid, updateChat);
    }
}


function updateChat(r){
    g_chat.update(r.payload.messages)
}
