"use strict";

function drawList(list) {
    for(let game of list){
        $("#content").append(createListEntry(game));
    }
    var new_game = $("<input type='text'' name='' id='game-input'></input>");
    var button_new_game = $("<button></button>")
        .addClass("new_game")
        .text("+ create new")
        .click(()=>{
            createGame();
            });
    $("#content")
        .append(new_game)
        .append(button_new_game);
}


function sendMessage(){
    let input = $("#message-input");
    let message = input.val();
    input.val("");
    AJAXmessage(gameid, playername, message);
}

function refreshGame() {
    AJAXgetGame(gameid, playername);
}

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
    if(game.hasOwnProperty("thisPlayer")){
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

    // Info text
    setInfoText(infotext[game.phase]);
    
    if(game.hasOwnProperty("thisPlayer")){
        drawOptionalDialogs(game);
    }

    drawChat(game.chat.messages);
}


function drawOptionalDialogs(game) {
// Dialogs based on game
    switch(game.phase){
        case "PH_START":
            // reserved
            break;

        case "PH_ELECT":
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'elect');
            }
            break;

        case "PH_VOTE":
            if(!game.thisPlayer.hasVoted){
                drawVoteDialog();
            }
            break;

        case "PH_DRAW":
            setInfoText();
            // TODO: Draw
            break;

        case "PH_PASS":
            if(game.thisPlayer.isPresident){
                drawPresidentsDialog(game.thisPlayer.hand);
            }
                 
            break;

        case "PH_VETO":
            if(game.thisPlayer.isPresident || game.thisPlayer.isChancellor){
                drawVetoDialog();
            }
            //no break here
        case "PH_ENFORCE":
            if(game.thisPlayer.isChancellor){
                drawChancellorsDialog(game.thisPlayer.hand);
            }       
            break;

        case "PH_INVESTIGATE":
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'investigate');
            }
            break;

        case "PH_PEAK":
            if(game.thisPlayer.isPresident){
                if(game.thisPlayer.hand.length > 0){
                    drawPeakDialog(game.thisPlayer.hand);
                }
            }
            break;

        case "PH_EXECUTE":
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'execute');
            }
            break;

        case "PH_SELECT_PRES":
            if(game.thisPlayer.isPresident){
                drawPlayers(game['players'], 'select_pres');
            }
            
            break;

        case "PH_FASCISTS_WON":
            break;

        case "PH_LIBERALS_WON":
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

function drawPeakDialog(cards) {
    $("#content").append(createPeakDialog(cards));
}

function drawVoteDialog() {
    $("#content").append(createVoteDialog());
}

function drawVetoDialog() {
    $("#content").append(createVetoDialog());
}

function drawChat(chat) {
    let chat_area = $("#chat");
    chat_area.css("height","100px");
    chat_area.css("overflow-y","scroll");
    chat_area.empty();
    chat.forEach(
        (entry) => {
            chat_area.append(createChatEntry(entry));
        }
    );
    chat_area.scrollTop(chat_area[0].scrollHeight);
}

function drawLobby(lobby) {
    
}