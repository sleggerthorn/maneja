<?php
    session_start();
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.cryptoHandler.php");
    class loginHandler{
        public static function checkLoginInformation($user, $password){
            if((isset($user) && !empty($user)) && (isset($password) && !empty($password))){
                $dbh = new Databasehandler("maneja", "maneja_backend_base");
                $cryh = new cryptoHandler();
                $id = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT usr_id FROM user WHERE usr_user = ?", array($user), true));
                //Save Query
                $login_allowance_query = ("SELECT pass_usr.pass_user FROM pass_usr, user WHERE user.usr_id = pass_usr.usr_id AND usr_user = ? AND usr_tooladmin = 1");
                $login_query_extension = array($user);
                $pass = $dbh->dbQuerySave($login_allowance_query, $login_query_extension, true);
                if($dbh->dbQueryNumRows($pass) > "0"){
                    $dbh->dbClose();
                    $pass = $dbh->dbQueryFetchAssoc($pass);
                    $pass = $cryh->pwDecrypt($pass["pass_user"], $id["usr_id"], "user");
                    if($password == $pass){
                        $_SESSION["cool"] = "coooool";
                        return "1";
                    }else{
                        return "0";
                    }
                }else{
                    $dbh->dbClose();
                    return "0";
                }
            }else{
                return "0";
            }
        }
    }
?>