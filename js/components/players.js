"use strict";

class Player extends BaseObject {
    constructor(name, info, index){
        super();
        this.name = name;
        this.index = index;
        this.info = info;
        function breakLongName(n){
            if(n.length > 7){
                return n.substr(0, 6) + "&shy;" + breakLongName(n.substr(6));
            }
            return(n);
        }

        this.el_name = $("<div></div>").addClass("player-name").html(breakLongName(name));
        this.el_inner = $("<div></div>").addClass("player-inner")
            .append(this.el_name);
        if(name == g_playername){this.el_inner.addClass("player-you")}
        this.el_hand = $("<div></div>").addClass("player-hand");
        this.el_vote = $("<div></div>").addClass("player-vote");
        this.el
            .append(this.el_inner)
            .append(this.el_hand)
            .append(this.el_vote);
        this.update(this.info);
        }

    update(info) {
        // TODO: compare new and old info
        this.el[0].className = "player " + this.cssCallbackClasses; // remove all classes
        let classes = "";
        classes += TFN(info.isPresident, " president", "", "");
        classes += TFN(info.isChancellor, " chancellor", "", "");
        classes += TFN(info.isDead, " dead", "", "");
        classes += TFN(info.isHitler, " hitler", "", "");
        classes += TFN(info.isFascist, " fascist", " liberal", "");
        classes += TFN(info.hasVoted, " voted", "", "");
        classes += TFN(info.vote, " voted-ja", " voted-nein", "");
        classes += TFN(info.confirmedNotHitler, " not-hitler", "", "");
        this.el.addClass(classes);
        this.el_hand[0].className = "player-hand";
        if(info.handSize) this.el_hand.addClass("size size-" + String(info.handSize));
        return this;
    }
}

class Players extends BaseObject {
    constructor(players) {
        super();
        this.players = [];
        let i = 0;
        this.el.addClass("players");
        for (let [name, info] of Object.entries(players)){
            let p = new Player(name, info, i).appendTo(this.el);
            this.players[name] = p;
            i++;
        }
    }

    update(players){
        // TODO: some checking
        for (let [name, info] of Object.entries(players)){
            this.players[name].update(info);
        }
    }

    setClickCallback(callback, except=[]){
        let i = 0;
        for (let [name, info] of Object.entries(this.players)){
            this.players[name].setClickCallback(except.includes(i) ? null : callback);
            i++;
        }
    }
}