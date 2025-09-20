<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password === $confirm_password) {
        $check_sql = "SELECT * FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'employee')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $success = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de la création du compte.";
            }
        } else {
            $error = "Nom d'utilisateur déjà existant.";
        }
    } else {
        $error = "Les mots de passe ne correspondent pas.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Inscription</h2>
    <form method="post">
        <label>Nom d'utilisateur</label><br>
        <input type="text" name="username" required><br>
        <label>Mot de passe</label><br>
        <input type="password" name="password" required><br>
        <label>Confirmer le mot de passe</label><br>
        <input type="password" name="confirm_password" required><br><br>
        <button type="submit">S'inscrire</button>
    </form>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <?php if(isset($success)) echo "<p style='color:green'>Compte créé avec succès ! Vous pouvez maintenant vous connecter.</p>"; ?>
    <p><a href="login.php">Retour à la connexion</a></p>
</body>
</html>
