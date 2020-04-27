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
            AJAXcreateGame(game, g_playername, ()=>joinGame(game));
        });
        input.el.addClass("list-item");
        input.el.on('input', restrictInput);
        gamelist.push(input);
        let back_button = new ButtonObject(
            "< Back",
            function(){window.location.replace("index.php")},
            "back");
        super(gamelist, "list-games", "Player: <span class=game>"+g_playername+"</span>", [back_button]);
    }
    update(games){
        for (let i = 0; i < this.items.length - 1; i++) {
            this.items[i].el.remove();
        }
        this.items = [this.items[this.items.length - 1]];
        for (let i = games.length - 1; i >= 0; i--) {
            let button = createJoinButton(games[i]);
            let listitem = new ListItem([games[i]],["game"], i+1, button);
            listitem.setClickCallback(function(){
                let d = new ConfirmDialog("Are you sure you want to delete "+ this.text[0]+"?",
                ()=>AJAXdeleteGame(this.text[0], g_playername, updateGameList)
                ).appendTo("body");
            });
            this.items.unshift(listitem);
            this.el_list.prepend(listitem.el);
        }
        return this;
    }
}

function kickPlayer(name){
    AJAXkickPlayer(g_gameid, g_playername, name, updateLobby);
}

function createPlayerInList(name, ready, i, button){
    let item = new ListItem(
        [(g_playername != name) ? name : "<span class=myname>" + name + "</span>",
            (ready ? "ready" : "")],
        ["name", (ready ? "ready" : "not-ready")],
        i+1,
        (g_playername != name) ? button : null);
    return item;
}

function getPlayButtonCallback(lobby){
    let AJAXfunc = lobby.gameActive ? AJAXgetGame : AJAXstartGame;
    return function(){
        AJAXfunc(g_gameid, g_playername, createGame)}
}

function getPlayButtonText(lobby){
    return lobby.gameActive ? "Enter >" : "Start >";
}

function getPlayButtonClass(lobby){
    return "start " + ((lobby.everyoneReady || lobby.gameActive ) ? " can-start" : "");
}

function createPlayButton(lobby){
    let button = new ButtonObject(
        getPlayButtonText(lobby),
        getPlayButtonCallback(lobby),
        getPlayButtonClass(lobby));
    return button;
}

function createRestartButton(lobby){
    let button = new ButtonObject(
        "restart",
        lobby.gameActive
            ? ()=>AJAXstartGame(g_gameid, g_playername, createGame)
            : ()=>{g_errordialog = new ErrorDialog("cannot restart game that has not started").appendTo("body")},
        "start" + (lobby.gameActive ? " can-start" : ""));
    return button;
}

class PlayersList extends ListObject {
    constructor(lobby){
        let players = lobby.players;
        let playerlist = [];
        let i = 0;
        let back_button = new ButtonObject(
                        "< Leave",
                        function(){
                            AJAXleaveGame(g_gameid, g_playername,
                                (r)=>{
                                g_lobby.players.destroy();
                                createGameList(r);
                                })},
                        "back");
        let ready_button = new ButtonObject(
                        "I am ready!",
                        function(){
                            AJAXready(g_gameid, g_playername, true, updateLobby)},
                        "ready");
        let start_button = createPlayButton(lobby);
        let restart_button = createRestartButton(lobby);
        let b_list = [back_button, ready_button, start_button];
        if(lobby.thisPlayer.isCreator){
            b_list = [back_button, ready_button, restart_button, start_button];
        }
        super(playerlist, "list-players", "Game: <span class=game>"+g_gameid+"</span>", b_list);
        this.json = lobby;
        this.update(lobby);
    }
    update(lobby){
        this.json = lobby;
        let players = lobby.players;
        this.el_list.empty();
        this.items = [];
        let i = 0;
        for (let [name, ready] of Object.entries(players)){
            let button = null;
            if(lobby.thisPlayer.isCreator){
                button = new ButtonObject("kick", function(){kickPlayer(name)}, "kick");
            }
            let player = createPlayerInList(name, ready, i, button);
            this.items.push(player);
            this.el_list.append(player.el);
            i++;
        }
        this.buttons[this.buttons.length - 1]
            .setClickCallback(
                getPlayButtonCallback(lobby),
                getPlayButtonClass(lobby))
            .setText(
                getPlayButtonText(lobby)
            );
        return this;
    }
}