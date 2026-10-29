
"use strict";

// ============================================
// WDF Practical 13 - Students API Frontend
// Fetch API + Asynchronous CRUD
// ============================================

const API_URL = "api.php";

// DOM elements
const studentForm = document.getElementById("studentForm");
const studentId = document.getElementById("studentId");
const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const courseInput = document.getElementById("course");
const semesterInput = document.getElementById("semester");

const studentsTable = document.getElementById("studentsTable");
const searchInput = document.getElementById("searchInput");
const loading = document.getElementById("loading");
const message = document.getElementById("message");

const formTitle = document.getElementById("formTitle");
const submitBtn = document.getElementById("submitBtn");
const cancelBtn = document.getElementById("cancelBtn");
const refreshBtn = document.getElementById("refreshBtn");

let students = [];
let messageTimer;
let searchTimer;

// --------------------------------------------
// Centralized API request and error handling
// --------------------------------------------
async function apiRequest(action, options = {}, query = "") {
    const url = `${API_URL}?action=${encodeURIComponent(action)}${query}`;

    const response = await fetch(url, {
        ...options,
        headers: {
            ...(options.body
                ? { "Content-Type": "application/json" }
                : {}),
            ...options.headers
        }
    });

    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error(
            `The server returned an invalid JSON response (HTTP ${response.status}).`
        );
    }

    if (!response.ok || result.status !== "success") {
        throw new Error(
            result.message || `Request failed with HTTP ${response.status}.`
        );
    }

    return result;
}

// --------------------------------------------
// Show success and error messages
// --------------------------------------------
function showMessage(text, type = "success") {
    clearTimeout(messageTimer);

    message.textContent = text;
    message.className = type;
    message.hidden = false;

    messageTimer = setTimeout(() => {
        message.hidden = true;
        message.textContent = "";
        message.className = "";
    }, 4000);
}

// --------------------------------------------
// Loading state
// --------------------------------------------
function setLoading(isLoading) {
    loading.hidden = !isLoading;
    refreshBtn.disabled = isLoading;
}

// --------------------------------------------
// Render student table safely
// --------------------------------------------
function renderStudents() {
    const filteredStudents = students;

    studentsTable.replaceChildren();

    if (filteredStudents.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 6;
        cell.className = "empty";
        cell.textContent = searchInput.value.trim() !== ""
            ? "No students match your search."
            : "No students found. Add your first student.";

        row.appendChild(cell);
        studentsTable.appendChild(row);
        return;
    }

    filteredStudents.forEach(student => {
        const row = document.createElement("tr");

        [
            student.id,
            student.name,
            student.email,
            student.course,
            student.semester
        ].forEach(value => {
            const cell = document.createElement("td");
            cell.textContent = String(value);
            row.appendChild(cell);
        });

        const actionsCell = document.createElement("td");
        const actions = document.createElement("div");
        actions.className = "actions";

        const editButton = document.createElement("button");
        editButton.type = "button";
        editButton.className = "edit";
        editButton.textContent = "Edit";
        editButton.setAttribute(
            "aria-label",
            `Edit student ${student.name}`
        );
        editButton.addEventListener("click", () => editStudent(student.id));

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.className = "delete";
        deleteButton.textContent = "Delete";
        deleteButton.setAttribute(
            "aria-label",
            `Delete student ${student.name}`
        );
        deleteButton.addEventListener(
            "click",
            () => deleteStudent(student.id)
        );

        actions.append(editButton, deleteButton);
        actionsCell.appendChild(actions);
        row.appendChild(actionsCell);
        studentsTable.appendChild(row);
    });
}

// --------------------------------------------
// READ: Fetch all students
// --------------------------------------------
async function loadStudents(showSuccess = false) {
    setLoading(true);

    try {
        const result = await apiRequest("list");

        students = result.data;
        renderStudents();

        if (showSuccess) {
            showMessage("Student list refreshed successfully.");
        }
    } catch (error) {
        showMessage(error.message, "error");

        studentsTable.replaceChildren();

        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 6;
        cell.className = "empty";
        cell.textContent = "Could not load students. Try Refresh.";

        row.appendChild(cell);
        studentsTable.appendChild(row);
    } finally {
        setLoading(false);
    }
}

// --------------------------------------------
// SEARCH: Search students through the PHP API
//--------------------------------------------
async function searchStudents() {
    const query = searchInput.value.trim();

    setLoading(true);

    try {
        const result = await apiRequest(
            "search",
            {},
            `&q=${encodeURIComponent(query)}`
        );

        students = result.data;
        renderStudents();

    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        setLoading(false);
    }
}

// --------------------------------------------
// CREATE / UPDATE: Save student form
// --------------------------------------------
studentForm.addEventListener("submit", async event => {
    event.preventDefault();

    const id = studentId.value;

    const payload = {
        name: nameInput.value.trim(),
        email: emailInput.value.trim(),
        course: courseInput.value.trim(),
        semester: Number(semesterInput.value)
    };

    if (
        !payload.name ||
        !payload.email ||
        !payload.course ||
        !Number.isInteger(payload.semester) ||
        payload.semester < 1 ||
        payload.semester > 8
    ) {
        showMessage("Please enter valid student details.", "error");
        return;
    }

    const action = id ? "update" : "create";

    if (id) {
        payload.id = Number(id);
    }

    submitBtn.disabled = true;
    submitBtn.textContent = id ? "Updating..." : "Adding...";

    try {
        const result = await apiRequest(action, {
            method: "POST",
            body: JSON.stringify(payload)
        });

        resetForm();
        await loadStudents();

        showMessage(result.message);
    } catch (error) {
        showMessage(error.message, "error");
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = studentId.value
            ? "Update Student"
            : "Add Student";
    }
});

// --------------------------------------------
// Prepare a student for editing
// --------------------------------------------
function editStudent(id) {
    const student = students.find(item => Number(item.id) === Number(id));

    if (!student) {
        showMessage("Student not found in the current list.", "error");
        return;
    }

    studentId.value = student.id;
    nameInput.value = student.name;
    emailInput.value = student.email;
    courseInput.value = student.course;
    semesterInput.value = String(student.semester);

    formTitle.textContent = "Edit Student";
    submitBtn.textContent = "Update Student";
    cancelBtn.hidden = false;

    studentForm.scrollIntoView({
        behavior: "smooth",
        block: "start"
    });

    nameInput.focus();
}

// --------------------------------------------
// DELETE: Remove a student
// --------------------------------------------
async function deleteStudent(id) {
    const student = students.find(item => Number(item.id) === Number(id));

    if (!student) {
        showMessage("Student not found.", "error");
        return;
    }

    if (!window.confirm(`Delete student "${student.name}"?`)) {
        return;
    }

    try {
        const result = await apiRequest("delete", {
            method: "POST",
            body: JSON.stringify({ id: Number(id) })
        });

        if (String(studentId.value) === String(id)) {
            resetForm();
        }

        await loadStudents();
        showMessage(result.message);
    } catch (error) {
        showMessage(error.message, "error");
    }
}

// --------------------------------------------
// Reset form to Add Student mode
// --------------------------------------------
function resetForm() {
    studentForm.reset();
    studentId.value = "";

    formTitle.textContent = "Add Student";
    submitBtn.textContent = "Add Student";
    cancelBtn.hidden = true;
}

cancelBtn.addEventListener("click", resetForm);

/* --------------------------------------------
   DEBOUNCED AJAX SEARCH
-------------------------------------------- */
searchInput.addEventListener("input", () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        searchStudents();
    }, 300);
});

// Refresh list from the PHP API
refreshBtn.addEventListener("click", () => loadStudents(true));

// Initial load
document.addEventListener("DOMContentLoaded", () => {
    loadStudents();
});
