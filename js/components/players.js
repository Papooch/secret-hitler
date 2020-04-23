"use strict";

class Player extends GameObject {
    constructor(name, info, index){
        super();
        this.name = name;
        this.index = index;
        this.info = info;
        this.update(this.info);
        this.el_name = $("<div></div>").addClass("player-name").text(name);
        this.el_inner = $("<div></div>").addClass("player-inner")
            .append(this.el_name);
        this.el_confirmed = $("<div></div>").addClass("player-confirmed");
        this.el_vote = $("<div></div>").addClass("player-vote");
        this.el
            .append(this.el_inner)
            .append(this.el_confirmed)
            .append(this.el_vote);
        }

    update(info, callback=null) {
        // TODO: compare new and old info
        this.el[0].className = ""; // remove all classes
        let classes = "player";
        classes += TFN(info.isPresident, " president", "", "");
        classes += TFN(info.isChancellor, " chancellor", "", "");
        classes += TFN(info.isDead, " dead", "", "");
        classes += TFN(info.isHitler, " hitler", "", "");
        classes += TFN(info.isFascist, " fascist", " liberal", "");
        classes += TFN(info.hasVoted, " voted", "", "");
        classes += TFN(info.vote, " voted-ja", " voted-nein", "");
        classes += TFN(info.confirmedNotHitler, " not-hitler", "", "");
        this.el.addClass(classes);
        if (callback){
            this.setClickCallback(callback);
        } else {
            this.setClickCallback(null);
        }
        return this;
    }
}

class Players extends GameObject {
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

    update(players, callback=null){
        // TODO: some checking
        for (let [name, info] of Object.entries(players)){
            this.players[name].update(info, callback);
        }
    }

    setClickCallback(callback){
        for (let [name, info] of Object.entries(this.players)){
            this.players[name].setClickCallback(callback);
        }
    }
}