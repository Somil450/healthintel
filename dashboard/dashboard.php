<?php
session_start();

/* ✅ STRICT LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../db.php";

$patient_id = (int) $_SESSION['user_id'];

/* TOTAL DISEASES */
$totalDiseases = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c 
     FROM patient_disease_history 
     WHERE patient_id = $patient_id"
))['c'] ?? 0;

/* CRITICAL CASES */
$criticalCases = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c 
     FROM patient_disease_history 
     WHERE patient_id = $patient_id 
       AND status = 'Critical'"
))['c'] ?? 0;

/* ACTIVE CASES */
$activeCases = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS c 
     FROM patient_disease_history 
     WHERE patient_id = $patient_id 
       AND status = 'Active'"
))['c'] ?? 0;

/* LAST DIAGNOSIS */
$lastVisit = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT MAX(detected_date) AS d 
     FROM patient_disease_history 
     WHERE patient_id = $patient_id"
))['d'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HealthIntel Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <h2>HealthIntel</h2>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- DASHBOARD STATS -->
<div class="dashboard">

    <div class="card stat">
        <h3>Total Diseases</h3>
        <p><?= (int)$totalDiseases ?></p>
    </div>

    <div class="card stat danger">
        <h3>Critical Cases</h3>
        <p><?= (int)$criticalCases ?></p>
    </div>

    <div class="card stat warning">
        <h3>Active Treatments</h3>
        <p><?= (int)$activeCases ?></p>
    </div>

    <div class="card stat">
        <h3>Last Diagnosis</h3>
        <p><?= $lastVisit ? htmlspecialchars($lastVisit) : 'N/A' ?></p>
    </div>

</div>

<!-- ACTION BUTTONS -->
<div class="actions">
    <a class="btn" href="../patient/patient_profile.php">
        View Patient Timeline
    </a>

    <a class="btn secondary" href="../patient/add_history.php">
        Add New Disease
    </a>
</div>

</body>
</html>
