
class VoteCard extends GameObject {
    constructor(text) {
        super();
        this.text = text
        this.el.addClass("vote-card " + text).text(text.toUpperCase()+"!");
    }
}


class GovernmentDialog extends DialogObject {
    constructor(hand, isPresident) {
        let policies = [];
        this.prompt = "Select policy to ENFORCE";
        this.type = "chancellor";
        if(isPresident){
            this.prompt = "select policy to DISCARD"
            this.type = "president";
        }
        for (let i = 0; i < hand.length; i++) {
            let policyCard = new PolicyCard(hand[i] ? "fascist" : "liberal");
            if(isPresident){
                policyCard.setClickCallback(function(){console.log(playername, "discarding", i, "(", this.type, ")")});
            }else{
                policyCard.setClickCallback(function(){console.log(playername, "enforcing", i, "(", this.type, ")")});
            }
            policies.push(policyCard);
        }
        super(prompt, policies, this.type);
    }   
}

class VoteDialog extends DialogObject {
    constructor(president, chancellor) {
        let jaCard = new VoteCard("ja").setClickCallback(function(){console.log("voting JA for " + president + " and " + chancellor)});
        let neinCard = new VoteCard("nein").setClickCallback(function(){console.log("voting NEIN for " + president + " and " + chancellor)});
        super("Vote for " + president + " (as president), and " + chancellor + " (as chancellor).",
        [jaCard, neinCard], 'vote');
    }
}