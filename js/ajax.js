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


function AJAXgetGames() {
    return getData({"action":"get_games"});
}

function AJAXleaveGame(game, player) {
    return getData({"action":"leave","game":game, "player":player});
}

function AJAXcreateGame(game, player) {
    return getData({"action":"delete","game":game, "player":player});
}

function AJAXmessage(game, player, message) {
    return getData({"action":"message", "game":game, "player":player, "message":message});
}

function AJAXgetChat(game) {
    return getData({"action":"get_chat", "game":game});
}

function AJAXcreateGame(game, player) {
    return getData({"action":"create","game":game, "player":player});
}

function AJAXjoinGame(game, player) {
    return getData({"action":"join","game":game, "player":player});
}

function AJAXready(game, player, ready) {
    return getData({"action":"ready","game":game, "player":player, "ready":Number(ready)});
}

function AJAXkickPlayer(game, player, kick) {
    return getData({"action":"kick","game":game, "player":player, "kick":kick});
}

function AJAXgetGame(game, player=null) {
    return getData({"action":"get_game", "game":game, "player":player});
}

function AJAXelect(game, player, id) {
    return getData({"action":"elect", "game":game, "player":player, "id":id});
}

function AJAXvote(game, player, vote) {
    return getData({"action":"vote", "game":game, "player":player, "vote":Number(vote)});
}

function AJAXdraw(game, player) {
    return getData({"action":"draw", "game":game, "player":player});
}

function AJAXpass(game, player, discard) {
    return getData({"action":"pass", "game":game, "player":player, "discard":discard});
}

function AJAXveto(game, player, wants) {
    return getData({"action":"veto", "game":game, "player":player, "wants":Number(wants)});
}

function AJAXenforce(game, player, enforce) {
    return getData({"action":"enforce", "game":game, "player":player, "enforce":enforce});
}

function AJAXselectPres(game, player, id) {
    return getData({"action":"select_pres", "game":game, "player":player, "id":id});
}

function AJAXexecute(game, player, id) {
    return getData({"action":"execute", "game":game, "player":player, "id":id});
}

function AJAXinvestigate(game, player, id) {
    return getData({"action":"investigate", "game":game, "player":player, "id":id});
}

function AJAXpeak(game, player) {
    return getData({"action":"peak", "game":game, "player":player});
}

function AJAXpeakOk(game, player) {
    return getData({"action":"peak_ok", "game":game, "player":player});
}
