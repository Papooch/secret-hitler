"use strict";

// globl objects:
var game = {
    board : null,
    players : null,
}

var lobby = {
    games : null,
    players : null,
};

var chat = {
    coutn : null,
    messages : null,
}

// var playername = "verunka";
var gameid = "sample";
var v;
var b;
var l;
var g;

function main(){
    //#TODO: lobby
    
    createGameList();
    

    // AJAXgetGame(gameid, playername)
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
