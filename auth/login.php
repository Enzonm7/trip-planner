<?php 

session_start();
require("../config/db.php");

if ($_SERVER['REQUEST_METHOD']=="POST"){
    $email=$_POST["email"];
    $pswd=$_POST['pswd'];

    
    $req=$pdo->prepare("SELECT * FROM users WHERE email=?");
    $req->execute([$email]);
    $user=$req->fetch();

    //If the user enters the button login
    if ($_POST['action']=="login"){
        if ($email=="" || $pswd==""){
            $error="Please enter all the informations";
            echo $error;
        }else{
            //We verify the hashed password from the database corresponds to the one the user put
            if ($user && password_verify($pswd,$user["password_hash"])){
                $_SESSION['id']= $user['id'];
                $_SESSION['username']=$user['username'];
                header('Location: ../trips/user_home.php');
                exit();
            }else{
                $error="The credentials given to us were not correct";
                echo $error;
            }
        }
    }if ($_POST['action']=='back'){
        header('Location: ../index.php');
        exit();
    }

    if ($_POST['action']=='register'){
        header("Location: register.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication</title>
</head>
<body>
    <h1>Authentication</h1>
    <form action="" method="POST">
        <input type="hidden" name="action" value="login">
        <input type="text" placeholder="Email" name="email">
        <input type="password" placeholder="Password" name="pswd">
        <button type="submit">Login</button>
    </form>

    <form action="" method="POST">
        <input type="hidden" name="action" value="back">
        <button type="submit">Back</button>
    </form>

    <form action="" method="POST">
        <input type="hidden" name="action" value="register">
        <button type="submit">Create an account</button>
    </form>
</body>
</html>