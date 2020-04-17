function createListEntry(name) {
    var el_name = $("<div></div>")
        .css("display","inline-block")
        .addClass("entry_name")
        .text(name);
    var button_join = $("<button></button>")
        .addClass("join_game")
        .text("join")
        .click(()=>{
            console.log("joining " + name);
            AJAXgetGame(name)
                .then((r)=>{
                    console.log(r['payload']);
                    drawGame(r['payload']);
                }
                );
            });
    return $("<div></div>")
        .addClass("list_entry")
        .append(el_name)
        .append(button_join);
}

function createPlayer(name) {
    var el_name = $("<div></div>")
        .css("display","inline-block")
        .addClass("player_name")
        .text(name);
    var button_select = $("<button></button>")
        .addClass("join_game")
        .text("select")
        .click(()=>console.log("selected player " + name));
    return $("<div></div>")
        .addClass("player")
        .append(el_name)
        .append(button_select);
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
        pile.append(button_select);
    }
    return pile;
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
    console.log("creating policy " + type + " " + txt);
    return $("<div></div>")
        .text(txt)
        .addClass(type)
        .css("display", "inline-block")
        .css("height", "20px")
        .css("min-width", "10px")
        .css("border", "solid black 1px")
        .css("background-color", color);
}


function createPresidentDialog(cards) {
    var policy = $("<div></div>")
        .addClass("policy_dialog")
    cards.forEach(
        function (type, i){
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
        .addClass("policy_dialog")
    cards.forEach(
        function (type, i){
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