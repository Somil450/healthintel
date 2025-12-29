<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include "../db.php";
include "../engine/risk_engine.php";
include "../engine/disease_engine.php";
include "../engine/ai_summary_engine.php";
$patient_id = (int) $_SESSION['user_id'];
/* ================= DISEASE MAP ================= */
function diseaseMap($disease) {
    $d = strtolower($disease);
    if (strpos($d,'asthma')!==false) return ['Pulmonologist','Chest Hospital'];
    if (strpos($d,'heart')!==false) return ['Cardiologist','Cardiac Hospital'];
    if (strpos($d,'hypertension')!==false) return ['Cardiologist','Heart Clinic'];
    if (strpos($d,'cancer')!==false) return ['Oncologist','Cancer Hospital'];
    if (strpos($d,'covid')!==false) return ['General Physician','Multi-specialty Hospital'];
    return ['General Physician','Hospital'];
}
/* ================= PATIENT ================= */
$patient = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM patient WHERE patient_id=$patient_id")
);
/* ================= AI SUMMARY ================= */
$historyData = [];
$hq = mysqli_query($conn,"
    SELECT d.disease_name, h.status, h.severity_level
    FROM patient_disease_history h
    JOIN disease_master d ON h.disease_id=d.disease_id
    WHERE h.patient_id=$patient_id
");
while ($r = mysqli_fetch_assoc($hq)) $historyData[] = $r;
$aiSummary = generateHealthSummary($historyData, $patient['region']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Health Timeline</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="topbar">
    <h2 style="color:goldenrod">HEALTHINTEL</h2>
    <div>
        <button class="dark-toggle" onclick="toggleDark()">🌙</button>
        <a style="font-size:14px;padding-left:10px" href="../auth/logout.php">Logout</a>
    </div>
</div>
<div class="card">
    <p><b>Name:</b> <?= htmlspecialchars($patient['name']) ?></p>
    <p><b>Region:</b> <?= htmlspecialchars($patient['region']) ?></p>
</div>
<div class="card">
    <h2 style="color:goldenrod">🧠 AI Health Summary</h2>
    <?= $aiSummary ?>
</div>
<h2 style="color:goldenrod">Disease History</h2>
<div class="timeline">
<?php
$q = mysqli_query($conn,"
    SELECT d.disease_name, h.detected_date, h.status, h.severity_level
    FROM patient_disease_history h
    JOIN disease_master d ON h.disease_id=d.disease_id
    WHERE h.patient_id=$patient_id
    ORDER BY h.detected_date DESC
");
while ($row = mysqli_fetch_assoc($q)):
    [$specialist,$hospitalType] = diseaseMap($row['disease_name']);
    $risk = calculateRisk($row['severity_level'],$row['status']);
    $action = nextAction($risk);
?>
<div class="event <?= strtolower($row['status']) ?>">
    <h3 style="font-size:18px;color:blue"><?= htmlspecialchars($row['disease_name']) ?></h3>
    <br>
    <p><b>Date:</b> <?= $row['detected_date'] ?></p>
    <p><b>Status:</b> <?= $row['status'] ?></p>
    <p><b>Severity:</b> <?= $row['severity_level'] ?></p>
    <p><b>Risk:</b> <?= $risk ?></p>
    <p><b>Action:</b> <?= $action ?></p>

    <p><b>Recommended Specialist:</b> <?= $specialist ?></p>
    <p><b>Hospital Type:</b> <?= $hospitalType ?></p>
<br>
    <button class="btn" onclick="showMap(this,'<?= htmlspecialchars($hospitalType) ?>')">
        📍 Find Nearby
    </button>

    <div class="mapBox" style="margin-top:12px;"></div>

    <!-- Upload Report -->
    <form method="POST" action="upload_report.php"
          enctype="multipart/form-data" class="upload-row">
        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
        <input type="hidden" name="disease_name"
               value="<?= htmlspecialchars($row['disease_name']) ?>">
        <input type="file" name="report_file" required>
        <button class="btn">Upload</button>
        <button type="button" class="btn secondary"
                onclick="openReportsModal()">📂 My Reports</button>
    </form>
<br>
    <!-- Appointment -->
    <form method="POST" action="book_appointment.php">
        <input type="hidden" name="disease"
               value="<?= htmlspecialchars($row['disease_name']) ?>">
        <input type="hidden" name="specialty"
               value="<?= htmlspecialchars($specialist) ?>">
        <input type="hidden" name="hospital_type"
               value="<?= htmlspecialchars($hospitalType) ?>">

        <label>Date</label>
        <input type="date" name="date" required>

        <label>Time</label>
        <input type="time" name="time" required>

        <button class="btn">Book Appointment</button>
    </form>
</div>
<?php endwhile; ?>
</div>

<div class="actions">
    <a class="btn" href="add_history.php">Add Disease</a>
    <a class="btn secondary" href="../dashboard/dashboard.php">Back</a>
    <a class="btn" href="download_report.php">Download PDF</a>
    <a class="btn" href="my_appointments.php">My Appointments</a>
</div>

<!-- REPORTS MODAL -->
<div id="reportsModal" class="modal">
<div class="modal-content">
<button class="close-btn" onclick="closeReportsModal()">✖</button>
<h3>📂 My Reports</h3>

<?php
$reports = mysqli_query($conn,"
    SELECT report_id,file_name,file_path,disease_name,uploaded_at
    FROM medical_reports
    WHERE patient_id=$patient_id
    ORDER BY uploaded_at DESC
");

if (mysqli_num_rows($reports)>0):
while ($rep=mysqli_fetch_assoc($reports)):
?>
<div class="report-row">
    <div>
        <b><?= htmlspecialchars($rep['file_name']) ?></b><br>
        <small><?= htmlspecialchars($rep['disease_name']) ?> |
        <?= $rep['uploaded_at'] ?></small>
    </div>
    <div class="report-actions">
        <a class="btn small" target="_blank"
           href="<?= htmlspecialchars($rep['file_path']) ?>">View</a>

        <form method="POST" action="delete_report.php"
              onsubmit="return confirm('Delete this report?')">
            <input type="hidden" name="report_id"
                   value="<?= $rep['report_id'] ?>">
            <button class="btn danger small">Delete</button>
        </form>
    </div>
</div>
<?php endwhile; else: ?>
<p>No reports uploaded.</p>
<?php endif; ?>
</div>
</div>

<script>
function toggleDark(){document.body.classList.toggle("dark");}
function openReportsModal(){document.getElementById("reportsModal").style.display="flex";}
function closeReportsModal(){document.getElementById("reportsModal").style.display="none";}

function showMap(btn, hospitalType) {
    const box = btn.closest(".event").querySelector(".mapBox");
    box.innerHTML = "📍 Fetching location...";

    navigator.geolocation.getCurrentPosition(pos => {
        const q = encodeURIComponent(hospitalType+" near "+pos.coords.latitude+","+pos.coords.longitude);
        box.innerHTML = `
            <a class="btn" target="_blank"
               href="https://www.google.com/maps/search/${q}">
               📍 Open in Google Maps
            </a>
            <iframe src="https://www.google.com/maps?q=${q}&output=embed"
                width="100%" height="260" style="border:0;border-radius:12px;margin-top:10px;">
            </iframe>`;
    }, ()=> box.innerHTML="❌ Location denied");
}
</script>

</body>
</html>
