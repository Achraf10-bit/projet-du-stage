<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include "config.php";

if (isset($_POST['upload'])) {
    if (isset($_POST['contract_id'])) {
        $contract_id = intval(explode(' - ', $_POST['contract_id'])[0]);
    } else {
        $contract_id = "NULL";
    }
    $type = $_POST['type'];
    $file = $_FILES['file']['name'];
    $target = "uploads/".basename($file);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $sql = "INSERT INTO documents (contract_id, type, file_path) VALUES ($contract_id, '$type', '$target')";
        if ($conn->query($sql)) {
            $success = "Document téléchargé avec succès !";
        } else {
            $error = "Erreur lors de l'insertion en base de données.";
        }
    } else {
        $error = "Erreur lors du téléchargement du fichier.";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM documents WHERE id=$id");
}

$contracts = $conn->query("SELECT DISTINCT c.id, c.name 
                          FROM contracts c 
                          LEFT JOIN documents d ON c.id = d.contract_id 
                          WHERE d.id IS NULL 
                          ORDER BY c.name");

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT d.*, c.name as contract_name, cl.name as client_name 
    FROM documents d 
    LEFT JOIN contracts c ON d.contract_id = c.id 
    LEFT JOIN clients cl ON c.client_id = cl.id";
if ($search) {
    $sql .= " WHERE 
        d.type LIKE '%$search%' OR 
        d.file_path LIKE '%$search%' OR 
        c.name LIKE '%$search%' OR 
        cl.name LIKE '%$search%'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des documents</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Gestion des documents</h2>
<a href="tableau_de_bord.php">← Retour au tableau de bord</a>
<hr>

<h3>Télécharger un document</h3>

<?php if(isset($success)): ?>
    <div style="color: green; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0;">
        <?= $success ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div style="color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0;">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" id="uploadForm">
    <label>Document :</label>
    <input list="contractOptions" name="contract_id" id="contractInput" placeholder="Tapez ou sélectionnez un contrat">
    <datalist id="contractOptions">
        <?php while($c = $contracts->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?> - <?= htmlspecialchars($c['name']) ?>">
        <?php endwhile; ?>
    </datalist>
    <br>
    <label>Type de document :</label>
    <select name="type">
        <option value="Contrat">Contrat</option>
        <option value="Facture">Facture</option>
        <option value="Devis">Devis</option>
        <option value="Bon de commande">Bon de commande</option>
        <option value="Récépissé">Récépissé</option>
    </select><br>
    <label>Fichier :</label>
    <input type="file" name="file" required id="fileInput"><br>
    <button type="submit" name="upload">Télécharger</button>
</form>

<script>
document.getElementById('uploadForm').addEventListener('submit', function() {
    setTimeout(function() {
        document.getElementById('fileInput').value = '';
    }, 100);
});

const searchInput = document.querySelector('input[name="search"]');
const searchForm = searchInput.form;
searchInput.addEventListener('input', function() {
    if (this.value === '') {
        searchForm.submit();
    }
});
</script>

<h3>Liste des documents</h3>

<form method="get" style="margin-bottom:20px;">
    <input type="text" name="search" placeholder="Rechercher un document..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
    <button type="submit">Rechercher</button>
</form>

<table>
<tr><th>ID</th><th>Client</th><th>Contrat</th><th>Type</th><th>Fichier</th><th>Actions</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['client_name'] ?? '-' ?></td>
    <td><?= $row['contract_name'] ?? '-' ?></td>
    <td><?= $row['type'] ?></td>
    <td><a href="<?= $row['file_path'] ?>" target="_blank">Voir</a></td>
    <td><a href="documents.php?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
