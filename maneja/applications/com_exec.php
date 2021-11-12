<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/classes/class.targetHandler.php");
$command = new targetHandler();
if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "change"){
    $command->changeUserProfile($_REQUEST["usr_name"], $_REQUEST["usr_surname"], $_REQUEST["usr_tooladmin"],  $_REQUEST["usr_email"], $_REQUEST["usr_id"]);
}elseif(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "activate" || $_REQUEST["action"] == "deactivate" || $_REQUEST["action"] == "delete")){
    $command->changeServerAccess($_REQUEST["acssrv_ip"], "22", "ssh", $_REQUEST["acssrv_name"], $_REQUEST["usr_password"], $_REQUEST["usr_user"], $_REQUEST["usr_email"], $_REQUEST["action"]);
}elseif($_REQUEST["action"] == "create"){
    $command->createUser($_REQUEST["usr_name"], $_REQUEST["usr_surname"], $_REQUEST["usr_user"], $_REQUEST["usr_user_med"], $_REQUEST["usr_password"], $_REQUEST["usr_email"], $_REQUEST["usr_company"], $_REQUEST["usr_tooladmin"], $_REQUEST["action"]);
}else{
    echo "ERROR: Cannot set Parameters due to unrecognized action";
}
?>