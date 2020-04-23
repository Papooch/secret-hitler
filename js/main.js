"use strict";

// globl objects:
var game = {
    board : null,
    players : null,
}

var lobby = {
    players : null,
}

var chat = {
    coutn : null,
    messages : null,
}

var playername = "verunka";
var gameid = "sample";

function main(){
    // #TODO: lobby
    
    AJAXgetGame(gameid, playername)
    .then((r)=>{
        let p = r.payload;
        game.board = new Board(p.board, p.modifiers, p.triggers).appendTo("body");
        game.players = new Players(p.players).appendTo("body");
    });

}
