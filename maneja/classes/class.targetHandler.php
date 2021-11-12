<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.connectionHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");
    class targetHandler{
        private static function errorFinder($result_exec){
            if($result_exec == 0){
                return true;
            }else{
                switch ($result_exec) {
                    case 1:
                        echo "[Command-Error] Command given is wrong or has wrong Parameter set ".$result_exec;
                        break;
                    case 2:
                        echo "[Command-Error] Misuse of shell builtins ".$result_exec;
                        break;
                    case 6:
                        echo "[Connection-Error] Authentification with Server failed due to Public Key Restriction or the login/group that has been provided does not exist";
                        break;
                    case 127:
                        echo "[Execution-Error] Cannot execute command, Command not found!";
                        break;
                    case 128:
                        echo "[Execution-Error] Cannot execute command. Invalid Arguments given!";
                        break;
                    case 255:
                        echo "[Execution-Error] Wrong Port given in Database or Target not reachable!";
                        break;
                    default:
                        echo "[Command-Error:] Cannot execute command. System returned with code ".$result_exec." This can be caused by an unexecutable Command given or an unreachable Host.";
                        break;
                }die;
            }
        }
        private static function setDbPermission($action, $ip, $id, $servername){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $dbh->dbConnect();
            if(isset($action) && !empty($action)){
                if($action == "activate"){
                    $permission = "1";
                }else{
                    $permission = "0";
                }
                $dbh->dbQuery("UPDATE permission JOIN user ON permission.usr_id = user.usr_id JOIN access_server ON permission.acssrv_id = access_server.acssrv_id SET perm_grant=".$permission." WHERE access_server.acssrv_ip='".$ip."' AND user.usr_id='".$id."' AND access_server.acssrv_name='".$servername."'");
                $dbh->dbClose();
            }
            else{
                return "Unkown Action permitted to targetHandler. Can not set Permission".$dbh->getDbError();
            }
        }
    //Formals changeUserProfile -> changeUserInformation -> changeServerAccess
        public static function targetServer($ip, $id, $servername, $port, $command, $action){
            $connection = new connectionHandler($ip, $port);
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $cryh = new cryptoHandler();
            $server_pass_info = $dbh->dbQueryFetchAssoc($dbh->dbQueryReturn("SELECT acssrv_user, acssrv_pass FROM access_server WHERE acssrv_ip = '".$ip."' AND acssrv_name = '".$servername."'"));
            $server_pass_info["acssrv_pass"] = $cryh->srvDecrypt($server_pass_info["acssrv_pass"], $ip, $servername);
            if($connection->serverConnect($server_pass_info["acssrv_user"], $server_pass_info["acssrv_pass"]) == true){
                for($x = 0; $x < count($command); $x++){
                    exec($connection->getConnection()." '".$command[$x]."'", $array, $result_exec);
                    if(self::errorFinder($result_exec) == true){
                        self::setDbPermission($action, $ip, $id, $servername);
                        $state = "true";
                    }else{
                        $state = "false";
                    }
                }return $state;
            }else{
                return "Connection Error";
            }
        }
    }
?>