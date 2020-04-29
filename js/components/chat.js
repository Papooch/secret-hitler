"use trict";

class ChatMessage extends BaseObject {
    constructor(message){
        super();
        this.time = message.time;
        this.player = message.player;
        this.text = message.message;
        this.el.addClass("message");
        if(message.player == g_playername){
            this.el.addClass("this-player");
        }
        this.el_time = $("<div></div>").addClass("message-time")
            .html(this.time.substr(11,6));
        this.el_player = $("<div></div>").addClass("message-player")
            .html(this.player
                .replace(/\[(.+?)\]/g, "<span class=system>$1</span>") // [] -> custom
            );
        this.el_text = $("<div></div>").addClass("message-text")
            .html(this.text
                    .replace(/\*(.+?)\*/g, "<big>$1</big>") // * -> big
                    .replace(/\~(.+?)\~/g, "<s>$1</s>") // * -> strikethrough
                    .replace(/_(.+?)_/g, "<i>$1</i>") // _ -> italic
                    .replace(/j\{([^\{]+?)}/g, "<span class=passed>$1</span>") // j{}
                    .replace(/n\{([^\{]+?)}/g, "<span class=failed>$1</span>") // n{}
                    .replace(/p\{([^\{]+?)}/g, "<span class=president>$1</span>") // p{}
                    .replace(/c\{([^\{]+?)}/g, "<span class=chancellor>$1</span>") // c{}
                    .replace(/f\{([^\{]+?)}/g, "<span class=fascist>$1</span>") // f{}
                    .replace(/l\{([^\{]+?)}/g, "<span class=liberal>$1</span>") // l{}
                    .replace(/h\{([^\{]+?)}/g, "<span class=highlight>$1</span>") // l{}
            );
        this.el.append(this.el_time)
                .append(this.el_player)
                .append(this.el_text);
    }
}

class ChatBox extends BaseObject {
    constructor(chat){
        super();
        this.message_count = 1;
        this.el.addClass("chat");
        this.messages = [];
        this.el_messages = $("<div></div>").addClass("messages");
        this.input = new InputObject("",
            "Your message",
            new ButtonObject("Send", null, "send"),
        );
        let sendCallback = ()=>{
            let msg = this.input.getValue();
            this.input.setValue();
            if(msg.length == 0) return;
            AJAXmessage(g_gameid, g_playername, msg, updateChat)
        }
        this.input.button.setClickCallback(sendCallback);
        this.input.el_input.keyup((e)=>{
            if(e.key == "Enter"){
                sendCallback()
            };
        })
        this.el.append(this.input.el);
        this.el.append(this.el_messages);
        this.update(chat, false);
    }

    update(chat, highlight=true){
        let chat_length = chat.length;
        if(chat_length > this.message_count){
            for (let i = this.message_count; i < chat_length; i++) {
                if(i!=0){ // do not include the first message
                    this.addMessage(chat[i], highlight);
                }
            }
        } else if (chat_length < this.message_count) {
            this.el_messages.empty();
            this.message_count = 1;
            this.update(chat, false);
        }
    }

    addMessage(message, highlight=true){
        let msg = new ChatMessage(message);
        if(highlight) msg.highlight('highlight', 500);
        this.messages.push(msg.prependTo(this.el_messages));
        this.message_count++;
        this.scrollDown();
    }

    scrollDown(){
        this.el_messages.scrollTop(0);
    }
}
