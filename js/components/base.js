"use strict";

class GameObject {
    constructor() {
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


class DialogObject extends GameObject{
    constructor(prompt, items, name){
        super();
        this.el.addClass("dialog " + name);
        // this.inner = $("<div></div>").addClass("dialog");
        this.el_prompt = $("<div></div>").addClass("dialog-prompt").text(prompt);
        this.el.append(this.el_prompt);
        this.items = [];
        if(!items) return;
        items.forEach(item => {
            item.el.on("click", ()=>this.close());
            this.items.push(item.appendTo(this.el));
        });
    }
    close(){
        this.el.remove();
    }
}


class ButtonObject extends GameObject{
    constructor(text, callback, cssclass){
        super();
        this.el.addClass(cssclass);
        this.text = text;
        this.setClickCallback(callback);
    }
}

//#TODO: Merge with ButtonObject
class VoteCard extends GameObject {
    constructor(text) {
        super();
        this.text = text
        this.el.addClass("vote-card " + text).text(text.toUpperCase()+"!");
    }
}