# StudentHub Portal

A student portal project developed as part of the **Web Development Frameworks (WDF)** practicals. The project progresses from website planning and responsive frontend development to PHP form processing, MySQL database operations, authentication, CRUD modules, asynchronous APIs, and role-based administration.

## Project Overview

StudentHub demonstrates the development of student-facing pages and administrative modules using core web technologies. The practicals cover individual concepts and features; they are maintained in separate practical folders where appropriate.

## Practical Coverage

| Practical | Topic | Main Technologies |
|---|---|---|
| 1 | Project planning, requirements, sitemap, wireframes and GitHub setup | Git, GitHub, Draw.io/Figma |
| 2 | Semantic HTML5 pages and accessibility-ready structure | HTML5 |
| 3 | Responsive interface design | CSS3, Flexbox, Grid, Bootstrap, Tailwind CSS |
| 4 | DOM manipulation, event handling and UI interactivity | JavaScript, DOM, localStorage |
| 5 | Registration form and frontend validation | HTML5, JavaScript, Regular Expressions |
| 6 | JSON data rendering, search, filtering, sorting and pagination | JSON, Fetch API, JavaScript |
| 7 | PHP form processing and CSV/JSON file storage | PHP, CSV, JSON |
| 8 | Database schema design and PHP–MySQL connectivity | MySQL, phpMyAdmin, PHP, PDO |
| 9 | Secure registration, duplicate checks and password hashing | PHP, MySQLi |
| 10 | Login/logout, sessions and role-based access | PHP, MySQL, Sessions |
| 11 | Student management CRUD, search and filtering | PHP, MySQL |
| 12 | Event management CRUD and poster uploads | PHP, MySQL, File Uploads |
| 13 | PHP JSON APIs and asynchronous CRUD | PHP, JavaScript Fetch API, JSON |
| 14 | Admin dashboard, role-based access and audit logging | PHP, MySQL, Bootstrap |

## Project Modules

Depending on the practical folder, the project includes work related to:

- **Student portal pages:** Home, About, Contact, Login, Sign Up, Dashboard, Profile, Study Material, Timetable, Attendance, Results and Notices.
- **Interactive interface:** Responsive navigation, dynamic UI elements, theme preferences and client-side validation.
- **Data handling:** JSON rendering, search, filtering, sorting and pagination.
- **Backend processing:** PHP form handling, server-side validation and file-based storage.
- **Database operations:** MySQL connectivity, registration and student/event CRUD.
- **Authentication and administration:** Login/logout, sessions, role-based access and admin dashboard functionality.
- **Asynchronous operations:** PHP JSON endpoints and Fetch API-based interactions.

*Note: Individual features should be considered implemented only where they are present and working in the relevant practical folder.*

## Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **CSS and UI:** Flexbox, CSS Grid, Media Queries, Bootstrap, Tailwind CSS
- **Backend:** PHP
- **Database:** MySQL, phpMyAdmin
- **Data formats:** JSON, CSV, SQL
- **Web APIs:** Fetch API, PHP JSON APIs
- **Development environment:** Visual Studio Code, XAMPP
- **Version control:** Git and GitHub

## Project Structure

The repository contains the main StudentHub project and separate folders for the later practicals.

```text
Student-Hub-Portal/
├── css/
├── images/
├── js/
├── Practical_5/
├── Practical_6/
├── Practical_7/
├── Practical_8/
├── Practical_9/
├── Practical_10/
├── Practical_11/
├── Practical_12/
├── Practical_13/
├── Practical_14/
├── index.html
├── about.html
├── contact.html
├── login.html
├── signup.html
├── dashboard.html
├── profile.html
├── studymaterial.html
├── timetable.html
├── attendance.html
├── result.html
├── notice.html
├── logout.html
└── README.md
```

*The tree is illustrative. Actual files and subfolders may differ between practicals.*

## Requirements

To run the applicable practicals locally, install:

- [XAMPP](https://www.apachefriends.org/) with Apache, PHP and MySQL
- [Visual Studio Code](https://code.visualstudio.com/)
- A modern web browser
- Git, if you want to clone or update the repository

## Installation and Setup

### 1. Clone the repository

Open a terminal and run:

```bash
git clone https://github.com/Vishva-Fultariya/Student-Hub-Portal.git
```

### 2. Place the project in XAMPP

Copy or move the cloned project into the XAMPP `htdocs` directory. For example:

```text
C:\xampp\htdocs\Student-Hub-Portal\
```

If your local folder has a different name, use that name in the browser URL.

### 3. Start XAMPP

Open the XAMPP Control Panel and start **Apache**.

Start **MySQL** as well for practicals that use a database.

### 4. Open the project

Visit the applicable project URL in your browser:

```text
http://localhost/Student-Hub-Portal/
```

For a standalone practical, open its folder path, for example:

```text
http://localhost/Student-Hub-Portal/Practical_5/
```

Replace the example path with the actual practical folder and entry-point file.

### 5. Configure database-dependent practicals

For practicals that require MySQL:

1. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
2. Create the database specified by the relevant practical.
3. Import its supplied `.sql` file, if available.
4. Configure the database connection file with your local database settings.
5. Open the appropriate PHP entry point through `localhost`.

Database names, table structures, and entry-point filenames may differ between practicals. Follow the configuration supplied in each folder.

## Testing and Verification

Test each practical independently using its relevant requirements.

- **Frontend:** Check page navigation, responsive layouts, form labels, keyboard accessibility and JavaScript interactions.
- **Validation:** Test valid, invalid, empty and duplicate form submissions where applicable.
- **File storage:** Verify CSV/JSON writes and displayed feedback.
- **Database:** Test connection handling and the required create, read, update and delete operations.
- **Authentication:** Test successful and failed login, logout and restricted-page access where implemented.
- **File uploads:** Test permitted file types, invalid types and file-size limits.
- **Fetch/API:** Verify JSON responses, asynchronous operations, loading states and error handling.
- **Administration:** Verify role restrictions, dashboard data and audit logging where implemented.

Record the actual results and screenshots in your practical documentation. Do not mark a test as passed until it has been performed.

## Security and Accessibility Considerations

The relevant practicals address security and accessibility concepts such as:

- Semantic HTML and accessible form labels
- Client-side and server-side input validation
- Password hashing and password verification
- Prepared statements for database operations
- Session management and role-based access control
- File-upload validation
- Appropriate error handling and access restrictions

These are implementation goals, not a guarantee that every module has been independently security-audited. Verify each feature in its corresponding practical before reporting it as complete.

## Learning Outcomes

Through Practicals 1–14, the project provides practice in:

- Planning and structuring a web application
- Building responsive and accessible web pages
- Adding client-side interactivity
- Processing and validating form submissions
- Working with JSON, CSV and MySQL
- Implementing PHP-based data management
- Using Fetch API for asynchronous communication
- Understanding authentication, authorization and administration
- Maintaining source code using Git and GitHub

## Repository

**GitHub:** [Vishva-Fultariya/Student-Hub-Portal](https://github.com/Vishva-Fultariya/Student-Hub-Portal)

## Academic Context

**Subject:** Web Development Frameworks (WDF)  
**Project:** StudentHub Portal  
**Practical Coverage:** 1–14

This repository documents the practical exercises and implementation work completed during the course. Refer to the relevant practical folder for its source code, configuration, and supporting files.
