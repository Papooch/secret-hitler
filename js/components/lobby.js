"use strict";

function createJoinButton(game){
    let button = new ButtonObject("Join", ()=>joinGame(game), "join");
    return button;
}

class GamesList extends ListObject {
    constructor(games){
        let gamelist = []
        let i = 0;
        games.forEach(name => {
            let button = createJoinButton(name);
            gamelist.push(new ListItem([name],["game"], i+1, button));
            i++;
        });
        var input = new InputObject("", "create new", new ButtonObject("+", null, 'join create'));
        input.button.setClickCallback(()=>{
            let game = input.getValue();
            AJAXcreateGame(game, playername)
            .then((r)=>joinGame(game));
        });
        input.el.addClass("list-item");
        input.el.on('input', restrictInput);
        gamelist.push(input);
        let back_button = new ButtonObject(
            "< Back",
            function(){window.history.back()},
            "back");
        super(gamelist, "list-games", "Player: <span class=game>"+playername+"</span>", [back_button]);
    }
    update(games){
        for (let i = 0; i < this.items.length - 1; i++) {
            this.items[i].el.remove();
        }
        for (let i = games.length - 1; i >= 0; i--) {
            let button = createJoinButton(games[i]);
            let listitem = new ListItem([games[i]],["game"], i+1, button);
            this.items.unshift(listitem);
            this.el_list.prepend(listitem.el);
        }
        return this;
    }
}

function kickPlayer(name){
    AJAXkickPlayer(gameid, playername, name)
        .then((r)=>lobby.players.update(r.payload.players));
}

function createPlayerInList(name, ready, i, button){
    let item = new ListItem(
        [(playername != name) ? name : "<span class=myname>" + name + "</span>",
        (ready ? "ready" : "")],
        ["name", (ready ? "ready" : "not-ready")],
        i+1,
        (playername != name) ? button : null);
    return item;
}

class PlayersList extends ListObject {
    constructor(players){
        let playerlist = [];
        let i = 0;
        for (let [name, ready] of Object.entries(players)){
            let button = new ButtonObject("kick", function(){kickPlayer(name)}, "kick");
            playerlist.push(createPlayerInList(name, ready, i, button));
            i++;
        }
        let back_button = new ButtonObject(
                        "< Leave",
                        function(){
                            AJAXleaveGame(gameid, playername)
                            .then((r)=>{
                                lobby.players.destroy();
                                createGameList();
                            })},
                        "back");
        let ready_button = new ButtonObject(
                        "I am ready!",
                        function(){
                            AJAXready(gameid, playername, true)
                            .then((r)=>lobby.players.update(r.payload.players))},
                        "ready");
        let start_button = new ButtonObject(
                        "Start >",
                        function(){
                            AJAXstartGame(gameid, playername)
                            .then(createGame())},
                        "create");
        super(playerlist, "list-players", "Game: <span class=game>"+gameid+"</span>", [back_button, ready_button, start_button]);
    }
    update(players){
        this.el_list.empty();
        let i = 0;
        for (let [name, ready] of Object.entries(players)){
            let button = new ButtonObject("kick", function(){kickPlayer(name)}, "kick");
            let player = createPlayerInList(name, ready, i, button);
            this.items.push(player);
            this.el_list.append(player.el);
            i++;
        }
        return this;
    }
}