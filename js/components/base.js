"use strict";

class BaseObject {
    constructor() {
        this.el = $("<div></div>");
        this.clickCallback = null;
        this.cssCallbackClasses = "";
    }
    appendTo(domElement){
        $(domElement).append(this.el);
        return this;
    }
    prependTo(domElement){
        $(domElement).prepend(this.el);
        return this;
    }
    setClickCallback(callback, cssclass=null){
        this.clickCallback = callback;
        let self = this;
        if (callback) {
            this.el.addClass("clickable " + (cssclass ? cssclass : ""));
            this.cssCallbackClasses += "clickable " + cssclass + " ";
            this.el.off("click");
            this.el.on("click", this.clickCallback.bind(self));
        } else {
            this.el.removeClass(this.cssCallbackClasses);
            this.el.off("click");
            this.cssCallbackClasses = "";
        }
        return this;
    }
    update(){}
    highlight(classname, duration){
        this.el.addClass(classname);
        setTimeout(()=>this.el.removeClass(classname), duration);
        return this;
    }
    destroy(timeout=0){
        setTimeout(()=>this.el.remove(), timeout);
        return this;
    }
}


class ButtonObject extends BaseObject{
    constructor(text, callback, cssclass){
        super();
        this.text = text;
        this.el_button_text = $("<div></div>").addClass("button-text").text(text);
        this.el.addClass("button " + cssclass);
        this.el.append(this.el_button_text);
        this.setClickCallback(callback);
    }
    setText(text) {
        this.text = text;
        this.el_button_text.text(text);
        return this;
    }
}


class DialogObject extends BaseObject{
    constructor(prompt, items, name, buttons=null){
        super();
        this.prompt = prompt;
        this.items = [];
        this.buttons = [];
        this.name = name;
        this.isOpen = true;
        this.el.addClass("dialog-wrapper opening ");
        this.el_dialog = $("<div></div>").addClass("dialog " + name);
        setTimeout(()=>this.el.removeClass("opening"), 300);
        // this.inner = $("<div></div>").addClass("dialog");
        this.el_prompt = $("<div></div>").addClass("dialog-prompt").html(prompt);
        if(items && items.length > 0){
            this.el_items = $("<div></div>").addClass("dialog-items");
            this.el_dialog.append(this.el_items);
            items.forEach(item => {
                item.el.on("click", ()=>this.close());
                this.items.push(item.appendTo(this.el_items));
            });
        }
        this.el_dialog.append(this.el_prompt);
        if(buttons && buttons.length > 0){;
            this.el_buttons = $("<div></div>").addClass("dialog-buttons");
            this.el_dialog.append(this.el_buttons);
            buttons.forEach(button => {
                button.el.on("click", ()=>this.close());
                this.buttons.push(button.appendTo(this.el_buttons));
            });
        }
        this.el.append(this.el_dialog);
    }
    close(timeout=0, callback=null){
        this.isOpen = false;
        setTimeout(()=>{
            if(callback) callback();
            this.el.addClass("closing");
            setTimeout(()=>this.el.remove(), 1000);
        }, timeout);
    }
}

class ListItem extends BaseObject {
    constructor(texts, cssclasses, number=null, button=null){
        super();
        this.el.addClass("list-item");
        this.el_number = $("<div></div>").addClass("item-number").text(number);
        this.el.append(this.el_number);
        this.text = texts;
        this.texts = [];
        texts.forEach((text, index) => {
            this.texts.push($("<span></span>").addClass("item-text " + cssclasses[index]).html(text).appendTo(this.el));
        });
        this.button = button;
        if(button){
            this.button.el.addClass("item-button").appendTo(this.el);
        }
    }
}

class ListObject extends BaseObject {
    constructor(items, cssclass, title=null, bottombuttons=null){
        super()
        this.el.addClass("list " + cssclass);
        this.el_title = $("<div></div>").addClass("list-title").html(title);
        this.el_list = $("<div></div>").addClass("list-items");
        this.el_buttons = $("<div></div>").addClass("list-bottom-buttons");
        this.el.append(this.el_title);
        this.el.append(this.el_list);
        this.el.append(this.el_buttons);
        this.items = [];
        this.buttons = [];
        items.forEach(item => {
            this.items.push(item.appendTo(this.el_list));
        });
        if(!bottombuttons) return;
        bottombuttons.forEach(button => {
            this.buttons.push(button.appendTo(this.el_buttons));
        });
    }
    update(items){
        this.el_list.empty();
        this.items = [];
        items.forEach(item => {
            this.items.push(item.appendTo(this.el_list));
        });
    }
    setClickCallback(callback, except=[]){
        for (let i = 0; i < this.items.length; i++) {
            this.items[i].setClickCallback(except.includes(i) ? null : callback);
        }
    }
}

class InputObject extends BaseObject {
    constructor(label, placeholder, button){
        super();
        this.el.addClass("input");
        this.el_label = $("<div></div>").addClass("input-label").html(label);
        this.el_input = $("<input class=textfield type='text' pattern='.{3,}' placeholder='" + placeholder + "'>");
        this.button = button;
        this.el
            .append(this.el_label)
            .append(this.el_input)
            .append(this.button.el);
    }
    getValue(){
        return this.el_input.val()
    }
    setValue(value){
        this.el_input.val(value);
    }
}