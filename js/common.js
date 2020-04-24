"use strict";

function restrictInput(e){
    let str = e.target.value
    str = str.replace(/[^A-Za-z0-9 áčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ]/,'');
    if(str.length > 15){
        str = str.substr(0,15);
    }
    e.target.value = str;
}