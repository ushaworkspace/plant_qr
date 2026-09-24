<?php
session_start();
include "db.php";

if(isset($_POST['login']))
{
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = mysqli_query($conn,"SELECT * FROM admin WHERE username='$username' AND password='$password'");

    if(mysqli_num_rows($result)>0)
    {
        $_SESSION['admin']=$username;
        header("Location:index.php");
        exit();
    }
    else
    {
        $error="Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Login</title>

<style>

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#e8f5e9;
}

.login-box{
    width:350px;
    margin:100px auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 10px gray;
}

h2{
    text-align:center;
    color:#2e7d32;
}

input{
    width:100%;
    padding:10px;
    margin-top:10px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:5px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    background:#2e7d32;
    color:white;
    border:none;
    border-radius:5px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#1b5e20;
}

.error{
    color:red;
    text-align:center;
}

</style>

</head>

<body>

<div class="login-box">

<h2>🌿 Admin Login</h2>

<?php
if(isset($error))
{
    echo "<p class='error'>$error</p>";
}
?>

<form method="POST">

<input type="text" name="username" placeholder="Username" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit" name="login">Login</button>

</form>

</div>

</body>
</html>
```
