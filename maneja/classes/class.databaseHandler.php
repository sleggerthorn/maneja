<?php
    /////////// Programmed by Nico Czisch aka Neiko ///////////
    final class databaseHandler{
        // ### attributes ### //////////////////////////////////////////////////////////////

        private $db_host;
        private $db_user;
        private $db_password;
        private $db;
        private $db_connect;
        private $db_stmt;
        private $db_stmt_result;
        private $db_affected_rows;

        // ### methods ### //////////////////////////////////////////////////////////////
        
        function __construct($user_id, $db){
            $db_auth = parse_ini_file($_SERVER["DOCUMENT_ROOT"]."/ressources/user_data.ini");
            $this->setDbHost($db_auth["DB_HOST"]);
            $this->setDbUser($db_auth["DB_USER"]);
            $this->setDbPassword($db_auth["DB_PASSWORD"]);
            $this->setDb($db);
            $this->dbConnect();
        }

        // ### magic methods ###
        function __deconstruct(){
            $this->dbClose();
        }

        // ### setter methods ###
        private function setDbHost($db_host){
            $this->db_host = $db_host;
        }

        private function setDbUser($db_user){
            $this->db_user = $db_user;
        }

        private function setDbPassword($db_password){
            $this->db_password = $db_password;
        }

        private function setDb($db){
            $this->db = $db;
        }

        private function setDbConnect($db_connect){
            $this->db_connect = $db_connect;
        }

        private function setDbAffectedRows($db_affected_rows){
            $this->db_affected_rows = $db_affected_rows;
        }

        // ### getter methods ###
        private function getDbHost(){
            return $this->db_host;
        }

        private function getDbUser(){
            return $this->db_user;
        }
        
        private function getDbPassword(){
            return $this->db_password;
        }

        private function getDb(){
            return $this->db;
        }
        
        public function getDbConnect(){
            return $this->db_connect;
        }

        public function getDbError(){
            return $this->getDbConnect()->error;
        }

        public function getDbAffectedRows(){
            return $this->db_affected_rows;
        }

        // ### Stellt verbindung mit Datenbank her ###
        public function dbConnect(){
            $this->setDbConnect(new mysqli($this->getDbHost(), $this->getDbUser(), $this->getDbPassword()));
            if($this->getDbConnect()->connect_error){
                echo "[Databasehandler][Error]: Connection Error: ". $this->getDbConnect()->connect_error;
                die;
            }else{
                $this->getDbConnect()->set_charset("utf8");
                $this->dbSelectDatabase();
            }
        }

        // ### Wählt die Datenbank aus ###
        private function dbSelectDatabase(){
            if(!mysqli_select_db($this->getDbConnect(), $this->getDb())){
                echo "[Databasehandler][Error]: Database Select Error: ". $this->getDbConnect()->error;
                return false;
            }else{
                return true;
            }
        }

        // ### Bereitet den SQL Befehl vor ###
        public function dbQueryPrepare($sql_command){
             $this->db_stmt = $this->getDbConnect()->prepare($sql_command);
             return true;
        }

        // ### Bindet die Parmaeter an den SQL Befehl ###
        public function dbQueryBindParam($db_stmt, $params){
            if ($params != null)
            {
                // Generate the Type String (eg: 'issisd')
                $types = '';
                foreach($params as $param)
                {
                    if(is_int($param)) {
                        // Integer
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        // Double
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        // String
                        $types .= 's';
                    } else {
                        // Blob and Unknown
                        $types .= 'b';
                    }
                }
            
                // Add the Type String as the first Parameter
                $bind_names[] = $types;
            
                // Loop thru the given Parameters
                for ($i = 0; $i < count($params); $i++)
                {
                    // Create a variable Name
                    $bind_name = 'bind' . $i;
                    // Add the Parameter to the variable Variable
                    $$bind_name = $params[$i];
                    // Associate the Variable as an Element in the Array
                    $bind_names[] = &$$bind_name;
                }
                    
                // Call the Function bind_param with dynamic Parameters
                call_user_func_array(array($db_stmt,'bind_param'), $bind_names);
            }
            return $db_stmt;
        }

        // ### Führt den vorbereiten SQL Befehl aus ###
        public function dbQueryExecute(){
            $this->db_stmt->execute();
            $this->setDbAffectedRows(($this->getDbConnect()->affected_rows));
        }

        // ### Führt einen SQL Befehl aus welcher vor SQL Injections schützt ###
        public function dbQuerySave($sql_command, $params, $return){
            $this->dbQueryPrepare($sql_command);
            $this->dbQueryBindParam($this->db_stmt, $params);
            $this->dbQueryExecute();
            if($return == true){
                $this->db_stmt_result = $this->db_stmt->get_result();
                $this->db_stmt->close();
                return $this->db_stmt_result;
            }else{
                $this->db_stmt->close();
            }
        }

        // ### Führt einen SQL Befehl aus und gibt den Status zurück ###
        public function dbQuery($sql_command){
            $result = "";
            if($this->getDbConnect()->query($sql_command) === TRUE){
                $this->setDbAffectedRows(($this->getDbConnect()->affected_rows));
                $result = "[Databasehandler][Info]: Database Query successfully"; 
            }else{
                $result = "[Databasehandler][Error]: ". $this->getDbConnect()->error; 
            }
            return $result;
        }

        // ### Führt einen SQL Befehl aus und gibt das Ergebnis zurück ###
        public function dbQueryReturn($sql_command){
            $this->setDbAffectedRows(($this->getDbConnect()->affected_rows));
            return $this->getDbConnect()->query($sql_command);
        }


        // ### Liefter die Anzahl der Zeilen des Ergebnisses ###
        public function dbQueryNumRows($sql_result){
            return $sql_result->num_rows;
        }

        // ### Liefert einen Datensatz als assoziatives Array ###
        public function dbQueryFetchAssoc($sql_result){
            return $sql_result->fetch_assoc();
        }

        // ### Schließt die Verbindung zur Datenbank ###
        public function dbClose(){
            $this->getDbConnect()->close();
            $this->setDbConnect("");
        }
    }
?>