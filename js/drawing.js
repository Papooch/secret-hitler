function drawLobby(list) {
    for(game of list){
        $("#content").append(createListEntry(game));
    }
    $("#content").append($("<div></div>").text("ahoj"));
}

function drawGame(game) {
    $("#content").append("Secret Hitler");
    drawPolicies(0, game['liberalPolicies'], 6);
    drawPolicies(1, game['fascistPolicies'], 5);
    
    
    drawPile("draw", game['drawPile'].length);
    drawPile("discard", game['discardPile'].length);
    
    
    $("#content").append("<br>Players:");
    drawPlayers(game['players']);
}


function drawPlayers(players) {
    for(p of players){
        $('#content').append(createPlayer(p));
    }
}

function drawPile(type, count){
    $("#content").append(createPile(type, count))
}

function drawPolicies(type, count, total){
    console.log("drawing policies");
    $("#content").append($("<br>"));
    for(let i=0; i<total; i++){
        $("#content").append(createPolicy(i>=count?"empty":type, ""));
    }
}