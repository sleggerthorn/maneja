<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.targetHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.cryptoHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.connectionHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");

    final class serviceHandler{
        //Sets Command and resolves placeholder for execution
        public static function setCommand($action, $id, $password, $ip, $port){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $dbh->dbConnect();
            $cryh = new cryptoHandler();
            $user = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT usr_user, usr_name, usr_surname, usr_email, usr_job, usr_company FROM user WHERE usr_id = '".$id."'"));
            $pass = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT pass_user, pass_mail, pass_1und1 FROM pass_usr, user WHERE user.usr_id = pass_usr.usr_id AND pass_usr.usr_id = '".$id."'"));
            $servername = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT acssrv_name FROM access_server WHERE acssrv_ip = '".$ip."'"));
            $pass_user = $cryh->pwDecrypt($pass["pass_user"], $id, "user");
            if($servername["acssrv_name"] == "Active Directory"){
                $user["usr_user"] = str_replace(".", "", $user["usr_user"]);
            }else if($servername["acssrv_name"] == "Mattermost"){
                $user["usr_user"] = str_replace("@mediatronic.eu", "", $user["usr_email"]);
            }else if($servername["acssrv_name"] == "Icinga"){
                $pass_user = password_hash($cryh->pwDecrypt($pass["pass_user"], $id, "user"), PASSWORD_DEFAULT, array("memory_cost" => PASSWORD_ARGON2_DEFAULT_MEMORY_COST, "time_cost" => PASSWORD_ARGON2_DEFAULT_TIME_COST, "threads" => PASSWORD_ARGON2_DEFAULT_THREADS));
            }else if($servername["acssrv_name"] == "Mail"){
                $salt = substr(sha1(rand()), 0, 16);
                $pass_user = base64_encode(hash('sha512', $pass_user . $salt, true) . $salt);
            }
            $placeholder_replace = array(
                "usr_user" => $user["usr_user"],
                "usr_name" => $user["usr_name"],
                "usr_surname" => $user["usr_surname"],
                "usr_password" => $pass_user,
                "usr_email" => $user["usr_email"],
                "usr_einsundeins" => $cryh->pwDecrypt($pass["pass_1und1"], $id, "1und1"),
                "usr_email_password" => $cryh->pwDecrypt($pass["pass_mail"], $id, "mail"),
                "usr_job" => $user["usr_job"],
                "usr_company" => $user["usr_company"]
            );

            $result = $dbh->dbQueryReturn("SELECT access_server.acssrv_".$action."_com FROM access_server JOIN permission ON access_server.acssrv_id = permission.acssrv_id JOIN user ON permission.usr_id=user.usr_id WHERE user.usr_id='".$id."' AND access_server.acssrv_ip='".$ip."' AND access_server.acssrv_port='".$port."'");
            while($row = $dbh->dbQueryFetchAssoc($result)){
                $res_command = $row;
            }
            if(!empty($res_command["acssrv_".$action."_com"]) && isset($res_command["acssrv_".$action."_com"])){
                $command = explode("?", $res_command["acssrv_".$action."_com"]);
                $dbh->dbClose();

                for($x=0;$x<count($command);$x++){
                    foreach($placeholder_replace as $key => $value){
                        $command = str_replace($key, $value, $command);
                    }
                }
                return $command;
            }else{
                return "Befehl nicht gefunden, bitte legen Sie zuerst einen Befehl an";
            }
        }

        //Puts a new Server into the Backend. Commands must not be set here, they can be edited later

        public static function createServer($servername, $ip, $port, $acs_user, $acs_pass, $com_cre, $com_act, $com_deact, $com_del, $flag, $action){
            $cryh = new cryptoHandler();
            $valid = filter_var($ip, FILTER_VALIDATE_IP);
            if($valid == true){
                $dbh = new Databasehandler("maneja", "maneja_backend_base");
                $dbh->dbConnect();
                $check_server_exist_param = array($ip, $port);
                $check_server_exist = $dbh->dbQuerySave("SELECT acssrv_name FROM access_server WHERE acssrv_ip = ? AND acssrv_port = ?", $check_server_exist_param, true);
                if($dbh->dbQueryNumRows($check_server_exist) == "0"){
                    $create_server_param = array($ip, $port, $acs_user, $acs_pass, $com_cre, $com_act, $com_deact, $com_del, $servername, $flag);
                    $dbh->dbQuerySave("INSERT INTO access_server(acssrv_id, acssrv_ip, acssrv_port, acssrv_user, acssrv_pass, acssrv_create_com, acssrv_activate_com, acssrv_deactivate_com, acssrv_delete_com, acssrv_name, acssrv_deletable)VALUES(NULL,?,?,?,?,?,?,?,?,?,?)", $create_server_param, false);
                    $cryh->srvEncrypt($acs_pass, $ip, $servername);
                    $all_user_query = $dbh->dbQueryReturn("SELECT usr_id FROM user");
                    $param = array($ip, $port);
                    while($row = $dbh->dbQueryFetchAssoc($all_user_query)){
                        $all_user["usr_id"][] = $row;
                    }
                    for($x=0; $x<count($all_user["usr_id"]);$x++){
                        foreach($all_user["usr_id"][$x] as $value){
                            $dbh->dbQuerySave("INSERT INTO permission(acssrv_id, usr_id, perm_grant, perm_local_admin) VALUES ((SELECT acssrv_id FROM access_server WHERE acssrv_ip=? AND acssrv_port=?),'".$value."',0,0)", $param, true);
                        }
                    }
                    if(empty($dbh->getDbError())){
                        $dbh->dbClose();
                        return "1";
                    }else{
                        $dbh->dbClose();
                        return "[serviceHandler ERROR:] Bei abarbeiten des Querys ist folgender Fehler aufgetreten: ".$dbh->getDbError();
                    }
                }else{
                    $dbh->dbClose();
                    return "Service on Port already exist";
                }
            }else{
                return "Not a IP-Adress";
            }
        }

        //Edits Serverinformations

        public static function alterServer($id, $servername, $acssrv_ip, $acssrv_port, $acssrv_create_com, $acssrv_activate_com, $acssrv_deactivate_com, $acssrv_delete_com, $acssrv_deletable, $action){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $dbh->dbConnect();
            $server_alter_query = "UPDATE access_server SET acssrv_name = ?, acssrv_ip = ?, acssrv_port = ?, acssrv_create_com = ?, acssrv_activate_com = ?, acssrv_deactivate_com = ?, acssrv_delete_com = ?, acssrv_deletable = ? WHERE acssrv_id = ?";
            $server_alter_param = array($servername, $acssrv_ip, $acssrv_port, $acssrv_create_com, $acssrv_activate_com, $acssrv_deactivate_com, $acssrv_delete_com, $acssrv_deletable, $id);
            $altered_server = $dbh->dbQuerySave($server_alter_query, $server_alter_param, true);
            if(empty($dbh->getDbError())){
                return "1";
            }else{
                return "Keine Einträge betroffen oder Datenbankfehler! ".$dbh->getDbError();
            }
        }

        private static function userAccountCreate($user_id, $server_id, $servername, $password, $ip, $port){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            //Check for user account flag in Backend
            $dbh->dbConnect();
            $user_account_query = "SELECT perm_server_account FROM permission, user, access_server WHERE user.usr_id = permission.usr_id AND access_server.acssrv_id = permission.acssrv_id AND permission.usr_id = ? AND permission.acssrv_id = ?";
            $user_account_param = array($user_id, $server_id);
            $user_account = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave($user_account_query, $user_account_param, true));
            if($user_account["perm_server_account"] != "1"){
                $command = self::setCommand("create", $user_id, $password, $ip, $port);
                $target = new targetHandler();
                $execution_result = $target->targetServer($ip, $user_id, $servername, $port, $command, "create");
                if($execution_result == "true"){
                    $created_account = $dbh->dbQuerySave("UPDATE permission SET perm_server_account = 1 WHERE permission.usr_id = ? AND permission.acssrv_id = ?", array($user_id, $server_id), true);
                    if($dbh->dbQueryNumRows($created_account) != "0"){
                        return "true";
                    }else{
                        echo "Error during Account Creation";
                        return "Error during Account Creation";
                    }
                }
            }else{
                return "true";
            }
        }
        
        //Sets Accessrights for specific user accounts in the backend and sends Commands to targetHandler

        public static function changeServerAccess($ip, $port, $servername, $password, $id, $email, $action){
        $dbh = new Databasehandler("maneja", "maneja_backend_base");
        $auth_param = array($ip, $port);
        $auth_query = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT acssrv_port, acssrv_id FROM access_server WHERE acssrv_ip =? AND acssrv_port=?", $auth_param, true));
        //Execute Command on Server and get Serverresponse
        //Errorhandling belongs to connectionHandler
            $server_id = $auth_query["acssrv_id"];
            if(self::userAccountCreate($id, $server_id, $servername, $password, $ip, $port) == "true"){
                $command = self::setCommand($action, $id, $password, $ip, $port);
                $target = new targetHandler();
                $execution_result = $target->targetServer($ip, $id, $servername, $auth_query["acssrv_port"], $command, $action);
                //Sets active or inactive flag in Database
                if($execution_result == "true"){
                    if($action == "delete"){
                        $dbh->dbQuerySave("UPDATE permission SET perm_server_account = 0 WHERE permission.usr_id = user.usr_id AND permission.acssrv_id = access_server.acssrv_id AND user.usr_id = ? AND access_server.acssrv_id = ?", array($user_id, $server_id), false);
                        return "1";
                    }
                    if($action == "activate"){
                        $dbh->dbQueryReturn("UPDATE user SET usr_status = 1 WHERE user.usr_id = '".$id."'");
                        return "1";
                    }else{
                        $avg_status = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT SUM(permission.perm_grant) FROM user, permission WHERE user.usr_id = permission.usr_id AND user.usr_id = '".$id."'"));
                        if($avg_status["SUM(permission.perm_grant)"] == 0){
                            $dbh->dbQueryReturn("UPDATE user SET usr_status = 0 WHERE user.usr_id = '".$id."'");
                            return "1";
                        }else{
                            return "1";
                        }
                    }
                }
            };
        }
    }
?>