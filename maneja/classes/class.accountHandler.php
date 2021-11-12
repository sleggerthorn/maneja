<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.connectionHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.cryptoHandler.php");
    final class accountHandler{
        public static function createUser($name, $surname, $password , $email, $company, $job, $tooladmin, $action){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $cryh = new cryptoHandler();
            $dbh->dbConnect();
            if($company == "MediaTronic"){
                $user = strtolower($name[0].".".$surname);
            }else{
                $user = strtolower($name[0].$name[1].$surname[0].$surname[1]);
            }
            $pass = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT user.usr_id, pass_usr.pass_mail, pass_usr.pass_1und1 FROM user, pass_usr WHERE user.usr_id = pass_usr.usr_id ORDER BY usr_id DESC LIMIT 1"));
            $id = $pass["usr_id"];
            $mail_password = "M3d115+#".strtoupper($name[0].$surname[0])."+#".(intval(substr($cryh->pwDecrypt($pass["pass_mail"], $id, "mail"), strpos($cryh->pwDecrypt($pass["pass_mail"], $id, "mail"), "20")))+1);
            $oneandone_password = "1plus1!%2018!%Med!17!0".(intval(substr($cryh->pwDecrypt($pass["pass_1und1"], $id, "1und1"), -3)) +1);

            $check_user_exist_param = array($user, $email);
            $check_user_exist = $dbh->dbQuerySave("SELECT usr_user FROM user WHERE usr_user=? OR usr_email=?", $check_user_exist_param, true);

            if($dbh->dbQueryNumRows($check_user_exist) == "0"){

                $create_user_param = array($name, $surname, $user, $email, $company, $job, $tooladmin);
                $dbh->dbQuerySave("INSERT INTO user(usr_id, usr_name, usr_surname, usr_user, usr_email, usr_company, usr_job, usr_tooladmin, usr_status) VALUES(NULL, ?, ?, ?, ?, ?, ?, ?, 0)", $create_user_param, false);

                $all_server_query = $dbh->dbQueryReturn("SELECT acssrv_id FROM access_server");
                $param = array($user);

                while($row = $dbh->dbQueryFetchAssoc($all_server_query)){
                    $all_server["acssrv_id"][] = $row;
                }
                for($x=0; $x<count($all_server["acssrv_id"]);$x++){
                    foreach($all_server["acssrv_id"][$x] as $value){
                        $dbh->dbQuerySave("INSERT INTO permission(usr_id, acssrv_id, perm_grant, perm_local_admin) VALUES ((SELECT usr_id FROM user WHERE usr_user=?),'".$value."',0,0)", $param, true);
                    }
                }

                $pass = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT usr_id FROM user WHERE usr_user=?", array($user), true));

                $cryh->pwEncrypt($password, $mail_password, $oneandone_password, $pass["usr_id"], $user);

                if(empty($dbh->getDbError())){
                    $dbh->dbClose();
                    echo "1";
                }else{
                    echo "Database Error". $dbh->getDbError();
                    $dbh->dbClose();
                }
            }else{
                echo "User/Email already exists";
                $dbh->dbClose();
            }
        }
        public static function dropUserProfile($id, $user){
            if((!empty($id) && strlen($id) < 9) && (!empty($user) && strlen($user) < 25)){
                $dbh = new Databasehandler("maneja", "maneja");
                $check_permission = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT permission.perm_grant FROM permission WHERE permission.usr_id=?", $id, true));
                if($check_permission["perm_grant"][0] == "0"){
                    $dbh->dbQuerySave("DELETE FROM permission WHERE permission.usr_id=?", $id, false);
                    $dbh->dbQuerySave("DELETE FROM user WHERE user.usr_id=?", $id, false);
                    if($dbh->getDbAffectedRows() != "0"){
                        echo "true";
                        $latest_id = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT usr_id FROM user ORDER BY usr_id DESC LIMIT 1"));
                        $dbh->dbQuery("ALTER TABLE user AUTO_INCREMENT =".$latest_id["usr_id"]);
                    }else{
                        echo "false";
                        error_log("No rows matched or affected");
                        error_log($dbh->getDbError());
                    }
                }else{
                    echo "false";
                    error_log("User Permission still set, cannot delete User! Please unset the Userpermission first.");
                }
            }
        }


        public static function changeUserProfile($name, $surname, $permission, $email, $id){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $dbh->dbConnect();
            $profile_setting_query = "UPDATE user SET usr_name = ?, usr_surname = ?, usr_job = ?, usr_tooladmin = ?, usr_email = ? WHERE usr_id = ?";
            $profile_setting_param = array($name, $surname, $permission, $email, $id);
            $profile_settings = $dbh->dbQuerySave($profile_setting_query, $profile_setting_param, true);
            if(empty($dbh->getDbError())){
                return "1";
            }else{
                return "Keine Einträge betroffen oder Datenbankfehler! ".$dbh->getDbError();
            }
        }
    }
?>