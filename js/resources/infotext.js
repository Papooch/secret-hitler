var infotext = {
    PH_START: "",
    PH_ELECT: "President chooses chancellor!",
    PH_VOTE: "Vote for this government.",
    PH_DRAW: "President is drawing 3 policies and passes 2 to chancellor.",
    PH_PASS: "President is drawing 3 policies and passes 2 to chancellor.",
    PH_VETO: "Chancellor enforces 1 policy or can agree on veto.",
    PH_ENFORCE: "Chancellor enforces 1 policy.",
    PH_INVESTIGATE: "President investigates a player.",
    PH_PEAK: "President looks at top 3 policies.",
    PH_EXECUTE: "President kills a player.",
    PH_SELECT_PRES: "President chooses next president!",
    PH_FASCISTS_WON: "Fascists won!",
    PH_LIBERALS_WON: "Liberals won!"
}


/**
 * @brief returns one of three values depending on value of value
 * 
 * @param {bool|null} value
 * @param {any} t returned when value is true
 * @param {any} f returned when value is false
 * @param {any} n returned when value is null
 * 
 * @returns t, f or n
 */
function TFN(value, t, f, n=null){
    if(value == null){
        return n
    }
    return value ? t : f;
}