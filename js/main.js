function main() {
    if(gameid){
        AJAXgetGame(gameid, playername)
            .then((r)=>{
                drawGame(r.payload);
            })
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