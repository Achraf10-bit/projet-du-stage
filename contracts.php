<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include "config.php";

if (isset($_POST['add'])) {
    $client_id = $_POST['client_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $contract_type = $_POST['contract_type'];
    $currency = $_POST['currency'];
    $amount = $_POST['amount'];
    $tax_required = isset($_POST['tax_required']) ? 1 : 0;
    $tax_rate = $_POST['tax_rate'];
    $payment_terms = $_POST['payment_terms'];
    $signature_date = $_POST['signature_date'];
    $expiry_date = $_POST['expiry_date'];
    $status = $_POST['status'];

    $year = date('Y');
    $count_result = $conn->query("SELECT COUNT(*) as count FROM contracts WHERE YEAR(created_at) = $year");
    $count = $count_result->fetch_assoc()['count'];
    $contract_number = 'CON-' . $year . '-' . str_pad(($count + 1), 3, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO contracts (client_id, contract_number, name, description, contract_type, currency, amount, tax_required, tax_rate, payment_terms, signature_date, expiry_date, status) 
            VALUES ($client_id, '$contract_number', '$name', '$description', '$contract_type', '$currency', $amount, $tax_required, $tax_rate, '$payment_terms', '$signature_date', '$expiry_date', '$status')";
    $conn->query($sql);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM contracts WHERE id=$id");
}

$clients = $conn->query("SELECT id,name FROM clients");


$result = $conn->query("SELECT c.*,cl.name as client_name FROM contracts c JOIN clients cl ON c.client_id=cl.id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des contrats</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Gestion des contrats</h2>
<a href="tableau_de_bord.php">← Retour au tableau de bord</a>
<hr>

<h3>Ajouter un nouveau contrat</h3>
<form method="post" class="contract-form">
    <div class="form-row">
        <label>Client :</label>
        <select name="client_id" required>
            <?php while($c = $clients->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="form-row">
        <label>Titre du contrat :</label>
        <input type="text" name="name" required>
    </div>
    
    <div class="form-row">
        <label>Description :</label>
        <textarea name="description" rows="3"></textarea>
    </div>
    
    <div class="form-row">
        <label>Type de contrat :</label>
        <select name="contract_type" required>
            <option value="Régie">Régie (Prestation en mode temps et matériels)</option>
            <option value="Forfait">Forfait</option>
            <option value="Maintenance_et_Support">Maintenance et Support</option>
            <option value="Vente_Marchandises">Vente de Marchandises</option>
            <option value="Prestation_Services">Prestation de Services</option>
            <option value="Partenariat_Commercial">Partenariat Commercial</option>
            <option value="NDA">Accord de confidentialité (NDA)</option>
        </select>
    </div>
    
    <div class="form-row">
        <label>Devise :</label>
        <select name="currency">
            <option value="MAD">MAD (Dirham marocain)</option>
            <option value="EUR">EUR (Euro)</option>
            <option value="USD">USD (Dollar américain)</option>
        </select>
    </div>
    
    <div class="form-row">
        <label>Montant HT :</label>
        <input type="number" name="amount" step="0.01">
    </div>
    
    <div class="form-row">
        <label>TVA requise :</label>
        <div class="checkbox-group">
            <input type="checkbox" name="tax_required" checked> Oui
        </div>
    </div>
    
    <div class="form-row">
        <label>Taux de TVA (%) :</label>
        <input type="number" name="tax_rate" value="20" step="0.01">
    </div>
    
    <div class="form-row">
        <label>Conditions de paiement :</label>
        <input type="text" name="payment_terms">
    </div>
    
    <div class="form-row">
        <label>Date de signature :</label>
        <input type="date" name="signature_date">
    </div>
    
    <div class="form-row">
        <label>Date d'expiration :</label>
        <input type="date" name="expiry_date">
    </div>
    
    <div class="form-row">
        <label>Statut :</label>
        <select name="status">
            <option value="Brouillon">Brouillon</option>
            <option value="Actif">Actif</option>
            <option value="Terminé">Terminé</option>
            <option value="Résilié">Résilié</option>
            <option value="Expiré">Expiré</option>
        </select>
    </div>
    
    <div class="form-row">
        <button type="submit" name="add">Ajouter le contrat</button>
    </div>
</form>

<h3>Liste des contrats</h3>
<table>
<tr><th>ID</th><th>Client</th><th>N° Contrat</th><th>Titre</th><th>Type</th><th>Montant HT</th><th>TVA</th><th>Montant TTC</th><th>Statut</th><th>Actions</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['client_name'] ?></td>
    <td><?= $row['contract_number'] ?></td>
    <td><?= $row['name'] ?></td>
    <td><?= $row['contract_type'] ?></td>
    <td><?= number_format($row['amount'], 2) ?> <?= $row['currency'] ?></td>
    <td>
        <?php 
        if ($row['tax_required'] && $row['amount'] > 0) {
            $tva_amount = ($row['amount'] * $row['tax_rate']) / 100;
            echo number_format($tva_amount, 2) . ' ' . $row['currency'];
        } else {
            echo '0.00 ' . $row['currency'];
        }
        ?>
    </td>
    <td>
        <?php 
        if ($row['tax_required'] && $row['amount'] > 0) {
            $total_ttc = $row['amount'] + (($row['amount'] * $row['tax_rate']) / 100);
            echo number_format($total_ttc, 2) . ' ' . $row['currency'];
        } else {
            echo number_format($row['amount'], 2) . ' ' . $row['currency'];
        }
        ?>
    </td>
    <td><?= $row['status'] ?></td>
    <td><a href="contracts.php?delete=<?= $row['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
