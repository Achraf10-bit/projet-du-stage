<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include "config.php";

if (isset($_POST['add'])) {
    $name = $_POST['name']; $email = $_POST['email'];
    $phone = $_POST['phone']; $company = $_POST['company'];
    $notes = $_POST['notes'];
    $sql = "INSERT INTO clients (name,email,phone,company,notes) VALUES ('$name','$email','$phone','$company','$notes')";
    $conn->query($sql);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM clients WHERE id=$id");
}

$result = $conn->query("SELECT * FROM clients");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des clients</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Gestion des clients</h2>
<a href="tableau_de_bord.php">← Retour au tableau de bord</a>
<hr>

<h3>Ajouter un nouveau client</h3>
<form method="post">
    <input type="text" name="name" placeholder="Nom" required>
    <input type="email" name="email" placeholder="Email">
    <input type="text" name="phone" placeholder="Téléphone">
    <input type="text" name="company" placeholder="Entreprise">
    <textarea name="notes" placeholder="Notes"></textarea>
    <button type="submit" name="add">Ajouter le client</button>
</form>

<h3>Liste des clients</h3>
<table>
<tr><th>ID</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Entreprise</th><th>Actions</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['phone'] ?></td>
    <td><?= $row['company'] ?></td>
    <td><a href="clients.php?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
