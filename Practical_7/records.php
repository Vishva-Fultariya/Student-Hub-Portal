
<?php

/*
 * WDF Practical 7
 * Display Stored CSV Records
 */


/* -----------------------------------------
   1. Define CSV file location
   ----------------------------------------- */

$csv_file = __DIR__ . "/data/registrations.csv";


/* -----------------------------------------
   2. Check whether CSV exists
   ----------------------------------------- */

$records = [];
$csv_exists = file_exists($csv_file);


/* -----------------------------------------
   3. Read CSV records
   ----------------------------------------- */

if ($csv_exists) {

    $file_handle = fopen($csv_file, "r");

    if ($file_handle !== false) {

        // Read the CSV header
        $header = fgetcsv($file_handle);

        // Read each record
        while (($row = fgetcsv($file_handle)) !== false) {

            if (count($row) > 0) {
                $records[] = $row;
            }
        }

        fclose($file_handle);
    }
}


/* -----------------------------------------
   4. Number of records
   ----------------------------------------- */

$total_records = count($records);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Stored Registrations | WDF Practical 7</title>

    <style>

        /* ---------- Global ---------- */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }


        /* ---------- Container ---------- */

        .page-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }


        /* ---------- Header ---------- */

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .practical-label {
            display: inline-block;
            margin-bottom: 10px;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e8f0fe;
            color: #2563eb;
            font-size: 13px;
            font-weight: 700;
        }

        .page-header h1 {
            margin-bottom: 10px;
            font-size: 30px;
            color: #111827;
        }

        .page-description {
            color: #6b7280;
            line-height: 1.5;
        }


        /* ---------- Summary ---------- */

        .summary {
            margin-bottom: 20px;
            padding: 15px 18px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.05);
        }

        .summary strong {
            color: #2563eb;
        }


        /* ---------- Table Card ---------- */

        .table-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th,
        td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            color: #374151;
            font-size: 14px;
            white-space: nowrap;
        }

        td {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
        }

        tbody tr:hover {
            background: #f9fafb;
        }


        /* ---------- Empty State ---------- */

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #6b7280;
        }

        .empty-state h2 {
            margin-bottom: 10px;
            color: #374151;
        }


        /* ---------- Buttons ---------- */

        .actions {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .primary-button {
            background: #2563eb;
            color: #ffffff;
        }

        .primary-button:hover {
            background: #1d4ed8;
        }

        .secondary-button {
            background: #e5e7eb;
            color: #374151;
        }

        .secondary-button:hover {
            background: #d1d5db;
        }


        /* ---------- Mobile ---------- */

        @media (max-width: 600px) {

            .page-container {
                padding: 25px 12px;
            }

            .page-header h1 {
                font-size: 25px;
            }

            .table-card {
                padding: 12px;
            }
        }

    </style>

</head>

<body>

    <main class="page-container">

        <header class="page-header">

            <p class="practical-label">
                WDF Practical 7
            </p>

            <h1>
                Stored Registrations
            </h1>

            <p class="page-description">
                Registration records retrieved from the CSV file.
            </p>

        </header>


        <?php if ($csv_exists): ?>

            <div class="summary">

                Total registrations:
                <strong>
                    <?= $total_records ?>
                </strong>

            </div>


            <?php if ($total_records > 0): ?>

                <section class="table-card">

                    <table>

                        <thead>

                            <tr>

                                <th>Student Name</th>

                                <th>Email</th>

                                <th>Phone</th>

                                <th>Course</th>

                                <th>Semester</th>

                                <th>Message</th>

                                <th>Submitted At</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($records as $record): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[0] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[1] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[2] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[3] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[4] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[5] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $record[6] ?? "",
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </section>


            <?php else: ?>

                <section class="table-card">

                    <div class="empty-state">

                        <h2>
                            No Records Found
                        </h2>

                        <p>
                            The CSV file exists, but no registration
                            records have been stored yet.
                        </p>

                    </div>

                </section>

            <?php endif; ?>


        <?php else: ?>

            <section class="table-card">

                <div class="empty-state">

                    <h2>
                        No Registration Data
                    </h2>

                    <p>
                        The registrations CSV file has not been created yet.
                    </p>

                </div>

            </section>

        <?php endif; ?>


        <div class="actions">

            <a
                href="index.php"
                class="button primary-button"
            >
                New Registration
            </a>

        </div>

    </main>

</body>

</html>
