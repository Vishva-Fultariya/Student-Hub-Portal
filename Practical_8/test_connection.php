<?php

require_once "db.php";

$students = [];
$error = "";

try {

    $sql = "
        SELECT
            student_id,
            name,
            email,
            course
        FROM students
        ORDER BY student_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $students = $stmt->fetchAll();

} catch (PDOException $e) {

    $error = "Unable to fetch student data.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Database Test</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
        }

        .success {
            padding: 14px;
            margin: 20px 0;
            border-radius: 6px;
            background: #e8f7ee;
            color: #176b3a;
            border: 1px solid #b7e4c7;
            font-weight: bold;
        }

        .error {
            padding: 14px;
            margin: 20px 0;
            border-radius: 6px;
            background: #fdecec;
            color: #9b1c1c;
            border: 1px solid #f5b5b5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f0f2f5;
        }

        .info {
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>StudentHub Database Test</h1>

    <div class="success">
        Database Connected Successfully
    </div>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php else: ?>

        <h2>Student Table Output</h2>

        <?php if (count($students) > 0): ?>

            <table>

                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($students as $student): ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars($student["student_id"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student["name"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student["email"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($student["course"]) ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

            <p class="info">
                Student records were successfully fetched using
                PHP PDO and a Prepared Statement.
            </p>

        <?php else: ?>

            <p>No student records found.</p>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>