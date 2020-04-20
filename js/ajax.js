"use strict";

function getData(data=null) {
    return $.ajax({
        type: "GET",
        url: "php/interface.php",
        data: data,
        dataType: "json"
    }).then((r)=>{
        checkResponse(r);
    }).fail((e)=>{
        console.log(e);
        drawError(e.responseText);
    });
}


function checkResponse(r) {
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

function AJAXcreateGame(game) {
    return getData({"action":"create","game":game});
}

function AJAXgetGames() {
    return getData({"action":"get_games"});
}

function AJAXgetGame(game, player=null) {
    return getData({"action":"get_game", "game":game, "player":player});
}


function AJAXelect(game, player, id) {
    return getData({"action":"elect", "game":game, "player":player, "id":id});
}

function AJAXvote(game, player, vote) {
    return getData({"action":"vote", "game":game, "player":player, "vote":vote});
}

function AJAXdraw(game, player) {
    return getData({"action":"draw", "game":game, "player":player});
}

function AJAXpass(game, player, discard) {
    return getData({"action":"pass", "game":game, "player":player, "discard":discard});
}

function AJAXveto(game, player, wants) {
    return getData({"action":"veto", "game":game, "player":player, "wants":wants});
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