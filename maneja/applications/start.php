<?php
session_cache_limiter('private_no_expire');
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/models/model.start.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/applications/maneja/classes/class.loginHandler.php");
$login = new loginHandler();
if((isset($_POST["username"]) && !empty($_POST["username"])) && (isset($_POST["password"]) && !empty($_POST["password"]))){
    if($login->checkLoginInformation(utf8_decode($_POST["username"]), utf8_decode($_POST["password"]))){
        $_POST["loggedIn"] = true;
        $start = new startModel();
        echo $start->showModel();
    }else{
        echo "Falscher Nutzername / Passwort";
    //    echo $login->showModel();
    }
}else{
    echo "Kein Benutzername / Passwort übermittelt";
    echo $login->showModel();
}

?>