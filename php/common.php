<?php
function gameHash($game){
    $h = substr(md5($game), 10, 8);
    return $h;
}

?>