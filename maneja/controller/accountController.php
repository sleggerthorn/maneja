<?php
    if(!empty($_REQUEST["action"]) && isset($_REQUEST["action"])){
        switch ($_REQUEST["action"]) {
            case 'change':
                include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.accountHandler.php");
                $command = new accountHandler();
                echo $command->changeUserProfile($_REQUEST["usr_name"], $_REQUEST["usr_surname"], $_REQUEST["usr_job"], $_REQUEST["usr_tooladmin"],  $_REQUEST["usr_email"], $_REQUEST["usr_id"]);
                break;
            case 'delete':
                include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.accountHandler.php");
                $command = new accountHandler();
                echo $command->dropUserProfile($_REQUEST["usr_name"], $_REQUEST["usr_surname"], $_REQUEST["usr_tooladmin"],  $_REQUEST["usr_email"], $_REQUEST["usr_id"]);
                break;
            case 'create':
                include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.accountHandler.php");
                $command = new accountHandler();
                echo $command->createUser($_REQUEST["usr_name"], $_REQUEST["usr_surname"], $_REQUEST["usr_password"], $_REQUEST["usr_email"], $_REQUEST["usr_company"], $_REQUEST["usr_job"], $_REQUEST["usr_tooladmin"], $_REQUEST["action"]);
                break;
            default:
                echo "ERROR: Cannot set Parameters due to unrecognized action";
                break;
        }
    }
?>