<?php
    include_once($_SERVER["DOCUMENT_ROOT"]."/classes/class.serviceHandler.php");
    if(!empty($_REQUEST["action"]) && isset($_REQUEST["action"])){
        switch ($_REQUEST["action"]) {
            case 'activate':
                $command = new serviceHandler();
                echo $command->changeServerAccess($_REQUEST["acssrv_ip"], $_REQUEST["acssrv_port"],  $_REQUEST["acssrv_name"], $_REQUEST["usr_password"], $_REQUEST["usr_id"], $_REQUEST["usr_email"], 'activate');
                break;
            case 'deactivate':
                $command = new serviceHandler();
                echo $command->changeServerAccess($_REQUEST["acssrv_ip"], $_REQUEST["acssrv_port"], $_REQUEST["acssrv_name"], $_REQUEST["usr_password"], $_REQUEST["usr_id"], $_REQUEST["usr_email"], 'deactivate');
                break;
            case 'delete':
                $command = new serviceHandler();
                echo $command->changeServerAccess($_REQUEST["acssrv_ip"], $_REQUEST["acssrv_port"], $_REQUEST["acssrv_name"], $_REQUEST["usr_password"], $_REQUEST["usr_id"], $_REQUEST["usr_email"], 'delete');
                break;
            case 'create':
                $command = new serviceHandler();
                echo $command->createServer($_REQUEST["servername"], $_REQUEST["acssrv_ip"], $_REQUEST["acssrv_port"], $_REQUEST["acssrv_user"], $_REQUEST["acssrv_pass"], $_REQUEST["acssrv_com_cre"], $_REQUEST["acssrv_com_act"], $_REQUEST["acssrv_com_deact"], $_REQUEST["acssrv_com_del"], $_REQUEST["acssrv_deletable"], $_REQUEST["action"]);
                break;
            case 'change':
                $command = new serviceHandler();
                echo $command->alterServer($_REQUEST["acssrv_id"], $_REQUEST["acssrv_name"], $_REQUEST["acssrv_ip"], $_REQUEST["acssrv_port"], $_REQUEST["acssrv_create_com"], $_REQUEST["acssrv_activate_com"], $_REQUEST["acssrv_deactivate_com"], $_REQUEST["acssrv_delete_com"], $_REQUEST["acssrv_deletable"], $_REQUEST["action"]);
                break;
            case 'example':
                $command = new serviceHandler();
                echo $command->showExampleCommand($_REQUEST["exampleC"]);
                break;
            default:
                echo "ERROR: Cannot set Parameters due to unrecognized action";    
                break;
        }
    }
?>
