"use strict";

class InfoRow extends BaseObject{
    constructor(text){
        super();
        this.text = text;
        this.el = $("<div></div>");
        this.el_text = $("<div></div>").addClass('info-text').html(text);
        this.el.addClass('info-row');
        this.el.append(this.el_text);
    }
    update(text){
        this.text = text;
        this.el_text.html(text);
    }
}

class TopRow extends InfoRow{
    constructor(text, backbutton){
        super(text);
        this.el.addClass('top');
        this.button = backbutton;
        this.el.prepend(this.button.el);
    }
}