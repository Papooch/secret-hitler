
function getData(data=null) {
    return $.ajax({
        type: "GET",
        url: "php/interface.php",
        data: data,
        dataType: "json"
    }).then((r)=>{
        checkResponse(r)}
    );
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

function AJAXenforce(game, player, enforce) {
    return getData({"action":"enforce", "game":game, "player":player, "enforce":enforce});
}