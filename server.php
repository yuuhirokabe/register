<?php
require_once 'config.php';

if($_POST['action'] == "insert_user"){
    $errors = false;
    $username = htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');//Te Valdemārs
    $password = $_POST['password'];//Te Valdemārs
    $verify_password = $_POST['verify_password'];
    //Kāds šifrēšanas algoritms? Vai DB `users` tabulā vajag kolonu `salt`?
    $output = [];

    if(strlen($username) < 4){
        $errors = true;
        $output["username_error"] = true;
        $output["username_msg"] = "Lietotājvārdam jābūt vismaz 5 simbolus garam";
    } else {
        //Ja ievadīts lietotājvārds, kurš garāks par 4 simboliem, tad pārbauda vai tāds jau eksistē DB
        $is_available = "SELECT * FROM users WHERE username = '$username'";
        if($result = mysqli_query($con, $is_available)){
            if(mysqli_num_rows($result) == 1){
                $errors = true;
                $output["username_error"] = true;
                $output["username_msg"] = "Lietotājvārds jau ir aizņemts";
            }
        }
    }

    //Ja paroles nesakrīt, tad izvada, ka paroles nesakrīt
    if($password != $verify_password) {
        $errors = true;
        $output["verify_password_error"] = true;
        $output["verify_password_msg"] = "Paroles nesakrīt";
    }

    if(strlen($password) < 4){
        $errors = true;
        $output["password_error"] = true;
        $output["password_msg"] = "Parolei jābūt vismaz 5 simbolus garai";
    }
    if(!empty($output)){
        echo json_encode($output);
    }

    if(!$errors){//Ja nav eroru, tad reģistrē
        $insert_query = "INSERT INTO users (username, password, joined) VALUES ('$username', '$password', NOW())";
        $result = mysqli_query($con, $insert_query);
        if($result){
            $output = array(
                "success" => true,
                "msg"   => "Lietotājs veiksmīgi reģistrēts"
            );
            echo json_encode($output);
        }
    }


    
}
?>