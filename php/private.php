<?php

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

$admins = ['SUDO'];

function isAdmin($player){
    global $admins;
    return in_array($player, $admins);
}


/* ============================================ */
/*= ============= PRIVATE HELPER ============== */
/* ============================================ */

// ============ CHAT ================ //

function addChatMessage(string $game, string $player, string $message) : bool {
    # TODO: more input sanitisation
    $message = str_replace("<", "&lt", $message);
    $message = str_replace(">", "&gt", $message);

    if($message[0] == '\\'){
        if($message == "\\clc"){
            clearChatFile($game);
            return addChatMessage($game, '[info]', "h{The chat was cleared}");
        }
        return true;
    }
    $message = str_replace("\"", "'", $message);
    $mes['time'] = date('Y-m-d, H:i:s');
    $mes['player'] = $player;
    $mes['message'] = $message;
    appendChatFile($game, json_encode($mes));
    return true;
}

function addChatMessageStatus(array $data, string $message) : void {
    addChatMessage($data['game'], "[game]", $message);
}

function clearChat(array $data) : void {
    clearChatFile($data['game']);
}

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
    $data['chancellor'] = $data['president'];
    $data['modifiers']['temporalPresidency'] = false;
    addChatMessageStatus($data, 'The next president is p{'.$data['players'][$data['president']].'}');
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
    addChatMessageStatus($data, "Advancing election tracker...");
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
        if(!$data['modifiers']['vetoEnabled']){;
            addChatMessageStatus($data, "h{veto power} is now enabled");
            $data['modifiers']['vetoEnabled'] = true;
        }
    }
    if($data['fascistPolicies'] >= $data['triggersModifiers']['fascistEndGameAt']){
        if(!$data['modifiers']['fascistEndGame']){
            addChatMessageStatus($data, "From this point on, when c{hitler} is elected as chancellor, f{fascists win}.");
            $data['modifiers']['fascistEndGame'] = true;
        }
    }
}

function checkPowersAndSetPhase(array &$data) : bool {
    $fpolicies = $data['fascistPolicies'];
    $data['temporalPresidency'] = false;
    if (in_array($fpolicies, $data['triggersPowers']['selectPresidentAt'])){
        setPhase($data, 'PH_SELECT_PRES');  //-----> PH_SELECT_PRES (president chooses next president)
        addChatMessageStatus($data, "p{power triggered}: The president selects a h{next president candidate}.");
    } elseif (in_array($fpolicies, $data['triggersPowers']['investigateAt'])){
        addChatMessageStatus($data, "p{power triggered}: The president h{investigates a player}.");
        setPhase($data, 'PH_INVESTIGATE'); //-----> PH_INVESTIGATE (president investigates player)
    } elseif (in_array($fpolicies, $data['triggersPowers']['peakAt'])){
        addChatMessageStatus($data, "p{power triggered}: The president h{looks at the top 3 policies}.");
        setPhase($data, 'PH_PEAK'); //-----> PH_PEAK (president checks top three policies)
    } elseif (in_array($fpolicies, $data['triggersPowers']['executeAt'])){
        addChatMessageStatus($data, "p{power triggered}: The president h{executes a player}.");
        setPhase($data, 'PH_EXECUTE'); //-----> PH_EXECUTE (president kills a player)
    } else {
        return false; // nothing triggered
    }
    return true;
}

function checkWinSituationAndSetPhase(array &$data) : bool {
    if($data['liberalPolicies'] >= $data['triggersModifiers']['liberalsWinAt']){
        addChatMessageStatus($data, "l{Liberals won!}");
        setPhase($data, 'PH_LIBERALS_WON'); //-----> PH_LIBERALS_WON
        return true;
    }
    if($data['fascistPolicies'] >= $data['triggersModifiers']['fascistsWinAt']){
        addChatMessageStatus($data, "f{fascists won!}");
        setPhase($data, 'PH_FASCISTS_WON'); //-----> PH_LIBERALS_WON
        return true;
    }
    return false;
}


function enactPolicy(array &$data, int $policy) : void {
    // which card was enforced
    if($policy == 0){
        $data['liberalPolicies']++;
    } else {
        $data['fascistPolicies']++;
    }
    addChatMessageStatus($data, ($policy ? "f{Fascist}" : "l{Liberal}")." policy was enacted.");
    resetElectionTracker($data);
    resetVotes($data);
    updateModifiers($data);
}


function revealTopPolicy(array &$data) : void {
    $policy = array_pop($data['drawPile']);
    addChatMessageStatus($data, "Revealing top policy...");
    enactPolicy($data, $policy);
}

function markChancellorNotHitler(array &$data) : void {
    $data['confirmedNotHitler'][] = $data['chancellor'];
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
    addChatMessageStatus($data, "Shuffling discard pile back into draw pile...");
}

// ================== BOOL ================= //


// --- LOBBY --- //

function isReady(array $lobby, string $player){
    return $lobby['players'][$player];
}

function isEveryoneReady(array $lobby){
    foreach ($lobby['players'] as $p => $ready) {
        if(!$ready) return false;
    }
    return true;
}

function isCreator(array $lobby, string $player){
    return $player == $lobby['creator'];
}

// --- GAME ---- //

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
    $vote_count = 0;
    foreach ($data['voting'] as $name => $vote){
        if(isDead($data, $name)) continue;
        $vote_count++;
        if($vote == 1) $ja_count++;
    }
    if($ja_count/$vote_count > 0.5 ){
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

// ============== CREATING GAME OBJECT ===================== //

function getTriggersForPlayerCount(int $count) : array{
    // select, investigate, peak, execute
    $triggersPowers = [];
    if ($count <= 6) {
        $triggersPowers['selectPresidentAt'] = [];
        $triggersPowers['investigateAt'] = [];
        $triggersPowers['peakAt'] = [3];
        $triggersPowers['executeAt'] = [4,5];
    } elseif ($count > 6 && $count <= 8) {
        $triggersPowers['selectPresidentAt'] = [3];
        $triggersPowers['investigateAt'] = [2];
        $triggersPowers['peakAt'] = [];
        $triggersPowers['executeAt'] = [4,5];
    } elseif ($count > 8 && $count <= 10) {
        $triggersPowers['selectPresidentAt'] = [3];
        $triggersPowers['investigateAt'] = [1,2];
        $triggersPowers['peakAt'] = [0];
        $triggersPowers['executeAt'] = [4,5];
    }
    return $triggersPowers;
}

function createGameData(string $game, array $players){
    foreach ($players as $player) {
        $voting[$player] = null;
        $knownIdentity[$player] = [];
    }
    
    $fascistCountPlayerCountMap = [
        5 => 2,
        6 => 2,
        7 => 3,
        8 => 3,
        9 => 4,
        10 => 4
    ];

    $playerCount = count($players);
    $fascistCount = $fascistCountPlayerCountMap[$playerCount];
    $president = array_rand($players);
    $fascists = array_rand($players, $fascistCount);

    $drawPile = [1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0]; // 11 fascist, 6 liberal
    shuffle($drawPile);
    
    $ret = [
        "game" => $game,
        "phase" => 'PH_ELECT',
        "players" => $players,
        "voting" => $voting,
        "knownIdentity" => $knownIdentity,
        "president" => $president,
        "chancellor" => $president,
        "temporaryPresident" => null,
        "lastGovernment" => [],
        "fascists" => $fascists,
        "hitler" => $fascists[array_rand($fascists)],
        "confirmedNotHitler" => [],
        "dead" => [],
        "drawPile" => $drawPile,
        "discardPile" => [],
        "presidentsHand" => [],
        "chancellorsHand" => [],
        "liberalPolicies" => 0,
        "fascistPolicies" => 0,
        "electionTracker" => 0,
        "hitlerKnowsFascists" => ($playerCount <= 6) ? true : false,
        "wantsVeto" => [
            "president" => null,
            "chancellor" => null
        ],
        "triggersPowers" => getTriggersForPlayerCount($playerCount),
        "triggersModifiers" => [
            "vetoEnabledAt" => 5,
            "fascistEndGameAt" => 3,
            "liberalsWinAt" => 5,
            "fascistsWinAt" => 6
        ],
        "modifiers" => [
            "vetoEnabled" => false,
            "fascistEndGame" => false,
            "temporalPresidency" => false
        ]
    ];
    return $ret;
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
    $ret['wasLastGovernment'] = wasLastGovernment($data, array_search($player, $data['players']));
    $ret['confirmedNotHitler'] = in_array(array_search($player, $data['players']), $data['confirmedNotHitler']);
    return $ret;
}

function getPlayerRoles(array $data, string $player) : array {
    $ret = getLiberalPlayerRoles($data, $player);
    $ret = array_merge($ret, getFascistPlayerRoles($data, $player));
    return $ret;
}

function constructReturnObjectGame(array $data, string $player) : array {
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
    $indexes['deadPlayers'] = $data['dead'];
    $indexes['lastGovernment'] = $data['lastGovernment'];
    $indexes['thisPlayer'] = array_search($player, $data['players']);
    $indexes['president'] = $data['modifiers']['temporalPresidency']
                            ? $data['temporaryPresident']
                            : $data['president'];
    $indexes['chancellor'] = $data['chancellor'];
    $ret['indexes'] = $indexes;
    
    foreach ($data['players'] as $i => $p) {
        if($p == $player){
            $ret['players'][$p] = $ret['thisPlayer'];
            continue;
        }
        $ret['players'][$p] = getLiberalPlayerRoles($data, $p);
        $ret['players'][$p]['isFascist'] = null;
        $ret['players'][$p]['isHitler'] = null;
        $ret['players'][$p]['handSize'] = 0;
        if($ret['players'][$p]['isPresident']){
            $ret['players'][$p]['handSize'] = count($data['presidentsHand']);
        }
        if($ret['players'][$p]['isChancellor']){
            $ret['players'][$p]['handSize'] = count($data['chancellorsHand']);
        }

        $ret['players'][$p]['vote'] = null;
        if(hasEveryoneVoted($data)){
            $ret['players'][$p]['vote'] = $data['voting'][$p];
        }
        if (!$player){
            continue;
        }
        if($data['phase'] == 'PH_FASCISTS_WON' || $data['phase'] == 'PH_LIBERALS_WON'){
            $ret['players'][$p]['isFascist'] = isFascist($data, $p);
            $ret['players'][$p]['isHitler'] = isHitler($data, $p);
            continue;
        }
        if(in_array($i, $data['knownIdentity'][$player])){
            $ret['players'][$p]['isFascist'] = isFascist($data, $p);
        }
        if(isFascist($data, $player) && !isHitler($data, $player)){
            $ret['players'][$p]['isFascist'] = isFascist($data, $p);
            $ret['players'][$p]['isHitler'] = isHitler($data, $p);
        }
        if(isHitler($data, $player)){
            $ret['players'][$p]['isHitler'] = false;
            if($data['hitlerKnowsFascists']){
                $ret['players'][$p]['isFascist'] = isFascist($data, $p);
            }
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
    $chat = loadChatFile($data['game']);
    $ret['chatMessageCount'] = count($chat['messages']);
    global $response; $response['status'] = "ok";
    return $ret;
}

function constructReturnObjectLobby(array $lobby, string $player) : array {
    $ret['game'] = $lobby['game'];
    $ret['thisPlayer']['isCreator'] = isCreator($lobby, $player);
    $ret['thisPlayer']['isAdmin'] = isAdmin($player);
    $ret['players'] = $lobby['players'];
    $chat = loadChatFile($lobby['game']);
    $ret['chatMessageCount'] = count($chat['messages']);
    $ret['gameActive'] = isGameActive($lobby['game']);
    $ret['everyoneReady'] = isEveryoneReady($lobby);
    global $response; $response['status'] = "ok";
    return $ret;
}

?>