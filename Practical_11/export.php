
<?php

require_once "db.php";

// Fetch active student records for CSV export.
$sql = "SELECT id, name, email, course, gender, status, created_at
        FROM students
        WHERE status = 'active'
        ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    exit("Unable to export student records.");
}

// Send CSV download headers.
header("Content-Type: text/csv; charset=UTF-8");
header('Content-Disposition: attachment; filename="student_records.csv"');
header("Cache-Control: no-store, no-cache, must-revalidate");

$output = fopen("php://output", "w");

// Write column headings.
fputcsv($output, [
    "ID", "Name", "Email", "Course", "Gender", "Status", "Created At"
]);

// Write student records.
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row["id"],
        $row["name"],
        $row["email"],
        $row["course"],
        $row["gender"],
        $row["status"],
        $row["created_at"]
    ]);
}

fclose($output);
$result->free();
$conn->close();
exit;

?>
