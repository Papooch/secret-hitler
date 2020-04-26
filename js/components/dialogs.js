"use strict";

class VoteButton extends ButtonObject {
    constructor(vote, callback) {
        let text = vote ? "JA!" : "NEIN!";
        let cssclass = "vote-card " + (vote ? "ja" : "nein");
        super(text, callback, cssclass);
    }
}

class GovernmentDialog extends DialogObject {
    constructor(hand, isPresident, isVeto) {
        let policies = [];
        let vetoButtons = [];
        let prompt = "Select policy to <span class=enforce>ENFORCE</span>" +
                    (isVeto ? ", or:" : "");
        let type = "chancellor";
        if(isPresident){
            prompt = "select policy to <span class=chancellor>DISCARD</span>"
            type = "president";
            if(isVeto){
                prompt = "Do you want to <span class=highlight>VETO</span> this policy?";
            }
        }
        if(hand){
            for (let i = 0; i < hand.length; i++) {
                let policyCard = new PolicyCard(hand[i] ? "fascist" : "liberal");
                if(isPresident){
                    policyCard.setClickCallback(function(){AJAXpass(g_gameid, g_playername, i, updateGame)});
                }else{
                    policyCard.setClickCallback(function(){AJAXenforce(g_gameid, g_playername, i, updateGame)});
                }
                policies.push(policyCard);
            }
        }
        if(isVeto){
            vetoButtons.push(
                new VoteButton(
                    true,
                    ()=>AJAXveto(g_gameid, g_playername, true, updateGame)
                    ).setText("I want veto!"),
            )
            vetoButtons.push(
                new VoteButton(
                    false,
                    ()=>AJAXveto(g_gameid, g_playername, false, updateGame)
                    ).setText("I don't want veto!"),
            )
        }
        super(prompt, policies, type, vetoButtons);
    }   
}

class VoteDialog extends DialogObject {
    constructor(president, chancellor) {
        let jaCard = new VoteButton(true, function(){AJAXvote(g_gameid, g_playername, true, updateGame)});
        let neinCard = new VoteButton(false, function(){AJAXvote(g_gameid, g_playername, false, updateGame)});
        super("Vote for <span class=president>" + president + "</span>, and <span class=chancellor>" + chancellor + "</span>.",
        [jaCard, neinCard], 'vote');
    }
}

class PeakDialog extends DialogObject {
    constructor(hand) {
        let policies = [];
        hand.forEach(card => {
            let policyCard = new PolicyCard(card ? "fascist" : "liberal");
            policies.push(policyCard);
        })
        let ok_button = new ButtonObject(
            "Put back",
            ()=>{this.close(), AJAXpeakOk(g_gameid, g_playername, updateGame)},
            'confirm'
        );
        super("These are the top 3 cards", policies, 'peak', [ok_button]);
        //remove click callback from cards
        this.items.forEach(item => {
            item.setClickCallback(null);
        })
    }
}

class ErrorDialog extends DialogObject {
    constructor(errortext,  callback=null) {
        let ok_button = new ButtonObject("Alright...", ()=>this.close(0, callback), 'error');
        super("<span class=highlight>Error</span>: " + errortext, [], 'error', [ok_button]);
    }
}