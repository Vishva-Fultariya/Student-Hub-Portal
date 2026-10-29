
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | WDF Practical 7</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="page-container">

        <section class="form-card">

            <header class="form-header">
                <p class="practical-label">WDF Practical 7</p>

                <h1>Student Registration</h1>

                <p class="form-description">
                    Submit your registration details using the form below.
                </p>
            </header>

            <form action="process.php" method="POST">

                <div class="form-group">
                    <label for="student_name">Student Name</label>

                    <input
                        type="text"
                        id="student_name"
                        name="student_name"
                        placeholder="Enter your full name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="Enter 10-digit phone number"
                        maxlength="10"
                        required
                    >
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="course">Course</label>

                        <select id="course" name="course" required>
                            <option value="">Select course</option>
                            <option value="B.Tech CSE">B.Tech CSE</option>
                            <option value="B.Tech IT">B.Tech IT</option>
                            <option value="BCA">BCA</option>
                            <option value="MCA">MCA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>

                        <select id="semester" name="semester" required>
                            <option value="">Select semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">3rd Semester</option>
                            <option value="4">4th Semester</option>
                            <option value="5">5th Semester</option>
                            <option value="6">6th Semester</option>
                            <option value="7">7th Semester</option>
                            <option value="8">8th Semester</option>
                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label for="message">Message</label>

                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        placeholder="Enter your message"
                        required
                    ></textarea>
                </div>

                <button type="submit" class="submit-button">
                    Submit Registration
                </button>

            </form>

        </section>

    </main>

</body>
</html>

