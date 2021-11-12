<?php
session_cache_limiter('private_no_expire');
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/applications/maneja/models/model.right.php");
if(isset($_POST["usr_id"]) && !empty($_POST["usr_id"])){
    $start = new rightsModel($_POST["usr_id"]);
    echo $start->showModel();
}else{
    echo "Cannot call Site";
}
?>
