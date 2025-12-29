<?php
function calculateRisk($severity, $status) {
    if ($status === "Critical") return "High Risk";
    if ($severity === "Severe") return "Medium Risk";
    return "Low Risk";
}

function nextAction($risk) {
    if ($risk === "High Risk") return "Immediate hospital visit required";
    if ($risk === "Medium Risk") return "Consult specialist within 7 days";
    return "Monitor health and follow lifestyle advice";
}
?>
