<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
include "config.php";

$clientsResult = $conn->query("SELECT COUNT(*) as c FROM clients");
$totalClients = $clientsResult ? $clientsResult->fetch_assoc()['c'] : 0;

$contractsResult = $conn->query("SELECT COUNT(*) as c FROM contracts");
$totalContracts = $contractsResult ? $contractsResult->fetch_assoc()['c'] : 0;

$documentsResult = $conn->query("SELECT COUNT(*) as c FROM documents WHERE type='Facture'");
$totalInvoices = $documentsResult ? $documentsResult->fetch_assoc()['c'] : 0;

$currentMonth = date('Y-m');
$currentYear = date('Y');

$monthlyRevenueQuery = "SELECT COALESCE(SUM(amount), 0) as revenue 
                       FROM contracts 
                       WHERE DATE_FORMAT(signature_date, '%Y-%m') = '$currentMonth'";
$monthlyRevenueResult = $conn->query($monthlyRevenueQuery);
$monthlyRevenue = $monthlyRevenueResult ? $monthlyRevenueResult->fetch_assoc()['revenue'] : 0;

$treasuryQuery = "SELECT COALESCE(SUM(amount), 0) as cash_in 
                 FROM contracts";
$treasuryResult = $conn->query($treasuryQuery);
$treasury = $treasuryResult ? $treasuryResult->fetch_assoc()['cash_in'] : 0;

$vatQuery = "SELECT COALESCE(SUM((amount * tax_rate) / 100), 0) as vat_to_pay 
             FROM contracts 
             WHERE tax_required = 1";
$vatResult = $conn->query($vatQuery);
$vatToPay = $vatResult ? $vatResult->fetch_assoc()['vat_to_pay'] : 0;

$topClientsQuery = "SELECT cl.name, COALESCE(SUM(c.amount), 0) as total_revenue 
                    FROM clients cl 
                    LEFT JOIN contracts c ON cl.id = c.client_id 
                    GROUP BY cl.id, cl.name 
                    HAVING total_revenue > 0
                    ORDER BY total_revenue DESC 
                    LIMIT 5";
$topClientsResult = $conn->query($topClientsQuery);

$chartDataQuery = "SELECT DATE_FORMAT(signature_date, '%Y-%m') as month, 
                          COALESCE(SUM(amount), 0) as revenue 
                   FROM contracts 
                   WHERE signature_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                   GROUP BY DATE_FORMAT(signature_date, '%Y-%m') 
                   ORDER BY month";
$chartDataResult = $conn->query($chartDataQuery);
$chartData = [];
if ($chartDataResult) {
    while ($row = $chartDataResult->fetch_assoc()) {
        $chartData[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord financier</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>📊 Tableau de bord financier</h2>
    <p>Bienvenue, <?php echo $_SESSION['user']; ?> | <a href="logout.php">Déconnexion</a></p>
    
    <div class="navigation" style="display: flex !important; justify-content: center !important; align-items: center !important; gap: 20px !important; margin: 25px auto !important; width: 100% !important; text-align: center !important;">
        <a href="clients.php" class="nav-btn" style="background: #3498db; color: white; padding: 16px 32px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);">👥 Gérer les clients</a>
        <a href="contracts.php" class="nav-btn" style="background: #3498db; color: white; padding: 16px 32px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);">📋 Gérer les contrats</a>
        <a href="documents.php" class="nav-btn" style="background: #3498db; color: white; padding: 16px 32px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);">📁 Gérer les documents</a>
    </div>

    <div class="financial-dashboard" style="display: flex !important; flex-direction: row !important; justify-content: center !important; gap: 15px !important; flex-wrap: nowrap !important;">
        <div class="financial-card primary" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>💰 Chiffre d'Affaires Mensuel</h3>
            <div class="amount"><?= number_format($monthlyRevenue, 2) ?> MAD</div>
            <small>Mois en cours (<?= date('F Y') ?>)</small>
        </div>
        
        <div class="financial-card success" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>💵 Trésorerie</h3>
            <div class="amount"><?= number_format($treasury, 2) ?> MAD</div>
            <small>Total des factures payées</small>
        </div>
        
        <div class="financial-card warning" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>📋 TVA à Payer</h3>
            <div class="amount"><?= number_format($vatToPay, 2) ?> MAD</div>
            <small>Obligations fiscales</small>
        </div>
        
        <div class="financial-card info" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>📈 Contrats Actifs</h3>
            <div class="amount"><?= $totalContracts ?></div>
            <small>En cours d'exécution</small>
        </div>
        
        <div class="financial-card clients" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>🏆 Top Clients par CA</h3>
            <div class="liste">
                <?php if ($topClientsResult && $topClientsResult->num_rows > 0): ?>
                    <?php 
                    $count = 0;
                    while ($client = $topClientsResult->fetch_assoc()): 
                        if ($count >= 3) break;
                    ?>
                        <div class="element">
                            <span class="nom"><?= $client['name'] ?></span>
                            <span class="revenu"><?= number_format($client['total_revenue'], 2) ?> MAD</span>
                        </div>
                    <?php 
                        $count++;
                    endwhile; ?>
                <?php else: ?>
                    <p style="font-size: 12px; color: #7f8c8d;">Aucun client actif</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="financial-card stats" style="width: 200px !important; flex-shrink: 0 !important; display: inline-block !important; float: left !important;">
            <h3>📊 Statistiques</h3>
            <div class="stats">
                <div class="stat">
                    <span class="etiquette">👥 Clients</span>
                    <span class="valeur"><?= $totalClients ?></span>
                </div>
                <div class="stat">
                    <span class="etiquette">📄 Factures</span>
                    <span class="valeur"><?= $totalInvoices ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="graphique">
        <h3>📊 Évolution du CA (6 derniers mois)</h3>
        <canvas id="revenueChart" width="150" height="50"></canvas>
    </div>

    <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const chartData = <?= json_encode($chartData) ?>;
    
    const labels = chartData.map(item => {
        const date = new Date(item.month + '-01');
        return date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
    });
    
    const revenues = chartData.map(item => parseFloat(item.revenue));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chiffre d\'Affaires (MAD)',
                data: revenues,
                borderColor: '#2980b9',
                backgroundColor: 'rgba(41, 128, 185, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('fr-FR') + ' MAD';
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
