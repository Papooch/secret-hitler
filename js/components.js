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
            getGame(name)
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
            .click(()=>console.log("drawn 3 from draw pile"));
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