<?php
require('fio.php');
require('private.php');

/* ============================================ */
/* ============= INTERFACE ACCESS ============= */
/* ============================================ */

// ========== LOBBY ACCESS ========== //

function createGame(string $game, string $player) : array {
    if(!createLobby($game, $player)){

    }


    return True;
}

function joinGame(string $game, string $player) : bool {

}

function leaveGame(string $game, string $player) : bool {

}

function startGame(string $game) {

}

// ========== GAME ACCESS ========== //

function getGame(string $game, $player=null) : array {
    $data = loadGameFile($game);
    return constructReturnObject($data, $player);
}


function selectChancellor(string $game, string $player, int $id) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not a president"];
    }
    if(isPresident($data, $data['players'][$id])) {
        return ["president cannot elect himself"];
    }
    if(isDead($data, $data['players'][$id])){
        return ["cannot select a dead player"];
    }
    if(wasLastGovernment($data, $id)) {
        return ["this player was in last government"];
    }
    if(in_array($id, $data['lastGovernment'])){
        return ["selected player was in last government"];
    }
    $data['chancellor'] = $id;

    setPhase($data, 'PH_VOTE'); //-----> PH_VOTE (everyone votes)

    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}

function vote(string $game, string $player, bool $vote) : array {
    $data = loadGameFile($game);
    if(!isPlayer($data, $player)){
        return ["not a player!"];
    }
    if($data['phase'] != 'PH_VOTE'){
        return ["not a voting phase"];
    }
    $data['voting'][$player] = $vote;
    if(hasEveryoneVoted($data)){
        if(votePassed($data)){
            setLastGovernment($data);
            setPhase($data, 'PH_DRAW'); //-----> PH_DRAW (president draws 3)
            if($data['modifiers']['fascistEndGame']){
                if(isChancellorHitler($data)){
                    setPhase($data, "PH_FASCISTS_WON"); //-----> PH_FASCISTS_WON
                } else {
                    markChancellorNotHitler($data);
                }
            } 
        } else {
            advanceElectionTrackerAndCheckChaos($data);
            if(!checkWinSituationAndSetPhase($data)){ // next phase is decided by the function
                resetLastGovernment($data);
                advancePresident($data);
                setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president chooses chancellor)
            }
        }
    }
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}


function draw3(string $game, string $player) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_DRAW'){
        return ["not a drawing phase"];
    }
    if(!empty($data['presidentsHand'])){
        discardPresidentsHand($data);
    }

    $hand = [];
    for($i=0; $i<3; $i++){
        if(empty($data['drawPile'])){
            shuffleDiscardIntoDraw($data);
        }
        $hand[] = array_pop($data['drawPile']);
    }
    $data['presidentsHand'] = $hand;

    setPhase($data, 'PH_PASS'); //-----> PH_PASS (president passes 2 to chancellor)

    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}

function pass2chancellor(string $game, string $player, int $discard) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_PASS'){
        return ["not a passing phase"];
    }
    if(empty($data['presidentsHand'])){
        return ["president has empty hand"];
    }
    if(!empty($data['chancellorsHand'])){
        discardChancellorsHand($data);
    }

    array_push($data['discardPile'], $data['presidentsHand'][$discard]); // append to discard
    unset($data['presidentsHand'][$discard]); // remove from hand
    $data['chancellorsHand'] = array_values($data['presidentsHand']); // pass rest to chancellor
    $data['presidentsHand'] = []; // empty presidents hand

    resetVeto($data);
    if ($data['modifiers']['vetoEnabled']) {
        setPhase($data, 'PH_VETO'); //-----> PH_VETO (president and chancellor can agree on veto)
    } else {
        setPhase($data, 'PH_ENFORCE'); //-----> PH_ENFORCE (chancellor enforces 1 policy)
    }

    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}

function veto(string $game, string $player, bool $wants) : array {
    $data = loadGameFile($game);
    if($data['phase'] != 'PH_VETO'){
        return ["not a vetoing phase"];
    }
    if (isPresident($data, $player)){
        $data['wantsVeto']['president'] = $wants;
    } elseif (isChancellor($data, $player)) {
        $data['wantsVeto']['chancellor'] = $wants;
    } else {
        return ["You are neither chancellor nor president"];
    }
    if(!$wants){
        setPhase($data, 'PH_ENFORCE');  //----> PH_ENFORCE (chancellor must enforce policy)
    }
    if(bothExpressedVetoOpinion($data)){
        if (bothWantVeto($data)) {
            advanceElectionTrackerAndCheckChaos($data);
            resetLastGovernment($data);
            discardChancellorsHand($data);
            advancePresident($data);
            setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president chooses chancellor)
        } else {
            setPhase($data, 'PH_ENFORCE'); //----> PH_ENFORCE (chancellor must enforce policy)
        }
    }
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}

function enforcePolicy(string $game, string $player, int $enforce) : array {
    $data = loadGameFile($game);
    if(!isChancellor($data, $player)){
        return ["player not chancellor"];
    }
    if($data['phase'] != 'PH_ENFORCE' && $data['phase'] != 'PH_VETO'){
        return ["not an enforcing phase"];
    }
    if(empty($data['chancellorsHand'])){
        return ["chancellor has empty hand"];
    }

    $policy = $data['chancellorsHand'][$enforce];
    enactPolicy($data, $policy);
    
    array_push($data['discardPile'], $data['chancellorsHand'][1-$enforce]); // put the other card to discard
    $data['chancellorsHand'] = []; // empty chancellors hand

    if ((!checkWinSituationAndSetPhase($data)) &&
        ($policy == 0 || !checkPowersAndSetPhase($data))) { // next phase decided by the functions
        setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (nothing triggered,president chooses chancellor)
        advancePresident($data);
    }

    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}

function selectPresident(string $game, string $player, int $id) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_SELECT_PRES'){
        return ["cannot select president now"];
    }
    if($data['president'] == $id) {
        return ["president cannot select himself as president again"];
    }
    if(isDead($data, $data['players'][$id])){
        return ["cannot select a dead player"];
    }
    resetLastGovernment($data);
    $data['modifiers']['temporalPresidency'] = true;
    $data['temporaryPresident'] = $id;
    setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (chosen president chooses chancellor)
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}


function investigate(string $game, string $player, int $id) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_INVESTIGATE'){
        return ["not investigating phase"];
    }
    if($data['president'] == $id) {
        return ["can't investigate yourself"];
    }
    if(isDead($data, $data['players'][$id])){
        return ["cannot investigate a dead player"];
    }

    array_push($data['knownIdentity'][$player], $id);

    advancePresident($data);
    setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president selects chancellor)
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}


function execute(string $game, string $player, int $id) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_EXECUTE'){
        return ["not executing phase"];
    }
    if($data['president'] == $id) {
        return ["can't execute yourself"];
    }
    if(isDead($data, $data['players'][$id])){
        return ["what is dead may never die"];
    }

    array_push($data['dead'], $id);

    
    if(isHitler($data, $data['players'][$id])){
        setPhase($data, 'PH_LIBERALS_WON'); //-----> PH_FASCISTS_WON (Hitler is killed)
    }else{
        advancePresident($data);
        setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president selects chancellor)
    }
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}


function peak(string $game, string $player) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_PEAK'){
        return ["not peaking phase"];
    }
    if(!empty($data['presidentsHand'])){
        return ["you are peeking!"];
    }

    $hand = [];
    for($i=0; $i<3; $i++){
        if(empty($data['drawPile'])){
            shuffleDiscardIntoDraw($data);
        }
        $hand[] = array_pop($data['drawPile']);
    }
    $data['presidentsHand'] = $hand;
    
    saveGameFile($game, $data);
    return constructReturnObject($data, $player);
}


function peakOk(string $game, string $player) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if($data['phase'] != 'PH_PEAK'){
        return ["not peaking phase"];
    }

    for($i=0; $i<3; $i++){
        array_push($data['drawPile'], array_pop($data['presidentsHand']));
    }

    advancePresident($data);
    setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president selects chancellor)
    saveGameFile($game, $data);
    return constructReturnObject($data, $player); 
}

?>