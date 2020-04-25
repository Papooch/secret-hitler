"use strict";

class VoteButton extends ButtonObject {
    constructor(vote, callback) {
        let text = vote ? "JA!" : "NEIN!";
        let cssclass = "vote-card " + (vote ? "ja" : "nein");
        super(text, callback, cssclass);
    }
}

class GovernmentDialog extends DialogObject {
    constructor(hand, isPresident) {
        let policies = [];
        let prompt = "Select policy to <span class=enforce>ENFORCE</span>";
        let type = "chancellor";
        if(isPresident){
            prompt = "select policy to <span class=chancellor>DISCARD</span>"
            type = "president";
        }
        for (let i = 0; i < hand.length; i++) {
            let policyCard = new PolicyCard(hand[i] ? "fascist" : "liberal");
            if(isPresident){
                policyCard.setClickCallback(function(){AJAXpass(g_gameid, g_playername, i, updateGame)});
            }else{
                policyCard.setClickCallback(function(){AJAXenforce(g_gameid, g_playername, i, updateGame)});
            }
            policies.push(policyCard);
        }
        super(prompt, policies, type);
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