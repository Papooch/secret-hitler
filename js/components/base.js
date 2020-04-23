"use strict";

class GameObject {
    constructor() {
        this.el = $("<div></div>");
        this.clickCallback = null;
        this.cssCallbackClasses = "";
    }
    appendTo(domElement){
        $(domElement).append(this.el);
        return this;
    }
    setClickCallback(callback, cssclass){
        this.clickCallback = callback;
        let self = this;
        if (callback) {
            this.el.addClass("clickable " + cssclass);
            this.cssCallbackClasses += cssclass + " ";
            this.el.off("click");
            this.el.on("click", this.clickCallback.bind(self));
        } else {
            this.el.removeClass("clickable " + this.cssCallbackClasses);
            this.el.off("click");
        }
        return this;
    }

    update(){}
}


class DialogObject extends GameObject{
    constructor(prompt, items, name){
        super();
        this.prompt = prompt;
        this.items = [];
        this.name = name;
        this.el.addClass("dialog-wrapper opening ");
        this.el_dialog = $("<div></div>").addClass("dialog " + name);
        setTimeout(()=>this.el.removeClass("opening"), 300);
        // this.inner = $("<div></div>").addClass("dialog");
        this.el_prompt = $("<div></div>").addClass("dialog-prompt").text(prompt);
        this.el_items = $("<div></div>").addClass("dialog-items");
        this.el_dialog.append(this.el_items);
        this.el_dialog.append(this.el_prompt);
        this.el.append(this.el_dialog);
        if(!items) return;
        items.forEach(item => {
            item.el.on("click", ()=>this.close());
            this.items.push(item.appendTo(this.el_items));
        });
    }
    close(){
        this.el.addClass("closing");
        setTimeout(()=>this.el.remove(), 1000);
    }
}


class ButtonObject extends GameObject{
    constructor(text, callback, cssclass){
        super();
        this.text = text;
        this.el.addClass(cssclass);
        this.el.text(text);
        this.setClickCallback(callback);
    }
}