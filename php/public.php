<?php
require('fio.php');
require('private.php');

/* ============================================ */
/* ============= INTERFACE ACCESS ============= */
/* ============================================ */

// ========== LOBBY ACCESS ========== //

function getLobby(string $game, string $player) : array {
    if(!isGame($game)){
        global $response; $response['status'] = "rnok";
        return [$game." does not exist"];
    }
    $lobby = loadLobbyFile($game);
    return constructReturnObjectLobby($lobby, $player);
}

function createGame(string $game, string $player) : array {
    if(!createLobby($game, $player)){
        return ["cannot create game"];
    }
    return getGames();
}

function deleteGame(string $game, string $player) : array {
    $lobby = loadLobbyFile($game);
    if(!isAdmin($player) && !isCreator($lobby, $player)) {
        return ["you cannot do that!"];
    }
    deleteGameFiles($game);
    return getGames();
}

function joinGame(string $game, string $player) : array {
    $lobby = loadLobbyFile($game);
    if(!in_array($player, array_keys($lobby['players']))){
        if(count($lobby['players']) >= 10){
            return ['There are already 10 players in the lobby.'];
        }
        addChatMessageStatus($lobby, "j{".$player."} has joined");
        $lobby['players'][$player] = false;
        saveLobbyFile($game, $lobby);
    }
    return constructReturnObjectLobby($lobby, $player);
}

function leaveGame(string $game, string $player) : array {
    $lobby = loadLobbyFile($game);
    unset($lobby['players'][$player]);
    saveLobbyFile($game, $lobby);
    addChatMessageStatus($lobby, "n{".$player."} has left");
    return getGames();
}


function kickPlayer(string $game, string $player, string $kick) : array {
    $lobby = loadLobbyFile($game);
    if(!isCreator($lobby, $player)){
        return ["You can't kick players"];
    }
    unset($lobby['players'][$kick]);
    saveLobbyFile($game, $lobby);
    return constructReturnObjectLobby($lobby, $player);
}

function ready(string $game, string $player, bool $ready) : array {
    $lobby = loadLobbyFile($game);
    $lobby['players'][$player] = $ready;
    saveLobbyFile($game, $lobby);
    return constructReturnObjectLobby($lobby, $player);
}

function startGame(string $game, string $player) {
    $lobby = loadLobbyFile($game);
    if(count($lobby['players']) < 5) {
        global $response; $response['status'] = "nok";
        return ["At least 5 players are needed!"];
    }
    if(!isEveryoneReady($lobby)){
        global $response; $response['status'] = "nok";
        return ["Some players are not ready!"];
    }
    $data = createGameData($game, array_keys($lobby['players']));
    clearChat($data);
    addChatMessageStatus($data,
        "p{The game has started}! There are f{".count($data['fascists']).
        " fascists, including hitler} who ".
        ($data['hitlerKnowsFascists'] ? "j{knows}" : "j{does not know}").
        "who they are."
    );
    
    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player); 
}


// ========== CHAT ACCESS ============ //

function postMessage(string $game, string $player, string $message, bool $isGame=true) : array {
    if(addChatMessage($game, $player, $message)){
        return getChat($game);
    }else{
        return ['could not post mesaage'];
    }
}

function getChat(string $game) : array {
    global $response; $response['status'] = "ok";
    return loadChatFile($game);
}


// ========== GAME ACCESS ========== //

function getGames() : array {
    global $response; $response['status'] = "ok";
    return getGameFileList();
}

function getGame(string $game, $player=null) : array {
    if(!isGame($game)){
        global $response; $response['status'] = "rnok";
        return [$game." does not exist"];
    }
    $data = loadGameFile($game);
    return constructReturnObjectGame($data, $player);
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
    resetVotes($data);
    addChatMessageStatus($data,
       "The president wants to elect c{".$data['players'][$id]."} as a chancellor.");
    setPhase($data, 'PH_VOTE'); //-----> PH_VOTE (everyone votes)

    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player);
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
            addChatMessageStatus($data, "The vote j{passed}!");
            setLastGovernment($data);
            setPhase($data, 'PH_DRAW'); //-----> PH_DRAW (president draws 3)
            if($data['modifiers']['fascistEndGame']){
                if(isChancellorHitler($data)){
                    addChatMessageStatus($data,
                        "Hitler was elected as a chancellor. f{Fascists won}!");
                    setPhase($data, "PH_FASCISTS_WON"); //-----> PH_FASCISTS_WON
                } else {
                    markChancellorNotHitler($data);
                    addChatMessageStatus($data,
                        $data['players'][$data['chancellor']]." is h{not hitler}!");
                }
            } 
        } else {
            addChatMessageStatus($data, "The vote n{failed}!");
            advanceElectionTrackerAndCheckChaos($data);
            if(!checkWinSituationAndSetPhase($data)){ // next phase is decided by the function
                resetLastGovernment($data);
                advancePresident($data);
                setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president chooses chancellor)
            }
        }
    }
    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player);
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
    return constructReturnObjectGame($data, $player);
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
    return constructReturnObjectGame($data, $player);
}

function veto(string $game, string $player, bool $wants) : array {
    $data = loadGameFile($game);
    if($data['phase'] != 'PH_VETO'){
        return ["not a vetoing phase"];
    }
    if (isPresident($data, $player)){
        $data['wantsVeto']['president'] = $wants;
        addChatMessageStatus($data, "The p{president} ".($wants?"j{wants":"n{does not want")." veto}");
    } elseif (isChancellor($data, $player)) {
        $data['wantsVeto']['chancellor'] = $wants;
        addChatMessageStatus($data, "The c{chancellor} ".($wants?"j{wants":"n{does not want")." veto}");
    } else {
        return ["You are neither chancellor nor president"];
    }
    if(!$wants){
        setPhase($data, 'PH_ENFORCE');  //----> PH_ENFORCE (chancellor must enforce policy)
    }
    if(bothExpressedVetoOpinion($data)){
        if (bothWantVeto($data)) {
            addChatMessageStatus($data, "The government has j{agreed on a veto}.");
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
    return constructReturnObjectGame($data, $player);
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
    return constructReturnObjectGame($data, $player);
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
    $data['modifiers']['temporalPresidency'] = true;
    $data['temporaryPresident'] = $id;
    $data['chancellor'] = $id;
    setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (chosen president chooses chancellor)
    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player);
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

    addChatMessageStatus($data,
        "The p{president} now knows who j{".$data['players'][$id]."} is.");

    array_push($data['knownIdentity'][$player], $id);

    advancePresident($data);
    setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president selects chancellor)
    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player);
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
    
    addChatMessageStatus($data,
        "The president has h{executed ".$data['players'][$id]."}.");

    array_push($data['dead'], $id);
    
    if(isHitler($data, $data['players'][$id])){
        setPhase($data, 'PH_LIBERALS_WON'); //-----> PH_FASCISTS_WON (Hitler is killed)
        addChatMessageStatus($data,
            "Hitler was killed, l{Liberals won!}");
    }else{
        advancePresident($data);
        setPhase($data, 'PH_ELECT'); //-----> PH_ELECT (next president selects chancellor)
    }
    saveGameFile($game, $data);
    return constructReturnObjectGame($data, $player);
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
    return constructReturnObjectGame($data, $player);
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
    return constructReturnObjectGame($data, $player); 
}

?>