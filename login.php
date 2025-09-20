<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            header("Location: tableau_de_bord.php");
            exit;
        }
    }
    $error = "Invalid credentials";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Connexion</h2>
    <form method="post">
        <label>Nom d'utilisateur</label><br>
        <input type="text" name="username" required><br>
        <label>Mot de passe</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Se connecter</button>
    </form>
    <?php if(isset($error)) echo "<p style='color:red; margin-top: 15px;'>$error</p>"; ?>
    <p><a href="register.php">Créer un nouveau compte</a></p>
</body>
</html>

