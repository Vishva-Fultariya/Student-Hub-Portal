
<?php
session_start();
require_once "db.php";

// Escape output before displaying it in HTML.
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Handle messages after redirects.
$message = $_SESSION["message"] ?? "";
$message_type = $_SESSION["message_type"] ?? "success";
unset($_SESSION["message"], $_SESSION["message_type"]);

// Handle adding a student.
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_student"])) {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $gender = trim($_POST["gender"] ?? "");

    if (
        $name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($course, ["Computer Science", "Web Development", "Data Science"], true) ||
        !in_array($gender, ["Male", "Female", "Other"], true)
    ) {
        $_SESSION["message"] = "Please enter valid student details.";
        $_SESSION["message_type"] = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students (name, email, course, gender)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $course, $gender);

        if ($stmt->execute()) {
            $_SESSION["message"] = "Student added successfully.";
            $_SESSION["message_type"] = "success";
        } else {
            $_SESSION["message"] = "Unable to add student. Please try again.";
            $_SESSION["message_type"] = "error";
        }

        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

// Read search and filter inputs.
$search = trim($_GET["search"] ?? "");
$course_filter = trim($_GET["course_filter"] ?? "");

// Build a safe search query using MySQLi prepared statements.
$sql = "SELECT id, name, email, course, gender, status, created_at
        FROM students
        WHERE status = 'active'";

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR course LIKE ?)";
}

if (in_array($course_filter, [
    "Computer Science", "Web Development", "Data Science"
], true)) {
    $sql .= " AND course = ?";
} else {
    $course_filter = "";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($search !== "" && $course_filter !== "") {
    $term = "%" . $search . "%";
    $stmt->bind_param("ssss", $term, $term, $term, $course_filter);
} elseif ($search !== "") {
    $term = "%" . $search . "%";
    $stmt->bind_param("sss", $term, $term, $term);
} elseif ($course_filter !== "") {
    $stmt->bind_param("s", $course_filter);
}

$stmt->execute();
$result = $stmt->get_result();
$total_active = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Practical 11</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page">
    <header class="page-header">
        <div>
            <p class="eyebrow">WEB DEVELOPMENT FRAMEWORKS</p>
            <h1>Student Management System</h1>
            <p class="subtitle">Manage student records, search, and filter.</p>
        </div>
        <div class="stat">
            <span>Active results</span>
            <strong><?= $total_active ?></strong>
        </div>
    </header>

    <?php if ($message !== ""): ?>
        <div class="alert <?= e($message_type) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <h2>Add New Student</h2>

        <form method="POST" action="index.php" class="student-form">
            <div class="field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       maxlength="100" required>
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       maxlength="100" required>
            </div>

            <div class="field">
                <label for="course">Course</label>
                <select id="course" name="course" required>
                    <option value="">Select course</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Data Science">Data Science</option>
                </select>
            </div>

            <div class="field">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <button type="submit" name="add_student" class="btn primary">
                Add Student
            </button>
        </form>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Student Records</h2>
                <p class="subtitle">Only active students are shown.</p>
            </div>
            <a href="export.php" class="btn secondary">Export CSV</a>
        </div>

        <form method="GET" action="index.php" class="filters">
            <input
                type="search"
                name="search"
                placeholder="Search name, email, or course..."
                value="<?= e($search) ?>"
            >

            <select name="course_filter">
                <option value="">All Courses</option>
                <option value="Computer Science"
                    <?= $course_filter === "Computer Science" ? "selected" : "" ?>>
                    Computer Science
                </option>
                <option value="Web Development"
                    <?= $course_filter === "Web Development" ? "selected" : "" ?>>
                    Web Development
                </option>
                <option value="Data Science"
                    <?= $course_filter === "Data Science" ? "selected" : "" ?>>
                    Data Science
                </option>
            </select>

            <button type="submit" class="btn primary">Search / Filter</button>
            <a href="index.php" class="btn secondary">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row["id"] ?></td>
                            <td><?= e($row["name"]) ?></td>
                            <td><?= e($row["email"]) ?></td>
                            <td><?= e($row["course"]) ?></td>
                            <td><?= e($row["gender"]) ?></td>
                            <td><span class="status"><?= e($row["status"]) ?></span></td>
                            <td><?= e($row["created_at"]) ?></td>
                            <td class="actions">
                                <a class="action-edit"
                                   href="edit.php?id=<?= (int)$row["id"] ?>">Edit</a>

                                <form method="POST" action="delete.php"
                                      onsubmit="return confirm('Are you sure you want to mark this student as inactive?');">
                                    <input type="hidden" name="id"
                                           value="<?= (int)$row["id"] ?>">
                                    <button type="submit" class="action-delete">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty">
                            No active student records found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer>Practical 11 · Student Management System</footer>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
