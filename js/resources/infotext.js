"use strict";

var g_infotext = {
    PH_START: "",
    PH_ELECT: "<span class=draw>President</span> chooses <span class=discard>chancellor!</span>",
    PH_VOTE: "Vote for this government.",
    PH_DRAW: "President is <span class=draw>drawing 3 policies</span> and passes 2 to chancellor.",
    PH_PASS: "President <span class=draw>passes 2 policies</span> to chancellor.",
    PH_VETO: "Chancellor <span class=draw>enforces 1 policy</span> or can agree on <span class=highlight>veto</span>.",
    PH_ENFORCE: "Chancellor <span class=draw>enforces 1 policy</span>.",
    PH_INVESTIGATE: "President <span class=highlight>investigates a player</span>.",
    PH_PEAK: "President <span class=draw>looks at top 3</span> policies.",
    PH_EXECUTE: "President <span class=highlight>executes a player</span>.",
    PH_SELECT_PRES: "President chooses <span class=draw>next president!</span>",
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