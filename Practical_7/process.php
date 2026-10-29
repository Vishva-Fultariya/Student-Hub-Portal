
<?php

/*
 * WDF Practical 7
 * PHP Form Processing
 *
 * Step 3:
 * - Handle POST request
 * - Read submitted data
 * - Perform server-side validation
 */


/* -----------------------------------------
   1. Check whether the form used POST
   ----------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}


/* -----------------------------------------
   2. Read submitted form data
   ----------------------------------------- */

$student_name = trim($_POST["student_name"] ?? "");
$email        = trim($_POST["email"] ?? "");
$phone        = trim($_POST["phone"] ?? "");
$course       = trim($_POST["course"] ?? "");
$semester     = trim($_POST["semester"] ?? "");
$message      = trim($_POST["message"] ?? "");


/* -----------------------------------------
   3. Create an errors array
   ----------------------------------------- */

$errors = [];


/* -----------------------------------------
   4. Server-side validation
   ----------------------------------------- */


/* Student Name */

if ($student_name === "") {

    $errors[] = "Student name is required.";

} elseif (!preg_match("/^[a-zA-Z ]+$/", $student_name)) {

    $errors[] = "Student name can contain only letters and spaces.";

} elseif (strlen($student_name) < 2) {

    $errors[] = "Student name must contain at least 2 characters.";
}


/* Email */

if ($email === "") {

    $errors[] = "Email address is required.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors[] = "Please enter a valid email address.";
}


/* Phone */

if ($phone === "") {

    $errors[] = "Phone number is required.";

} elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

    $errors[] = "Phone number must contain exactly 10 digits.";
}


/* Course */

$allowed_courses = [
    "B.Tech CSE",
    "B.Tech IT",
    "BCA",
    "MCA"
];

if ($course === "") {

    $errors[] = "Please select a course.";

} elseif (!in_array($course, $allowed_courses, true)) {

    $errors[] = "Invalid course selected.";
}


/* Semester */

$allowed_semesters = [
    "1",
    "2",
    "3",
    "4",
    "5",
    "6",
    "7",
    "8"
];

if ($semester === "") {

    $errors[] = "Please select a semester.";

} elseif (!in_array($semester, $allowed_semesters, true)) {

    $errors[] = "Invalid semester selected.";
}


/* Message */

if ($message === "") {

    $errors[] = "Message is required.";

} elseif (strlen($message) < 10) {

    $errors[] = "Message must contain at least 10 characters.";

} elseif (strlen($message) > 500) {

    $errors[] = "Message must not exceed 500 characters.";
}


/* -----------------------------------------
   5. Display validation errors
   ----------------------------------------- */

if (!empty($errors)) {

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Validation Error</title>";

    echo "<style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 40px 20px;
        }

        .message-card {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: #b91c1c;
            margin-bottom: 20px;
        }

        ul {
            padding-left: 22px;
            line-height: 1.8;
            color: #374151;
        }

        .back-button {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 18px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 7px;
        }

        .back-button:hover {
            background: #1d4ed8;
        }
    </style>";

    echo "</head>";
    echo "<body>";

    echo "<div class='message-card'>";

    echo "<h1>Validation Error</h1>";

    echo "<p>Please correct the following errors:</p>";

    echo "<ul>";

    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error, ENT_QUOTES, "UTF-8") . "</li>";
    }

    echo "</ul>";

    echo "<a class='back-button' href='index.php'>Go Back to Form</a>";

    echo "</div>";

    echo "</body>";
    echo "</html>";

    exit;
}


/* -----------------------------------------
   6. Define CSV file location
   ----------------------------------------- */

$data_directory = __DIR__ . "/data";
$csv_file = $data_directory . "/registrations.csv";


/* -----------------------------------------
   7. Create data directory if needed
   ----------------------------------------- */

if (!is_dir($data_directory)) {

    if (!mkdir($data_directory, 0755, true)) {

        die("Unable to create data directory.");
    }
}


/* -----------------------------------------
   8. Open CSV file safely
   ----------------------------------------- */

$file_handle = fopen($csv_file, "a");

if ($file_handle === false) {

    die("Unable to open CSV file.");
}


/* -----------------------------------------
   9. Lock the file before writing
   ----------------------------------------- */

if (!flock($file_handle, LOCK_EX)) {

    fclose($file_handle);

    die("Unable to lock CSV file.");
}


/* -----------------------------------------
   10. Add CSV header if file is empty
   ----------------------------------------- */

if (filesize($csv_file) === 0) {

    $header = [
        "Student Name",
        "Email",
        "Phone",
        "Course",
        "Semester",
        "Message",
        "Submitted At"
    ];

    fputcsv($file_handle, $header);
}


/* -----------------------------------------
   11. Prepare registration record
   ----------------------------------------- */

$record = [
    $student_name,
    $email,
    $phone,
    $course,
    $semester,
    $message,
    date("Y-m-d H:i:s")
];


/* -----------------------------------------
   12. Write record to CSV
   ----------------------------------------- */

if (!fputcsv($file_handle, $record)) {

    flock($file_handle, LOCK_UN);
    fclose($file_handle);

    die("Unable to save registration data.");
}


/* -----------------------------------------
   13. Unlock and close the file
   ----------------------------------------- */

flock($file_handle, LOCK_UN);

fclose($file_handle);


/* -----------------------------------------
   14. Display success message
   ----------------------------------------- */

echo "<!DOCTYPE html>";

echo "<html lang='en'>";

echo "<head>";

echo "<meta charset='UTF-8'>";

echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";

echo "<title>Registration Successful</title>";

echo "<style>

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f4f7fb;
        margin: 0;
        padding: 40px 20px;
    }

    .message-card {
        max-width: 600px;
        margin: 50px auto;
        background: #ffffff;
        padding: 35px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    h1 {
        color: #166534;
        margin-bottom: 15px;
    }

    p {
        color: #374151;
        line-height: 1.6;
    }

    .back-button {
        display: inline-block;
        margin-top: 15px;
        padding: 11px 18px;
        background: #2563eb;
        color: #ffffff;
        text-decoration: none;
        border-radius: 7px;
    }

    .back-button:hover {
        background: #1d4ed8;
    }

</style>";

echo "</head>";

echo "<body>";

echo "<div class='message-card'>";

echo "<h1>Registration Successful</h1>";

echo "<p>";
echo "Your registration has been successfully validated and stored.";
echo "</p>";

echo "<p>";
echo "<strong>Data has been saved to registrations.csv.</strong>";
echo "</p>";

echo "<a class='back-button' href='index.php'>";
echo "Submit Another Registration";
echo "</a>";

echo "<a class='back-button' href='records.php'>";
echo "View Stored Records";
echo "</a>";

echo "</div>";

echo "</body>";

echo "</html>";

?>