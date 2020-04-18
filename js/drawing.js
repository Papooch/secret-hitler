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
    if (r.status = "ok") {
        try {
            r.payload.phase;
            drawGame(r.payload);
        } catch (e) {
            drawError("(game) " + r.payload);
            console.log(e);
        }
    } else {
        drawError("(interface) " + r.payload);
    }
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
    if(game.flags.isFascist){
        $("#content").append("Fascist, ");
    }
    if(game.flags.isHitler){
        $("#content").append("Hitler, ");
    }
    if(game.flags.isPresident){
        $("#content").append("PRESIDENT, ");
    }
    if(game.flags.isChancellor){
        $("#content").append("CHANCELLOR, ");
    }

    // Policy board
    drawPolicies(game.triggersPowers, 0, game['liberalPolicies'], 5);
    drawPolicies(game.triggersPowers, 1, game['fascistPolicies'], 6);

    
    // Election tracker
    drawElectionTracker(game.electionTracker);
    
    // Draw and discard pile
    drawPile("draw", game['drawPile'].length);
    drawPile("discard", game['discardPile'].length);
    
    // Players
    $("#content").append("<br>Players:");
    drawPlayers(game['players']);
    $("#content").append("<br>Available actions:<br>");

    // Dialogs based on game
    switch(game.phase){
        case "PH_START":
            // reserved
            break;

        case "PH_CHOOSE_CHANC":
            setInfoText("Game has started!");
            if(game.flags.isPresident){
                drawPlayers(game['players'], 'elect');
            }
            break;

        case "PH_VOTE":
            setInfoText("Vote for this government");
            if(!game.flags.hasVoted){
                drawVoteDialog();
            }
            break;

        case "PH_DRAW":
            setInfoText("President is drawing 3 policies and passes 2 to chancellor");
            // TODO: Draw
            break;

        case "PH_PASS":
            setInfoText("President is drawing 3 policies and passes 2 to chancellor");
            if(game.flags.isPresident){
                drawPresidentsDialog(game.presidentsHand);
            }
                 
            break;

        case "PH_VETO":
            
            break;

        case "PH_ENFORCE":
            setInfoText("Chancellor enforces 1 policy");
            if(game.flags.isChancellor){
                drawChancellorsDialog(game.chancellorsHand);
            }       
            break;

        case "PH_INVESTIGATE":
            
            break;

        case "PH_PEAK":
            
            break;

        case "PH_EXECUTE":
            
            break;

        case "PH_SELECT_PRES":
            
            break;

    }


}


function setInfoText(txt) {
    $("#info").text(txt);
}

function drawPlayers(players, action=null) {
   players.forEach(
       (p, i) => $('#content').append(createPlayer(p, i, action))
   );
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
            if(triggers.investigateAt.includes(i)) txt+= "I";
            if(triggers.peakAt.includes(i)) txt+= "P";
            if(triggers.executeAt.includes(i)) txt+= "E";
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
