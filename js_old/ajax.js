"use strict";


function getData(data=null) {
    return $.ajax({
        type: "GET",
        url: "php/interface.php",
        data: data,
        dataType: "json"
    }).fail((e)=>{
        console.log(e);
        drawError(e.responseText);
    });
}


function getDataPlay(data=null) {
    getData(data).then((r)=>{
        checkResponseGame(r);
    });
}

function getDataLobby(data=null) {
    getData(data).then((r)=>{
        drawLobby(r.payload);
    });
}

function getDataList(data=null){
    getData(data).then((r)=>{
        drawList(r.payload);
    });   
}

function getDataChat(data=null){
    getData(data).then((r)=>{
        drawChat(r.payload.messages);
    });   
}


function checkResponseGame(r) {
    if(!r.hasOwnProperty("status")){
        drawError("(backend) " + r);
        console.log(r);
        return;
    }

    if (!r.status == "ok") {
        drawError("(interface) " + r.payload);
        console.log(r);
        return;
    }
    
    if(!r.payload.hasOwnProperty("phase")){
        drawError("(game) " + r.payload);
        console.log(r);
        return;
    }
    drawGame(r.payload);
}

function AJAXgetGames() {
    return getDataList({"action":"get_games"});
}

function AJAXleaveGame(game, player) {
    return getDataList({"action":"leave","game":game, "player":player});
}

function AJAXcreateGame(game, player) {
    return getDataList({"action":"delete","game":game, "player":player});
}

function AJAXmessage(game, player, message) {
    return getDataChat({"action":"message", "game":game, "player":player, "message":message});
}

function AJAXgetChat(game) {
    return getDataChat({"action":"get_chat", "game":game});
}

function AJAXcreateGame(game, player) {
    return getDataLobby({"action":"create","game":game, "player":player});
}

function AJAXjoinGame(game, player) {
    return getDataLobby({"action":"join","game":game, "player":player});
}

function AJAXready(game, player, ready) {
    return getDataLobby({"action":"ready","game":game, "player":player, "ready":Number(ready)});
}

function AJAXkickPlayer(game, player, kick) {
    return getDataLobby({"action":"kick","game":game, "player":player, "kick":kick});
}

function AJAXgetGame(game, player=null) {
    return getDataPlay({"action":"get_game", "game":game, "player":player});
}

function AJAXelect(game, player, id) {
    return getDataPlay({"action":"elect", "game":game, "player":player, "id":id});
}

function AJAXvote(game, player, vote) {
    return getDataPlay({"action":"vote", "game":game, "player":player, "vote":Number(vote)});
}

function AJAXdraw(game, player) {
    return getDataPlay({"action":"draw", "game":game, "player":player});
}

function AJAXpass(game, player, discard) {
    return getDataPlay({"action":"pass", "game":game, "player":player, "discard":discard});
}

function AJAXveto(game, player, wants) {
    return getDataPlay({"action":"veto", "game":game, "player":player, "wants":Number(wants)});
}

function AJAXenforce(game, player, enforce) {
    return getDataPlay({"action":"enforce", "game":game, "player":player, "enforce":enforce});
}

function AJAXselectPres(game, player, id) {
    return getDataPlay({"action":"select_pres", "game":game, "player":player, "id":id});
}

function AJAXexecute(game, player, id) {
    return getDataPlay({"action":"execute", "game":game, "player":player, "id":id});
}

function AJAXinvestigate(game, player, id) {
    return getDataPlay({"action":"investigate", "game":game, "player":player, "id":id});
}

function AJAXpeak(game, player) {
    return getDataPlay({"action":"peak", "game":game, "player":player});
}

function AJAXpeakOk(game, player) {
    return getDataPlay({"action":"peak_ok", "game":game, "player":player});
}
