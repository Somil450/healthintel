<?php
function diseaseCategory($disease) {
    $map = [
        "Diabetes" => "Endocrinology",
        "Cancer" => "Oncology",
        "Heart Attack" => "Cardiology",
        "Asthma" => "Pulmonology"
    ];
    return $map[$disease] ?? "General Medicine";
}
?>
