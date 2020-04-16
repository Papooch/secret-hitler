function main() {
    if(gameid){
        getGame(gameid)
            .then((r)=>{
                drawGame(r.payload);
            })
    }else{
        getGames()
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