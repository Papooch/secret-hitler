function drawLobby(list) {
    for(game of list){
        $("#content").append(createListEntry(game));
    }
    $("#content").append($("<div></div>").text("ahoj"));
}


function refreshGame() {
    AJAXgetGame(gameid, playername);
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

var refresher = setInterval(refreshGame, 2000);

function drawError(error) {
    $("#content").empty();
    $("#content").append("ERROR: " + error);
}

function drawGame(game) {


    console.log(game);
    $("#content").empty();
    var refresh_button = $("<button></button>")
        .text("Refresh")
        .addClass("refresh_button")
        .click(refreshGame);

    // Title and refresh button
    $("#content").append(refresh_button);
    $("#content").append("<br>Secret Hitler");
    $("#content").append("<br>Player: " + playername);
    $("#content").append("<br>You are: ");

    // Roles
    if(game.thisPlayer.isFascist){
        $("#content").append("Fascist, ");
    }
    if(game.thisPlayer.isHitler){
        $("#content").append("Hitler, ");
    }
    if(game.thisPlayer.isPresident){
        $("#content").append("PRESIDENT, ");
    }
    if(game.thisPlayer.isChancellor){
        $("#content").append("CHANCELLOR, ");
    }

    // Policy board
    drawPolicies(game.triggers, 0, game.board.liberalPolicies, 5);
    drawPolicies(game.triggers, 1, game.board.fascistPolicies, 6);

    
    // Election tracker
    drawElectionTracker(game.board.electionTracker);
    
    // Draw and discard pile
    drawPile("draw", game.board.drawPileSize);
    drawPile("discard", game.board.discardPileSize);
    
    // Players
    $("#content").append("<br>Players:");
    drawPlayers(game['players']);
    $("#content").append("<br>Available actions:<br>");

    // Dialogs based on game
    switch(game.phase){
        case "PH_START":
            // reserved
            break;

        case "PH_ELECT":
            setInfoText("President chooses chancellor!");
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'elect');
            }
            break;

        case "PH_VOTE":
            setInfoText("Vote for this government");
            if(!game.thisPlayer.hasVoted){
                drawVoteDialog();
            }
            break;

        case "PH_DRAW":
            setInfoText("President is drawing 3 policies and passes 2 to chancellor");
            // TODO: Draw
            break;

        case "PH_PASS":
            setInfoText("President is drawing 3 policies and passes 2 to chancellor");
            if(game.thisPlayer.isPresident){
                drawPresidentsDialog(game.thisPlayer.hand);
            }
                 
            break;

        case "PH_VETO":
            setInfoText("Chancellor enforces 1 policy (or may want veto)");
            if(game.thisPlayer.isPresident || game.thisPlayer.isChancellor){
                drawVetoDialog();
            }
            //no break here
        case "PH_ENFORCE":
            setInfoText("Chancellor enforces 1 policy");
            if(game.thisPlayer.isChancellor){
                drawChancellorsDialog(game.thisPlayer.hand);
            }       
            break;

        case "PH_INVESTIGATE":
            
            break;

        case "PH_PEAK":
            
            break;

        case "PH_EXECUTE":
            
            break;

        case "PH_SELECT_PRES":
            setInfoText("President chooses next president!");
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'select_pres');
            }
            
            break;

        case "PH_FASCISTS_WON":
            setInfoText("Fascists won!");
            break;

        case "PH_LIBERALS_WON":
            setInfoText("Liberals won!");
            break;


    }


}


function setInfoText(txt) {
    $("#info").text(txt);
}

function drawPlayers(players, action=null) {
    let i = 0;
    for (let [name, info] of Object.entries(players)){
        $('#content').append(createPlayer(name, info, i, action));
        i++;
    }
}

function drawPile(type, count) {
    $("#content").append(createPile(type, count))
}

function drawElectionTracker(count) {
    $("#content").append(createElectionTracker(count));
}


function drawPolicies(triggers, type, count, total){
    $("#content").append($("<br>"));
    for(let i=0; i<total; i++){
        let txt = "\u00A0";
        if(type==1){
            if(triggers.selectPresidentAt.includes(i+1)) txt+= "S";
            if(triggers.investigateAt.includes(i+1)) txt+= "I";
            if(triggers.peakAt.includes(i+1)) txt+= "P";
            if(triggers.executeAt.includes(i+1)) txt+= "E";
        }
        $("#content").append(createPolicy(i>=count?"empty":type, txt));
    }
}

function drawPresidentsDialog(cards) {
    $("#content").append(createPresidentDialog(cards));
}

function drawChancellorsDialog(cards) {
    $("#content").append(createChancellorsDialog(cards));
}

function drawVoteDialog() {
    $("#content").append(createVoteDialog());
}

function drawVetoDialog() {
    $("#content").append(createVetoDialog());
}
