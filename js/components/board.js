"use strict";

class PolicyCard extends BaseObject {
    constructor(type) {
        super();
        this.type = type
        this.el.addClass("policy " + type);
    }

    update(type) {
        if (type != this.type) {
            //this.highlight();
        }
        this.el.removeClass(this.type).addClass(type);
        this.type = type;
        return this;
    }
}

class Pile extends PolicyCard {
    constructor(size, isDraw=false) {
        super("face-down");
        this.size = size;
        this.isDraw = isDraw;
        this.el.addClass("pile" + (isDraw ? " draw" : ""));
        this.el_size = $("<div></div>").addClass("pile-size").text(size);
        this.el.append(this.el_size);
    }

    update(size) {
        if (size != this.size) {
            this.highlight("clickable", 200);
        }
        this.size = size;
        this.el_size.text(this.size);
        return this;
    }
}

class PolicyRow extends BaseObject {
    constructor(total, count, type, triggers=null){
        super();
        this.total = total;
        this.type = type;
        this.count = count;
        this.el.addClass("policy-row " + type);
        this.triggers = triggers;
        this.update(count);
    }

    update(count){
        let flash = false;
        if(count > this.count){
            flash = true;
        }
        this.count = count;
        this.el.empty();
        for (let i = 0; i < this.total; i++) {
            let card = new PolicyCard((i < count ) ? this.type : 'empty', );
            if(this.triggers){
                let triggertxt = "";
                if(this.triggers.selectPresidentAt.includes(i+1)) triggertxt += " trigger-select";
                if(this.triggers.investigateAt.includes(i+1)) triggertxt += " trigger-investigate";
                if(this.triggers.peakAt.includes(i+1)) triggertxt += " trigger-peak";
                if(this.triggers.executeAt.includes(i+1)) triggertxt += " trigger-execute";
                if(this.triggers.vetoEnabledAt == (i+1)) triggertxt += " trigger-veto";
                card.el.addClass(triggertxt);
            }
            if((i == count-1) && flash){card.highlight("flash", 100)};
            this.el.append(card.el);        
        }
        return this;
    }

}

class ElectionTracker extends BaseObject {
    constructor(position=0) {
        super();
        this.position = position;
        this.el.addClass("election-tracker");
        // FIXME: fucking refactor this for gods sake!
        this.places = [
            $("<div></div>").addClass("tracker-place"),
            $("<div></div>").addClass("tracker-place"),
            $("<div></div>").addClass("tracker-place"),
        ];
        this.el.append(this.places[0]).append(this.places[1]).append(this.places[2]);        
        this.update(this.position);
    }

    update(position){
        if(position != this.position){
            // TODO: highlight
        }
        let i=0;
        this.places.forEach(
            place => {
                if(i==position){
                    place.addClass("tracker");
                }else{
                    place.removeClass("tracker");
                }
                i++;
            }
        );
        return this;
    }
}

class Board extends BaseObject {
    constructor(board, modifiers=null, triggers=null){
        super();
        this.board = board;
        this.modifiers = modifiers;
        this.triggers = triggers;
        this.drawPile = new Pile(board.drawPileSize, true);
        this.discardPile = new Pile(board.discardPileSize);
        this.liberalPolicies = new PolicyRow(
            triggers.liberalsWinAt,
            board.liberalPolicies,
            'liberal'
        );
        this.fascistPolicies = new PolicyRow(
            triggers.fascistsWinAt,
            board.fascistPolicies,
            'fascist',
            triggers
        );
        this.electionTracker = new ElectionTracker(board.electionTracker);
        this.el_middle_pane = $("<div></div>").addClass("middle");
        this.el_middle_pane
            .append(this.liberalPolicies.el)
            .append(this.electionTracker.el)
            .append(this.fascistPolicies.el);
        this.el
            .append(this.drawPile.el)
            .append(this.el_middle_pane)
            .append(this.discardPile.el);
        this.el.addClass("board");
    }

    update(board) {
        if(JSON.stringify(board) == JSON.stringify(this.board)){
            return this; // no update needed;
        }
        this.board = board;
        this.drawPile.update(board.drawPileSize);
        this.discardPile.update(board.discardPileSize);
        this.liberalPolicies.update(board.liberalPolicies);
        this.fascistPolicies.update(board.fascistPolicies);
        this.electionTracker.update(board.electionTracker);
        return this;
    }
}


///---- debug
var pl;
var fpol;
var b;

function update_board(r){
    b.update(r.payload.board);
}

function debug_draw_game(r){
    console.log(r);
    let p = r.payload;
    b = new Board(p.board, p.modifiers, p.triggers).appendTo("body");
    pl = new Players(r.payload.players).appendTo("body");
};


function drawError(error) {
    $("body").empty();
    $("body").append("ERROR: " + error);
}