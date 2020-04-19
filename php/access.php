<?php
require('fio.php');

/*Phase:
PH_START
PH_CHOOSE_CHANC
PH_VOTE
PH_DRAW
PH_PASS
PH_VETO
PH_ENFORCE
PH_INVESTIGATE
PH_PEAK
PH_EXECUTE
PH_SELECT_PRES
PH_FASCISTS_WON
PH_LIBERALS_WON
*/


/* ============================================ */
/*= ============= PRIVATE HELPER ============== */
/* ============================================ */

// ========== IN PLACE MODIFIERS ========== //

function setPhase(array &$data, string $phase) : void {
    $data['phase'] = $phase;
}


function advancePresident(array &$data) : void {
    $data['president']++;
    if($data['president'] >= count($data['players'])){
        $data['president'] = 0;
    }
    $data['modifiers']['temporalPresidency'] = false;
    resetVotes($data);
}

function resetVotes(array &$data) : void {
    $data['voting'] = [];
    foreach ($data['players'] as $player) {
        $data['voting'][$player] = null;
    }
}

function setLastGovernment(array &$data) : void {
    resetLastGovernment($data);
    $data['lastGovernment'] = [$data['president'], $data['chancellor']];
}

function resetLastGovernment(array &$data) : void {
    $data['lastGovernment'] = [];
}

function advanceElectionTrackerAndCheckChaos(array &$data) : bool {
    $data['electionTracker']++;
    if($data['electionTracker'] > 2){
        resetElectionTracker($data);
        return true;
    }
    return false;
}

function resetElectionTracker(array &$data) : void {
    $data['electionTracker'] = 0;
}

function updateModifiers(array &$data) : void {
    if($data['fascistPolicies'] >= $data['triggersModifiers']['vetoEnabledAt']){
        $data['modifiers']['vetoEnabled'] = true;
    }
    if($data['fascistPolicies'] >= $data['triggersModifiers']['fascistEndGameAt']){
        $data['modifiers']['fascistEndGame'] = true;
    }
}

function enactPolicy(array &$data, int $policy) : void {
    // which card was enforced
    if($policy == 0){
        $data['liberalPolicies']++;
    } else {
        $data['fascistPolicies']++;
    }
    resetVotes($data);
    updateModifiers($data);
}


function revealTopPolicy(array &$data) : void {
    $policy = array_pop($data['drawPile']);
    enactPolicy($data, $policy);
}

function markChancellorNotHitler(array &$data) : void {
    $data['confirmedNotHitler'][] = &$data['chancellor'];
}


// ================== BOOL ================= //

function isGame(string $game) : bool {
    return True;
}


function isPresident(array $data, string $player) : bool {
    $pres = $data['players'][$data['president']];
    if($data['modifiers']['temporalPresidency']){
        $pres = $data['players'][$data['temporaryPresident']];
    }
    if($player == $pres){
        return true;
    }else{
        return false;
    }
}

function isChancellor(array $data, string $player) : bool {
    if($player == $data['players'][$data['chancellor']]){
        return true;
    }else{
        return false;
    }
}

function isHitler(array $data, string $player) : bool {
    if($player == $data['players'][$data['hitler']]){
        return true;
    }else{
        return false;
    }
}

function isFascist(array $data, string $player) : bool {
    foreach ($data['fascists'] as $id) {
        if($player == $data['players'][$id]){
            return true;
        }
    }
    return false;
}

function isDead(array $data, string $player) : bool {
    foreach ($data['dead'] as $id) {
        if($player == $data['players'][$id]){
            return true;
        }
    }
    return false;
}

function isPlayer(array $data, string $player) : bool {
    foreach ($data['players'] as $pl) {
        if($player == $pl){
            return true;
        }
    }
    return false;
}

function hasVoted(array $data, string $player) : bool {
    return $data['voting'][$player] !== null;
}

function hasEveryoneVoted(array $data) : bool {
    foreach ($data['players'] as $p){
        if(!hasVoted($data, $p)){
            return false;
        }
    }
    return true;
}

function wasLastGovernment(array $data, int $id) : bool {
    return in_array($id, $data['lastGovernment']);
}

function votePassed(array $data) : bool {
    $ja_count = 0;
    foreach ($data['voting'] as $name => $vote){
        if($vote == 1) $ja_count++;
    }
    if($ja_count/count($data['voting']) > 0.5 ){
        // vote passed
        return true;
    }
    return false;
}

function isChancellorHitler(array $data) : bool {
    return $data['chancellor'] == $data['hitler'];
}



// ================ OTHER ================== //

function addPlayerRoles(array $data, string $player){
    $data['flags']['isDead'] = isDead($data, $player);
    $data['flags']['isPresident'] = isPresident($data, $player);
    $data['flags']['isChancellor'] = isChancellor($data, $player);
    $data['flags']['isHitler'] = isHitler($data, $player);
    $data['flags']['isFascist'] = isFascist($data, $player);
    $data['flags']['hasVoted'] = hasVoted($data, $player);
    return $data;
}

/* ============================================ */
/* ============= INTERFACE ACCESS ============= */
/* ============================================ */

function startGame(string $game) {

}



function getGame(string $game, string $player=null) : array {
    $data = loadGameFile($game);
    if(isPlayer($data, $player)){
        $data = addPlayerRoles($data, $player);
    }
    return $data;
}


function selectChancellor(string $game, string $player, int $id) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not a president"];
    }
    if($data['president'] == $id) {
        return ["president cannot elect himself"];
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
    return addPlayerRoles($data, $player);
}

function vote(string $game, string $player, bool $vote) : array {
    $data = loadGameFile($game);
    if(!isPlayer($data, $player)){
        return ["not a player!"];
    }
    $data['voting'][$player] = $vote;
    if(hasEveryoneVoted($data)){
        if(votePassed($data)){
            resetElectionTracker($data);
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
            if(advanceElectionTrackerAndCheckChaos($data)){
                revealTopPolicy($data);
            }
            resetLastGovernment($data);
            advancePresident($data);
            setPhase($data, 'PH_CHOOSE_CHANC'); //-----> PH_CHOOSE_CHANC (next president chooses chancellor)

        }
    }
    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
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
        array_push($data['discardPile'], ...$data['presidentsHand']);
    }

    $hand = [];
    for($i=0; $i<3; $i++){
        if(empty($data['drawPile'])){
            $data['drawPile'] = $data['discardPile'];
            $data['discardPile'] = [];
            shuffle($data['drawPile']);
        }
        $hand[] = array_pop($data['drawPile']);
    }
    $data['presidentsHand'] = $hand;

    setPhase($data, 'PH_PASS'); //-----> PH_PASS (president passes 2 to chancellor)

    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}

function pass2chancellor(string $game, string $player, int $discard) : array {
    $data = loadGameFile($game);
    if(!isPresident($data, $player)){
        return ["player not president"];
    }
    if(empty($data['presidentsHand'])){
        return ["president has empty hand"];
    }
    if(!empty($data['chancellorsHand'])){
        array_push($data['discardPile'], ...$data['chancellorsHand']);
    }

    array_push($data['discardPile'], $data['presidentsHand'][$discard]); // append to discard
    unset($data['presidentsHand'][$discard]); // remove from hand
    $data['chancellorsHand'] = array_values($data['presidentsHand']); // pass rest to chancellor
    $data['presidentsHand'] = []; // empty presidents hand

    # TODO: Check if veto unlocked
        # yes -> PH_VETO
    
    setPhase($data, 'PH_ENFORCE'); //-----> PH_ENFORCE (chancellor enforces 1 policy)

    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}

function veto(string $game, string $player, bool $wants) : array {
    # TODO:
}

function enforcePolicy($game, $player, $enforce) {
    $data = loadGameFile($game);
    if(!isChancellor($data, $player)){
        return ["player not chancellor"];
    }
    if(empty($data['chancellorsHand'])){
        return ["chancellor has empty hand"];
    }

    enactPolicy($data, $data['chancellorsHand'][$enforce]);

    array_push($data['discardPile'], $data['chancellorsHand'][1-$enforce]); // put the other card to discard
    $data['chancellorsHand'] = []; // empty chancellors hand

    #TODO: Check if effect triggered
        # yes -> # PH_INVESTIGATE
                 # PH_PEAK
                 # PH_EXECUTE
                 # PH_SELECT_PRES
                 # unlockVeto
                 # activateEndgame
                 #

    advancePresident($data);
    setPhase($data, 'PH_CHOOSE_CHANC'); //-----> PH_CHOOSE_CHANC (president chooses chancellor)
    saveGameFile($game, $data);
    return addPlayerRoles($data, $player);
}


function createGame(string $game) : bool {
    if (is_dir(SH_GAME_DIR.gameHash($game))) return False;
    mkdir(SH_GAME_DIR.$game);
    return True;
}

function addPlayer(string $game, string $player) : bool {

}

function removePlayer(string $game, string $player) : bool {

}

?>