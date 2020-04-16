
function getData(data=null) {
    return $.ajax({
        type: "GET",
        url: "php/hitler.php",
        data: data,
        dataType: "json"
    });
}

function createGame(game) {
    return getData({"action":"create","game":game});
}

function getGames() {
    return getData({"action":"get_games"});
}

function getGame(game) {
    return getData({"action":"get_games", "game":game});
}