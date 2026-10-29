
<?php
session_start();
require_once "db.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Validate the student ID.
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION["message"] = "Invalid student ID.";
    $_SESSION["message_type"] = "error";
    header("Location: index.php");
    exit;
}

// Fetch the existing active student.
$stmt = $conn->prepare(
    "SELECT id, name, email, course, gender
     FROM students
     WHERE id = ? AND status = 'active'"
);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    $_SESSION["message"] = "Student record not found or is inactive.";
    $_SESSION["message_type"] = "error";
    header("Location: index.php");
    exit;
}

$error = "";

// Process the update form.
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_student"])) {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $gender = trim($_POST["gender"] ?? "");

    if (
        $name === "" ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 100 ||
        !in_array($course, ["Computer Science", "Web Development", "Data Science"], true) ||
        !in_array($gender, ["Male", "Female", "Other"], true)
    ) {
        $error = "Please enter valid student details.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, course = ?, gender = ?
             WHERE id = ? AND status = 'active'"
        );
        $stmt->bind_param("ssssi", $name, $email, $course, $gender, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows >= 0) {
                $_SESSION["message"] = "Student details updated successfully.";
                $_SESSION["message_type"] = "success";
                $stmt->close();
                header("Location: index.php");
                exit;
            }
        }

        $error = "Unable to update student details. Please try again.";
        $stmt->close();
    }

    // Preserve submitted values when validation fails.
    $student["name"] = $name;
    $student["email"] = $email;
    $student["course"] = $course;
    $student["gender"] = $gender;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Practical 11</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page">
    <section class="panel">
        <h1>Edit Student Details</h1>
        <p class="subtitle">Update the selected student's information.</p>

        <?php if ($error !== ""): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= (int)$id ?>"
              class="student-form">

            <div class="field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       maxlength="100" required
                       value="<?= e($student["name"]) ?>">
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       maxlength="100" required
                       value="<?= e($student["email"]) ?>">
            </div>

            <div class="field">
                <label for="course">Course</label>
                <select id="course" name="course" required>
                    <?php foreach ([
                        "Computer Science", "Web Development", "Data Science"
                    ] as $course_option): ?>
                        <option value="<?= e($course_option) ?>"
                            <?= $student["course"] === $course_option ? "selected" : "" ?>>
                            <?= e($course_option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <?php foreach (["Male", "Female", "Other"] as $gender_option): ?>
                        <option value="<?= e($gender_option) ?>"
                            <?= $student["gender"] === $gender_option ? "selected" : "" ?>>
                            <?= e($gender_option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="update_student" class="btn primary">
                Save Changes
            </button>

            <a href="index.php" class="btn secondary">Cancel</a>
        </form>
    </section>
</div>
</body>
</html>
