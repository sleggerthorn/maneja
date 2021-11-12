<?php
session_cache_limiter('private_no_expire');
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/models/model.newEmployee.php");
if($_POST["employee"] == "1"){
    $new = new newEmployeeModel($_POST["employee"]);
    echo $new->showModel();
}else{
    echo "Bitte loggen Sie sich ein!";
}
?>