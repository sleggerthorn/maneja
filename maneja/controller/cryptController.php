<?php
    $passphrase = array("A", "a", "B", "b", "C", "c", "D", "d", "E", "e", "F", "f", "G", "g", "H", "h", "I", "i", "J", "j", "K", "k", "L", "l", "M", "m", "N", "n", "O", "o", "P", "p", "Q", "q", "R", "r", "S", "s", "T", "t", "U", "u", "V", "v", "W", "w", "X", "x", "Y", "y", "Z", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "+", "=");
    $key = "";
    if(empty($_POST["key"])){
        for($i=0; $i < 96; $i++){
            $key .= $passphrase[rand(0, 63)];
        }
    }else{
        $key = $_POST["key"];
    }
    $ext = "a";
    $smth = base64_encode(openssl_encrypt($_POST["password"], "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14"));
    for($b = strlen($smth); $b < 56; $b++){
        $ext .= $passphrase[rand(0, 63)];
    };
    echo base64_encode(openssl_encrypt($_POST["password"], "aes-256-ctr", $key, OPENSSL_RAW_DATA, "GkTaUuYiAy10RZ14"))." KEY: ".$key. " EXTENTION: ".$ext;
?>