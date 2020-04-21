"use strict";

function createListEntry(name) {
    var el_name = $("<div></div>")
        .css("display","inline-block")
        .addClass("entry_name")
        .text(name);
    var button_join = $("<button></button>")
        .addClass("join_game")
        .text("join")
        .click(()=>{
            joinGame(name);
            });
    return $("<div></div>")
        .addClass("list_entry")
        .append(el_name)
        .append(button_join);
}

function createPlayer(name, info, index, action=null, vote=null) {
    var el_name = $("<div></div>")
        .css("display","inline-block")
        .addClass("player_name")
        .text(name);

    var player = $("<div></div>")
        .addClass("player")
        .append(el_name);
    
    var button_select = $("<button></button>")
        .addClass("join_game")

    function TFN(value, t, f, n=null){
        if(value == null){
            return n
        }
        return value ? t : f;
    }
    
    let infotext = "";
    infotext += TFN(info.isPresident, " [PRESIDENT]", "", "");
    infotext += TFN(info.isChancellor, " [CHANCELLOR]", "", "");
    infotext += TFN(info.isDead, " [DEAD]", "", "");
    infotext += TFN(info.isHitler, " [HITLER]", "", "");
    infotext += TFN(info.isFascist, " [FASCIST]", " [LIBERAL]", "");
    infotext += TFN(info.hasVoted, " [VOTED]", "", "");
    infotext += TFN(info.vote, " [JA!]", "[NEIN!]", "");
    
    var player_info = $("<span></span>")
        .text(infotext);
    
    if(action != null){
        if(action == "elect"){
            button_select
                .text("Elect as chancellor")
                .click(()=>AJAXelect(gameid, playername, index));
        }
        else if(action == "select_pres"){
            button_select
                .text("Select as next president")
                .click(()=>AJAXselectPres(gameid, playername, index));
        }
        else if(action == "execute"){
            button_select
                .text("Execute")
                .click(()=>AJAXexecute(gameid, playername, index));
        }
        else if(action == "investigate"){
            button_select
                .text("Investigate")
                .click(()=>AJAXinvestigate(gameid, playername, index));
        }
        player.append(button_select);
    }
        
    return player.append(player_info);
}

function createPile(type, count) {
    var pile = $("<div></div>")
        .text(type + "pile: " + count)
        .addClass("pile_" + type);
    if(type=="draw"){
        var button_select = $("<button></button>")
            .addClass("draw")
            .text("draw 3")
            .click(()=>AJAXdraw(gameid, playername));
        var button_peak = $("<button></button>")
            .addClass("peak")
            .text("peak at 3")
            .click(()=>AJAXpeak(gameid, playername));
        pile.append(button_select).append(button_peak);
    }
    return pile;
}

function createElectionTracker(count) {
    return $("<div></div>")
        .text("Election tracker: " + count);
}

function createPolicy(type, txt) {
    if(type=="empty"){
        var color = "white";
    } else if(type==0){
        var color = "blue";
    }
    if(type==1){
        var color = "red";
    }
    return $("<div></div>")
        .text(txt)
        .addClass(type)
        .css("display", "inline-block")
        .css("height", "50px")
        .css("min-width", "30px")
        .css("border", "solid black 1px")
        .css("background-color", color);
}


function createPresidentDialog(cards) {
    var policy = $("<div></div>")
        .addClass("policy_dialog");
    cards.forEach(
        (type, i)=>{
            var button_select = $("<button></button>")
                .text("Discard")
                .addClass("button_discard")
                .click(()=>AJAXpass(gameid, playername, i));
            policy
                .append(createPolicy(type))
                .append(button_select);
        }
    );
    return policy;
}

function createChancellorsDialog(cards) {
    var policy = $("<div></div>")
        .addClass("policy_dialog");
    cards.forEach(
        (type, i)=>{
            var button_select = $("<button></button>")
                .text("Enforce")
                .addClass("button_discard")
                .click(()=>AJAXenforce(gameid, playername, i));
            policy
                .append(createPolicy(type))
                .append(button_select);
        }
    );
    return policy;
}

function createVoteDialog() {
    var dialog = $("<div></div>")
        .addClass("vote_dialog");
    var ja_button = $("<button></button>")
        .addClass("ja_button")
        .text("JA!")
        .click(()=>AJAXvote(gameid, playername, 1));
    var nein_button = $("<button></button>")
        .addClass("nein_button")
        .text("NEIN!")
        .click(()=>AJAXvote(gameid, playername, 0));    
    return dialog
        .append(ja_button)
        .append(nein_button);
}

function createVetoDialog() {
    var dialog = $("<div></div>")
        .addClass("veto_dialog");
    var ja_button = $("<button></button>")
        .addClass("veto_button")
        .text("I want veto!")
        .click(()=>AJAXveto(gameid, playername, 1));
    var nein_button = $("<button></button>")
        .addClass("no_veto_button")
        .text("I don't want veto!")
        .click(()=>AJAXveto(gameid, playername, 0));
    return dialog
        .append(ja_button)
        .append(nein_button);
}

function createPeakDialog(cards) {
    var dialog = $("<div></div>")
        .addClass("peak_dialog");
    var ok_button = $("<button></button>")
        .addClass("ok_button")
        .text("Alright...")
        .click(()=>AJAXpeakOk(gameid, playername));
    cards.forEach(
        (type, i)=>{
            dialog.append(createPolicy(type));
        }
    );
    return dialog
        .append(ok_button);
}

function createChatEntry(entry) {
    let timestamp = $("<span></span>")
        .addClass("timestamp")
        .text("(" + entry.time + ") ");
    let name = $("<span></span>")
        .addClass("player")
        .text(entry.player + ": ");
    let message = $("<span></span>")
        .addClass("message")
        .text(entry.message);
    return $("<div></div>")
        .addClass("chat-entry")
        .append(timestamp)
        .append(name)
        .append(message);
}