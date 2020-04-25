"use strict";

// globl objects:
var g_game = {
    info : null,
    board : null,
    instruction: null,
    players : null,
}

var g_lobby = {
    games : null,
    players : null,
};

var g_chat = {
    coutn : null,
    messages : null,
}

// var g_playername = "verunka";
var g_gameid = "sample";

function main(){
    //#TODO: lobby
    
    AJAXgetGames(createGameList);
    //g_playername = "ondra";
    //AJAXgetGame("sample", "ondra", createGame);
    

    // AJAXgetGame(g_gameid, g_playername)
    // .then((r)=>{
    //     let p = r.payload;
    //     game.board = new Board(p.board, p.modifiers, p.triggers).appendTo("body");
    //     game.players = new Players(p.players).appendTo("body");
    // });

    // let t = setTimeout(()=>{
    //     game.board.drawPile.setClickCallback(()=>{let v = new GovernmentDialog([1,0,1], true).appendTo("body")});
    //     //v = new GovernmentDialog([1,0,1], true).appendTo("body");
    //     v = new VoteDialog("Verunka", "Hrouda").appendTo("body");
    // }, 500);

}

class GameObject extends BaseObject {
    constructor(game) {
        super();
        this.phase = 'PH_START';
        this.info = new TopRow(
            "game: <span class=draw>" + g_gameid + "</span>, player: <span class=draw>" + g_playername + "</span>",
            new ButtonObject(
                "< BACK",
                ()=>AJAXgetLobby(g_gameid, g_playername, (r)=>{
                    this.destroy();
                    joinGame(g_gameid);
                }),
                "back"
            )).appendTo("body");
        this.board = new Board(
            game.board,
            game.modifiers,
            game.triggers).appendTo("body");
        this.instruction = new InfoRow(
            g_infotext[game.phase]).appendTo("body");
        this.players = new Players(game.players).appendTo("body");
        this.update(game);
    }
    update(game){
        this.board.update(game.board);
        console.log("updating game");
        this.players.update(game.players);
        if(game.phase != this.phase){
            this.players.setClickCallback(null);
            this.instruction.update(g_infotext[game.phase]);
            this.phase = game.phase;
            switch (game.phase) {
                case "PH_ELECT":
                    if(game.thisPlayer.isPresident){
                        this.players.setClickCallback(function(){
                            AJAXelect(g_gameid, g_playername, this.index, updateGame);
                        },
                        [...game.indexes.lastGovernment, ...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
                    break;

                case "PH_VOTE":
                    if(!game.thisPlayer.hasVoted && !game.thisPlayer.isDead){
                        let vote_dialog = new VoteDialog(
                            Object.keys(this.players.players)[game.indexes.president],
                            Object.keys(this.players.players)[game.indexes.chancellor]
                        ).appendTo("body");
                    }
                    break;

                case "PH_DRAW":
                    if(game.thisPlayer.isPresident){
                        this.board.drawPile.setClickCallback(()=>{
                            AJAXdraw(g_gameid, g_playername, (r)=>{
                                createGovernmentDialog(r, true)}
                            )
                        });
                    }
                    break;
                    
                case "PH_PASS":
                    this.board.drawPile.setClickCallback(null);
                    break;

                case "PH_ENFORCE":
                    if(game.thisPlayer.isChancellor){
                        new GovernmentDialog(game.thisPlayer.hand, false).appendTo("body");
                    };
                    break;

                case "PH_EXECUTE":
                    if(game.thisPlayer.isPresident){
                        this.players.setClickCallback(function(){
                            AJAXexecute(g_gameid, g_playername, this.index, updateGame);
                        },
                        [...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
    
                break;

                default:
                    break;
            }
        }
    }
    destroy(){
        $("body").empty();
    }
}