"use strict";

class GameObject {
    constructor() {
        this.parent = null;
        this.el = $("<div></div>");
        this.clickCallback = null;
    }
    appendTo(domElement){
        $(domElement).append(this.el);
        return this;
    }
    setClickCallback(callback){
        this.clickCallback = callback;
        let self = this;
        if (callback) {
            this.el.addClass("clickable");
            this.el.off("click");
            this.el.on("click", this.clickCallback.bind(self));
        } else {
            this.el.removeClass("clickable");
            this.el.off("click");
        }
        return this;
    }
    update(){}
}


class DialogObject {
    constructor(){

    }
}