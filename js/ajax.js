"use strict";

function getData(data=null, callback) {
    return $.ajax({
        type: "GET",
        url: "php/interface.php",
        data: data,
        dataType: "json"
    }).fail((e)=>{
        clearInterval(refresher);
        console.log(e);
        if(g_errordialog && g_errordialog.isOpen) return;
        g_errordialog = new ErrorDialog(
            "<span class=highlight>(BACKEND)</span>" + e.responseText
            + "<br><br><span class=highlight>Please try refreshing the page and inform Ondra about this.</span>"
            ).appendTo("body");
    }).then((r)=>{
        validateResponse(r, callback);
    });
}

function validateResponse(r, callback){
    //console.log(r);
    if(r.status == "ok"){
        callback(r);
    }else{
        console.log("ERROR: ", r.payload);
        if(g_errordialog && g_errordialog.isOpen) return;
        g_errordialog = new ErrorDialog(r.payload).appendTo("body");
    }
}

function AJAXgetGames(callback) {
    return getData({"action":"get_games"}, callback);
}


function AJAXgetLobby(game, player, callback) {
    return getData({"action":"get_lobby", "game":game, "player":player}, callback);
}

function AJAXleaveGame(game, player, callback) {
    return getData({"action":"leave","game":game, "player":player}, callback);
}

function AJAXdeleteGame(game, player, callback) {
    return getData({"action":"delete","game":game, "player":player}, callback);
}

function AJAXmessage(game, player, message, callback) {
    return getData({"action":"message", "game":game, "player":player, "message":message}, callback);
}

function AJAXgetChat(game, callback) {
    return getData({"action":"get_chat", "game":game}, callback);
}

function AJAXcreateGame(game, player, callback) {
    return getData({"action":"create","game":game, "player":player}, callback);
}

function AJAXjoinGame(game, player, callback) {
    return getData({"action":"join","game":game, "player":player}, callback);
}

function AJAXready(game, player, ready, callback) {
    return getData({"action":"ready","game":game, "player":player, "ready":Number(ready)}, callback);
}

function AJAXkickPlayer(game, player, kick, callback) {
    return getData({"action":"kick","game":game, "player":player, "kick":kick}, callback);
}


function AJAXstartGame(game, player, callback) {
    return getData({"action":"start", "game":game, "player":player}, callback);
}

function AJAXgetGame(game, player=null, callback) {
    return getData({"action":"get_game", "game":game, "player":player}, callback);
}

function AJAXelect(game, player, id, callback) {
    return getData({"action":"elect", "game":game, "player":player, "id":id}, callback);
}

function AJAXvote(game, player, vote, callback) {
    return getData({"action":"vote", "game":game, "player":player, "vote":Number(vote)}, callback);
}

function AJAXdraw(game, player, callback) {
    return getData({"action":"draw", "game":game, "player":player}, callback);
}

function AJAXpass(game, player, discard, callback) {
    return getData({"action":"pass", "game":game, "player":player, "discard":discard}, callback);
}

function AJAXveto(game, player, wants, callback) {
    return getData({"action":"veto", "game":game, "player":player, "wants":Number(wants)}, callback);
}

function AJAXenforce(game, player, enforce, callback) {
    return getData({"action":"enforce", "game":game, "player":player, "enforce":enforce}, callback);
}

function AJAXselectPres(game, player, id, callback) {
    return getData({"action":"select_pres", "game":game, "player":player, "id":id}, callback);
}

function AJAXexecute(game, player, id, callback) {
    return getData({"action":"execute", "game":game, "player":player, "id":id}, callback);
}

function AJAXinvestigate(game, player, id, callback) {
    return getData({"action":"investigate", "game":game, "player":player, "id":id}, callback);
}

function AJAXpeak(game, player, callback) {
    return getData({"action":"peak", "game":game, "player":player}, callback);
}

function AJAXpeakOk(game, player, callback) {
    return getData({"action":"peak_ok", "game":game, "player":player}, callback);
}
