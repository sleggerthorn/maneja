<?php
    if(!empty($_POST["username"]) && isset($_POST["username"]) && !empty($_POST["password"]) && isset($_POST["password"])){
        include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.loginHandler.php");
        $check_login = new loginHandler();
        echo $check_login->checkLoginInformation($_POST["username"], $_POST["password"]);
    }else{
        echo "Bitte geben Sie Nutzernamen und Passwort ein!";
    }
?>