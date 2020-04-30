"use strict";

// globl objects:
var g_game = {
    json : null,
    info : null,
    board : null,
    instruction: null,
    players : null,
}

var g_lobby = {
    games : null,
    players : null,
};

var g_chat = null;

var g_errordialog = new ErrorDialog().close();

function main(){
    AJAXgetGames(createGameList);
}

class GameObject extends BaseObject {
    constructor(game) {
        super();
        this.json = game;
        this.phase = 'PH_START';
        let topRowPlayerInfo = "";
        if(!Object.keys(game.players).includes(g_playername)){
            topRowPlayerInfo = "(spectator)";
        } else {
            /* This has to update when the game is restarted also */
            // if(game.thisPlayer.isFascist){
            //     topRowPlayerInfo = "<span class=fascist>fascist</span>"
            // }else{
            //     topRowPlayerInfo = "<span class=liberal>liberal</span>"
            // }
            // if(game.thisPlayer.isHitler){
            //     topRowPlayerInfo = "<span class=highlight>Hitler</span>"
            // }
        }

        this.info = new TopRow(
            "game: <span class=draw>" + g_gameid + "</span>, player: <span class=draw>" + g_playername + "</span> " + topRowPlayerInfo,
            new ButtonObject(
                "< BACK",
                ()=>AJAXgetLobby(g_gameid, g_playername, (r)=>{
                    this.destroy();
                    joinGame(g_gameid);
                }),
                "back"
            ))
        this.board = new Board(
            game.board,
            game.modifiers,
            game.triggers)
        this.instruction = new InfoRow(
            g_infotext[game.phase])
        this.players = new Players(game.players)
        this.dialog = new DialogObject(null, null, null, null);
        this.update(game);
        this.players.prependTo("body");
        this.instruction.prependTo("body");
        this.board.prependTo("body");
        this.info.prependTo("body");
        g_chat.scrollDown();
    }
    update(game){
        this.json = game;
        this.board.update(game.board);
        this.players.update(game.players);
        if(game.phase != this.phase || game.phase == "PH_PEAK"){
            this.phase = game.phase;
            this.players.setClickCallback(null);
            this.instruction.update(g_infotext[game.phase]);
            if(!Object.keys(game.players).includes(g_playername)) return;
            switch (game.phase) {
                case "PH_ELECT":
                    this.dialog.close();
                    if(game.thisPlayer.isPresident){
                        this.players.setClickCallback(function(){
                            new ConfirmDialog(
                                "Are you sure you want to elect <span class=chancellor>" + this.name + "</span> as a chancellor?",
                                ()=>AJAXelect(g_gameid, g_playername, this.index, updateGame)
                            ).appendTo("body");
                        },
                        [...game.indexes.lastGovernment, ...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
                    break;

                case "PH_VOTE":
                    if(!game.thisPlayer.hasVoted && !game.thisPlayer.isDead){
                        this.dialog = new VoteDialog(
                            Object.keys(this.players.players)[game.indexes.president],
                            Object.keys(this.players.players)[game.indexes.chancellor]
                        ).appendTo("body");
                    }
                    break;

                case "PH_DRAW":
                    if(game.thisPlayer.isPresident){
                        this.board.drawPile.setClickCallback(()=>{
                            AJAXdraw(g_gameid, g_playername, updateGame);
                        });
                    }
                    break;
                    
                case "PH_PASS":
                    if(game.thisPlayer.isPresident){
                        this.dialog.close();
                        this.dialog = new GovernmentDialog(game.thisPlayer.hand, true).appendTo("body");
                    };
                    this.board.drawPile.setClickCallback(null);
                    break;

                case "PH_VETO":
                    this.dialog.close();
                    if (game.thisPlayer.isPresident){
                        this.dialog = new GovernmentDialog(null, true, true).appendTo("body");
                    } else if (game.thisPlayer.isChancellor){
                        this.dialog = new GovernmentDialog(game.thisPlayer.hand, false, true).appendTo("body");
                    }
                    break;

                case "PH_ENFORCE":
                    this.dialog.close();
                    if(game.thisPlayer.isChancellor){
                        this.dialog.close();
                        this.dialog = new GovernmentDialog(game.thisPlayer.hand, false).appendTo("body");
                    };
                    break;

                case "PH_INVESTIGATE":
                    if(game.thisPlayer.isPresident){
                        this.players.setClickCallback(function(){
                            new ConfirmDialog(
                                "Are you sure you want to investigate <span class=highlight>" + this.name + "</span>'s identity?",
                                ()=>AJAXinvestigate(g_gameid, g_playername, this.index, updateGame)
                            ).appendTo("body");
                        },
                        [...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
                    break;

                case "PH_PEAK":
                    if(game.thisPlayer.isPresident){
                        if(game.thisPlayer.hand.length > 0 
                            && this.dialog.name != "peak") {
                            this.dialog = new PeakDialog(game.thisPlayer.hand).appendTo("body");
                        } else {
                            this.board.drawPile.setClickCallback(()=>{
                                AJAXpeak(g_gameid, g_playername, updateGame);
                            });
                        }
                    }
                    break;

                case "PH_SELECT_PRES":
                    if(game.thisPlayer.isPresident){
                        
                        this.players.setClickCallback(function(){
                            new ConfirmDialog(
                                "Are you sure you want to elect <span class=president>" + this.name + "</span> as the next president?",
                                ()=>AJAXselectPres(g_gameid, g_playername, this.index, updateGame)
                            ).appendTo("body");
                        },
                        [...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
                    break;
            
                case "PH_EXECUTE":
                    if(game.thisPlayer.isPresident){
                        this.players.setClickCallback(function(){
                            new ConfirmDialog(
                                "Are you sure you want execute <span class=highlight>" + this.name + "</span>?",
                                ()=>AJAXexecute(g_gameid, g_playername, this.index, updateGame)
                            ).appendTo("body");
                        },
                        [...game.indexes.deadPlayers, game.indexes.thisPlayer]);
                    }
    
                break;

                case "PH_FASCISTS_WON":
                    $("body").addClass("fascists-won");
                    clearInterval(refresher);
                    break;
                    
                    case "PH_LIBERALS_WON":
                    $("body").addClass("liberals-won");
                    clearInterval(refresher);
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