<?php
session_start();
include "../db.php";

$error = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, password FROM users WHERE email=?"
    );
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {

            $_SESSION['user'] = $email;
            $_SESSION['user_id'] = $row['user_id'];

            header("Location: ../dashboard/dashboard.php");
            exit;
        }
    }

    $error = "Invalid email or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HealthIntel Login</title>
<link rel="stylesheet" href="../assets/style.css">

<style>
.password-box {
    position: relative;
}
.password-box input {
    width: 100%;
    padding-right: 45px;
}
.password-box span {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
}
</style>
</head>

<body>

<div class="card" style="max-width:400px;margin:120px auto;">
    <h2 style="text-align:center;">HealthIntel Login</h2>

    <?php if (isset($_GET['reset'])): ?>
        <p style="color:green;text-align:center;">
            Password reset successful. Please login.
        </p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color:red;text-align:center;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input type="email"
               name="email"
               placeholder="Email"
               required>

        <div class="password-box">
            <input type="password"
                   id="loginPass"
                   name="password"
                   placeholder="Password"
                   required>
            <span onclick="toggleLogin()">👁</span>
        </div>

        <button class="btn" name="login" style="margin-top:15px;">
            Login
        </button>
    </form>

    <p style="text-align:center;margin-top:15px;">
        <a href="forgot_password.php">Forgot Password?</a>
    </p>

    <p style="text-align:center;">
        New user? <a href="register.php">Register</a>
    </p>
</div>

<script>
function toggleLogin() {
    const pass = document.getElementById("loginPass");
    pass.type = pass.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
