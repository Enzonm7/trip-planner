<?php
session_start();
require("../config/db.php");

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    if ($_POST['action']=="register"){
        //We get the informations from what posted the user
        $username=$_POST["username"];
        $email=$_POST["email"];
        $pswd=$_POST["pswd"];

        //Verify that there is no user already existing having the same email or username
        $req1=$pdo->prepare("SELECT * FROM users WHERE email=?");
        $req1->execute([$email]);

        $check1=$req1->fetch();

        $req2=$pdo->prepare("SELECT * FROM users WHERE username=?");
        $req2->execute([$username]);

        $check2=$req2->fetch();

        if ($check1){
            $error3="Email already in use";
            echo $error3;
        }
        elseif ($check2){
            $error4="Username already in use";
            echo $error4;
        }
        //Verify that the user put informations
        elseif ($username=="" || $email=="" || $pswd==""){
            $error1="Please complete all informations";
            echo $error1;
        }
        elseif(!str_contains($email,"@")){
            $error2="Your email is incorrect";
            echo $error2;
        }
            
        else{
            //We hash the password and Insert into the database the informations the user has given us
            $pswd_hashed=password_hash($pswd,PASSWORD_DEFAULT);
            $req=$pdo->prepare("INSERT INTO users(username,email,password_hash) VALUES (?,?,?);");
            $req->execute([$username,$email,$pswd_hashed]);
            $result="Account Created";
            echo $result;
        }
    }
    if ($_POST['action']=="back"){
        header("Location: ../index.php");
        exit();
    }
    if ($_POST['action']=='login'){
        header("Location: login.php");
        exit();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Account Creation</h1>
    <form action="" method="POST">
        <input type="hidden" name="action" value="register">
        <input type="text" placeholder="Email" name="email">
        <input type="text" placeholder="Username" name="username">
        <input type="password" placeholder="Password" name="pswd">
        <button type="submit">Create</button>
    </form>
    <form action="" method="POST">
        <input type="hidden" name="action" value="back">
        <button type="submit">Back</button>
    </form>

    <form action="" method="POST">
        <input type="hidden" name="action" value="login">
        <button type="submit">Login</button>
    </form>
</body>
</html>