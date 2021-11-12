<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.targetHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.connectionHandler.php");
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.databaseHandler.php");
    class cryptoHandler{
        private $passphrase_construction_array = array("A", "a", "B", "b", "C", "c", "D", "d", "E", "e", "F", "f", "G", "g", "H", "h", "I", "i", "J", "j", "K", "k", "L", "l", "M", "m", "N", "n", "O", "o", "P", "p", "Q", "q", "R", "r", "S", "s", "T", "t", "U", "u", "V", "v", "W", "w", "X", "x", "Y", "y", "Z", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "+", "=");
        private $key;
        private $length;

        private function getConstructArray(){
            return $this->passphrase_construction_array;
        }

        private function getKey(){
            return $this->key;
        }

        private function getLength(){
            return $this->length;
        }

        private function setKey($key){
            $this->key = $key;
        }

        private function setLength($length){
            $this->length = $length;
        }

        private function genKey($id){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $pass_key = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT pass_usr.pass_key, pass_usr.usr_id FROM pass_usr, user WHERE user.usr_id = pass_usr.usr_id AND user.usr_id = ?", array($id), true));
            if(empty($pass_key["pass_key"])){
                $passphrase = $this->getConstructArray();
                $key = "";
                for($i=0; $i < 96; $i++){
                    $key .= $passphrase[rand(0, 64)];
                }
                $this->setKey($key);
                return $key;
            }else{
                if(!empty($pass_key["pass_key"])){
                    $this->setKey($pass_key["pass_key"]);
                    return $pass_key["pass_key"];
                }else{
                    return "Key empty, cannot work with Password";
                }
            }
        }

        private function genSrvKey($ip){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $pass_key = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT acssrv_id, acssrv_key FROM access_server WHERE acssrv_ip = ?", array($ip), true));
            if(empty($pass_key["acssrv_key"])){
                $passphrase = $this->getConstructArray();
                $key = "";
                for($i=0; $i < 96; $i++){
                    $key .= $passphrase[rand(0, 64)];
                }
                $this->setKey($key);
                return $key;
            }else{
                if(!empty($pass_key["acssrv_key"])){
                    $this->setKey($acssrv_key["acssrv_key"]);
                    return $acssrv_key["acssrv_key"];
                }else{
                    return "Key empty, cannot work with Password";
                }
            }
        }
        
        private function extractSalt($crypt_pw, $id, $pw_type){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            switch ($pw_type) {
                case 'user':
                    $pw_info = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT pass_user, pass_user_length FROM pass_usr WHERE usr_id = ?", array($id), true));
                    break;
                case 'mail':
                    $pw_info = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT pass_mail, pass_mail_length FROM pass_usr WHERE usr_id = ?", array($id), true));
                    break;
                case '1und1':
                    $pw_info = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT pass_1und1, pass_1und1_length FROM pass_usr WHERE usr_id = ?", array($id), true));
                    break;
                default:
                    die;
            }
            if(!empty($pw_info) && isset($pw_info)){
                return substr($pw_info["pass_".$pw_type], 0, $pw_info["pass_".$pw_type."_length"]);
            }else{
                return "Password or Length empty or not set";
            }
        }
        
        private function extractSrvSalt($srv_pw, $ip, $servername){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $pw_info = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT acssrv_pass, acssrv_length FROM access_server WHERE acssrv_ip = ? AND acssrv_name = ?", array($ip, $servername), true));
            if(!empty($pw_info) && isset($pw_info)){
                return substr($pw_info["acssrv_pass"], 0, $pw_info["acssrv_length"]);
            }else{
                return "Password or Length empty or not set";
            }
        }

        private function setSalt($encrypted_pw){
            $passphrase = $this->getConstructArray();
            $pw_length = strlen(base64_encode($encrypted_pw));
            
            $this->setLength($pw_length);

            $salted_crypt_pw = "";
            for($i = strlen($pw_length); $i < 56; $i++){
                $salted_crypt_pw .= $passphrase[rand(0, 64)];
            };
            return base64_encode($encrypted_pw).$salted_crypt_pw;
        }

        public function pwEncrypt($pw_user, $pw_mail, $pw_1und1, $id, $user){
            $key = $this->genKey($id);
            $password = array(
                "pw_user" => openssl_encrypt($pw_user, "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14"),
                "pw_mail" => openssl_encrypt($pw_mail, "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14"),
                "pw_1und1" => openssl_encrypt($pw_1und1, "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14")
            );
            if(!empty($password["pw_user"]) && isset($password["pw_user"])){
                foreach($password as $pw_kind => $value){
                    $password[$pw_kind] = $this->setSalt($password[$pw_kind]);
                    $length[$pw_kind] = $this->getLength();
                }
                $dbh = new Databasehandler("maneja", "maneja_backend_base");
                $dbh->dbQuerySave("INSERT INTO pass_usr(usr_id, pass_user, pass_key, pass_user_length, pass_mail, pass_mail_length, pass_1und1, pass_1und1_length) VALUES ((SELECT usr_id FROM user WHERE usr_user = ? ), ?, ?, ?, ?, ?, ?, ?)", array($user, $password["pw_user"], $this->getKey(), $length["pw_user"], $password["pw_mail"], $length["pw_mail"], $password["pw_1und1"], $length["pw_1und1"]), true);
                return "1";
            }else{
                return "Failed encryption";
            }
        }

        public function srvEncrypt($srv_password, $srv_ip, $servername){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $key = $this->genSrvKey($srv_ip);
            $password = openssl_encrypt($srv_password, "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14");
            if(!empty($password) && isset($password)){
                $salt = $this->setSalt($password);
                $length = $this->getLength();
                $dbh = new Databasehandler("maneja", "maneja_backend_base");
                $dbh->dbQuerySave("UPDATE access_server SET acssrv_pass = ?, acssrv_length = ?, acssrv_key = ? WHERE acssrv_ip = ? AND acssrv_name = ?", array($salt, $length, $key, $srv_ip, $servername), true);
                return "1";
            }else{
                return "Failed encryption";
            }
        }

        public function srvDecrypt($crypt_pw, $ip, $servername){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $saltless_pw = $this->extractSrvSalt($crypt_pw, $ip, $servername);
            $decrypt_key = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT acssrv_key FROM access_server WHERE acssrv_ip = ? AND acssrv_name = ?", array($ip, $servername), true));
            if(!empty($decrypt_key["acssrv_key"]) && isset($decrypt_key["acssrv_key"])){
                return openssl_decrypt($saltless_pw, "aes-256-ctr", $decrypt_key["acssrv_key"], 0, "GkTaUuYiAy10RZ14");
            }else{
                return "Couldnt get Key";
            }
        }

        public function pwDecrypt($crypt_pw, $id, $pw_type){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $saltless_pw = $this->extractSalt($crypt_pw, $id, $pw_type);
            $decrypt_key = $dbh->dbQueryFetchAssoc($dbh->dbQuerySave("SELECT pass_key FROM pass_usr WHERE usr_id = ?", array($id), true));
            if(!empty($decrypt_key["pass_key"]) && isset($decrypt_key["pass_key"])){
                return openssl_decrypt($saltless_pw, "aes-256-ctr", $decrypt_key["pass_key"], 0, "GkTaUuYiAy10RZ14");
            }else{
                return "Couldnt get Key";
            }
        }
    }
?>