<?php
    session_cache_limiter('private_no_expire');
    session_start();
    include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/models/model.addServer.php");
    if($_POST["service"] == "3371"){
        $addServer = new addServerModel();
        echo $addServer->showModel();
    }else{
        echo "Bitte loggen Sie sich ein!";
    }
?>