<?php
    session_start();
    if(isset($_SESSION) && empty($_SESSION)){
        include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/models/model.login.php");
        $login = new loginModel();
        echo $login->showModel();
    }else{
        session_destroy();
    }
?>
