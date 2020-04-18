function main() {
    if(gameid){
        refreshGame();
    }else{
        AJAXgetGames()
        .then(
            (r)=>{
                $("#content").empty();
                drawLobby(r.payload);
            }
        )
        //data.then((r)=>console.log(r));
        //$("body").text(data);
    }
}