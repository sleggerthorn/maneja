<?php

    class connectionHandler{
        private $server_ip;
        private $server_port;
        private $auth_user;
        private $auth_password;
        private $auth_db;
        private $auth_type;
        private $connection;
        
        public function __construct($ip_address, $port){
            $this->setServerIp($ip_address);
            $this->setServerPort($port);
            $this->serverDisconnect();
        }

        private function setServerIp($server_address){
            $this->server_ip = $server_address;
        }
    
        private function setServerPort($serverport){
            $this->server_port = $serverport;
        }

        private function setAuthUser($user_auth){
            $this->auth_user = $user_auth;
        }

        private function setAuthPassword($password_auth){
            $this->auth_password = $password_auth;
        }

        private function setAuthDb($db){
            $this->auth_db = $db;
        }

        private function setConnection($connect){
            $this->connection = $connect;
        }

        private function getServerIp(){
            return $this->server_ip;
        }

        private function getServerPort(){
            return $this->server_port;
        }

        private function getAuthUser(){
            return $this->auth_user;
        }

        private function getAuthPassword(){
            return $this->auth_password;
        }

        private function getAuthDb(){
            return $this->auth_db;
        }

        public function getConnection(){
            return $this->connection;
        }

        public function testConnection(){
            exec("ping -w1 ".$this->getServerIp(), $output, $error);
            if($error === 0){
                return "true";
            }else{
                return ("[Connection Error:] Server not found");
            }
        }

        public function serverConnect($auth_user, $auth_password){
            $dbh = new Databasehandler("maneja", "maneja_backend_base");
            $this->setAuthUser($auth_user);
            $this->setAuthPassword($auth_password);
            $this->setConnection("sshpass -p ".$this->getAuthPassword()." ssh -T -o 'StrictHostKeyChecking=no' -p".$this->getServerPort()." ".$this->getAuthUser()."@".$this->getServerIp());
            return $this->testConnection();
        }

        public function serverDisconnect(){
            $this->setConnection("");
        }
    }
?>