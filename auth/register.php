<?php
session_start();
include "../db.php";

if (isset($_POST['register'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $password);

    if (mysqli_stmt_execute($stmt)) {

        $user_id = mysqli_insert_id($conn);

        // Auto create patient
        mysqli_query($conn,"
            INSERT INTO patient (patient_id, name, region)
            VALUES ($user_id, '$name', 'India')
        ");

        header("Location: login.php");
        exit;
    } else {
        $error = "Email already exists";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="card" style="max-width:400px;margin:100px auto;">
<h2>Register</h2>

<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn" name="register">Register</button>
</form>

<p style="text-align:center">
Already registered? <a href="login.php">Login</a>
</p>
</div>

</body>
</html>
