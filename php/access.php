<?php
require('fio.php');

/*Phase:
PH_START
PH_ELECT
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
    do{
        $data['president']++;
        if($data['president'] >= count($data['players'])){
            $data['president'] = 0;
        }
    } while (isDead($data, $data['players'][$data['president']]));
    $data['modifiers']['temporalPresidency'] = false;
    resetVotes($data);
}

function resetVotes(array &$data) : void {
    $data['voting'] = [];
    foreach ($data['players'] as $player) {
        $data['voting'][$player] = null;
    }
}

function resetVeto(array &$data) : void {
    $data['wantsVeto']['president'] = null;
    $data['wantsVeto']['chancellor'] = null;
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
        revealTopPolicy($data);
        if(count($data['drawPile']) < 3){
            shuffleDiscardIntoDraw($data);
        }
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

function checkPowersAndSetPhase(array &$data) : bool {
    $fpolicies = $data['fascistPolicies'];
    $data['temporalPresidency'] = false;
    if (in_array($fpolicies, $data['triggersPowers']['selectPresidentAt'])){
        setPhase($data, 'PH_SELECT_PRES');  //-----> PH_SELECT_PRES (president chooses next president)
    } elseif (in_array($fpolicies, $data['triggersPowers']['investigateAt'])){
        setPhase($data, 'PH_INVESTIGATE'); //-----> PH_INVESTIGATE (president investigates player)
    } elseif (in_array($fpolicies, $data['triggersPowers']['peakAt'])){
        setPhase($data, 'PH_PEAK'); //-----> PH_PEAK (president checks top three policies)
    } elseif (in_array($fpolicies, $data['triggersPowers']['executeAt'])){
        setPhase($data, 'PH_EXECUTE'); //-----> PH_EXECUTE (president kills a player)
    } else {
        return false; // nothing triggered
    }
    return true;
    
}

function enactPolicy(array &$data, int $policy) : void {
    // which card was enforced
    if($policy == 0){
        $data['liberalPolicies']++;
    } else {
        $data['fascistPolicies']++;
    }
    resetElectionTracker($data);
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

function discardPresidentsHand(array &$data) : void {
    array_push($data['discardPile'], ...$data['presidentsHand']);
}

function discardChancellorsHand(array &$data) : void {
    array_push($data['discardPile'], ...$data['chancellorsHand']);
}

function shuffleDiscardIntoDraw(array &$data) : void {
    array_push($data['drawPile'], ...$data['discardPile']);
    $data['discardPile'] = [];
    shuffle($data['drawPile']);
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
        if(!isDead($data, $p) && !hasVoted($data, $p)){
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

function bothExpressedVetoOpinion(array $data) : bool {
    if($data['wantsVeto']['president'] == null) return false;
    if($data['wantsVeto']['chancellor'] == null) return false;
    return true;
}

function bothWantVeto(array $data) : bool {
    if($data['wantsVeto']['president'] && $data['wantsVeto']['chancellor']){
        return true;
    }
    return false;
}

function isChancellorHitler(array $data) : bool {
    return $data['chancellor'] == $data['hitler'];
}



// ============== RETURN OBJECT CONSTRUCTION =============== //

function getFascistPlayerRoles(array $data, string $player) : array {
    $ret['isHitler'] = isHitler($data, $player);
    $ret['isFascist'] = isFascist($data, $player);
    return $ret;
}


function getLiberalPlayerRoles(array $data, string $player) : array {
    $ret['isDead'] = isDead($data, $player);
    $ret['isPresident'] = isPresident($data, $player);
    $ret['isChancellor'] = isChancellor($data, $player);
    $ret['hasVoted'] = hasVoted($data, $player);
    return $ret;
}

function getPlayerRoles(array $data, string $player) : array {
    $ret = getLiberalPlayerRoles($data, $player);
    $ret = array_merge($ret, getFascistPlayerRoles($data, $player));
    return $ret;
}

function constructReturnObject(array $data, string $player) : array {
    $ret['phase'] = $data['phase'];

    if (!isPlayer($data, $player)){
        $player = "";
    } else {
        $ret['thisPlayer'] = getPlayerRoles($data, $player);
        $ret['thisPlayer']['vote'] =  $data['voting'][$player];
        $ret['thisPlayer']['hand'] = isPresident($data, $player)
                                    ? $data['presidentsHand']
                                    : $data['chancellorsHand'];
    }
    
    foreach ($data['players'] as $i => $p) {
        if($p == $player){
            $ret['players'][$p] = $ret['thisPlayer'];
            continue;
        }
        $ret['players'][$p] = getLiberalPlayerRoles($data, $p);
        $ret['players'][$p]['isFascist'] = null;
        $ret['players'][$p]['isHitler'] = null;
        $ret['players'][$p]['vote'] = null;
        if (!$player){
            continue;
        }
        if(in_array($i, $data['knownIdentity'][$player])){
            $ret['players'][$p]['isFascist'] = isFascist($data, $p);
        }
        if(isFascist($data, $player) && !isHitler($data, $player)){
            $ret['players'][$p]['isFascist'] = isFascist($data, $p);
            $ret['players'][$p]['isHitler'] = isFascist($data, $p);
        }
        if(isHitler($data, $player)){
            $ret['players'][$p]['isHitler'] = false;
            if($data['hitlerKnowsFascists']){
                $ret['players'][$p]['isFascist'] = isFascist($data, $p);
            }
        }
        if(hasEveryoneVoted($data)){
            $ret['players'][$p]['vote'] = $data['voting'][$p];
        }
    }
    $board['liberalPolicies'] = $data['liberalPolicies'];
    $board['fascistPolicies'] = $data['fascistPolicies'];
    $board['electionTracker'] = $data['electionTracker'];
    $board['drawPileSize'] = count($data['drawPile']);
    $board['discardPileSize'] = count($data['discardPile']);
    $ret['board'] = $board;
    $ret['modifiers'] = $data['modifiers'];
    $ret['triggers'] = array_merge($data['triggersPowers'], $data['triggersModifiers']);
    return $ret;
}

/* ============================================ */
/* ============= INTERFACE ACCESS ============= */
/* ============================================ */

function startGame(string $game) {

}



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
            resetLastGovernment($data);
            advancePresident($data);
            setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president chooses chancellor)
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

function enforcePolicy(string $game, string $player, int $enforce) : array{
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
    
    if ($policy == 0 || !checkPowersAndSetPhase($data)) { // next phase decided by the function
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

}

function investigateOK(string $game, string $player) : array {

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