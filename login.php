<?php
include ('conn.php');
session_start();

$loginErrorMessage = "";
if (isset($_SESSION['login_error'])) {
    $loginErrorMessage = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$error = "";
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo "ERROR: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header('Location: greetingUser.php');
            exit();
        } else {
            $error = "Invalid Credentials";
        }
    } else {
        $error = "Invalid Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="icon" type="/png" href="gikonko.png" />
    <link href="output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-blue-200 flex justify-center items-center">
    <form action="" method="post" class="max-w-md w-full bg-green-400 p-8 shadow-2xl rounded-xl">
      
     <?php if (!empty($loginErrorMessage)): ?>
            <div class="text-red-500 rounded text-center mb-4">
                <?php echo $loginErrorMessage;?>
            </div>
     <?php endif; ?>  
     
        <h1 class="text-blue font-bold underline text-center text-blue-600 text-xl mb-6">Login Page</h1>
        <label class="font-bold block text-blue-500 text-lg">Username</label>
        <input type="text" name="username" 
        class="w-[76%] py-3 rounded-lg bg-green-200 shadow-lg focus:ring-2 focus:outline-green-400 mb-6 text-green-500"> <br>
        <label  class="font-bold text-blue-500 text-lg">Password</label>
        <input type="password" name="password" 
        class="w-[76%] py-3 rounded-lg bg-green-200 shadow-lg focus:ring-2 focus:outline-green-400 me-2 text-green-500 mb-6"> <br>

        <button name="login" class="bg-blue-400 w-[50%] py-3 text-white rounded-lg hover:bg-blue-500 shadow-2xl mb-6">Login</button>

        <div class="flex">
           <a href="loginStudent.php" class="text-blue-800 hover:underline transition duration-200">Login as Student</a>
        </div>
        <?php if (!empty($error)): ?>
        <div class="bg-red-200 border border-red-500 text-red-700 px-4 py-1 rounded  text-center">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
    </form>
</body>
</html>