// Practical 4 JavaScript
console.log("StudentHub Practical 4 JavaScript loaded successfully.");
/* =========================================================
   StudentHub Portal
   Practical 4: DOM Manipulation, Event Handling and UI
   ========================================================= */

"use strict";

document.addEventListener("DOMContentLoaded", function () {

    console.log("StudentHub Practical 4 JavaScript loaded successfully.");

    // =====================================================
    // 1. NOTIFICATION BANNER
    // =====================================================

    const notificationBanner = document.getElementById("notificationBanner");
    const dismissNotification = document.getElementById("dismissNotification");
    const notificationReopenWrapper = document.getElementById("notificationReopenWrapper");
    const showNotification = document.getElementById("showNotification");

    if (notificationBanner && dismissNotification) {

        dismissNotification.addEventListener("click", function () {
            notificationBanner.hidden = true;

            if (notificationReopenWrapper) {
                notificationReopenWrapper.hidden = false;
            }

            console.log("Announcement dismissed.");
        });

    }

    if (showNotification && notificationBanner) {

        showNotification.addEventListener("click", function () {
            notificationBanner.hidden = false;

            if (notificationReopenWrapper) {
                notificationReopenWrapper.hidden = true;
            }

            console.log("Announcement displayed.");
        });

    }


    // =====================================================
    // 2. COLLAPSIBLE FAQ
    // =====================================================

    const faqQuestions = document.querySelectorAll(".faq-question");

    faqQuestions.forEach(function (question) {

        question.addEventListener("click", function () {

            const answerId = question.getAttribute("aria-controls");
            const answer = document.getElementById(answerId);

            if (!answer) {
                return;
            }

            const isExpanded =
                question.getAttribute("aria-expanded") === "true";

            // Toggle the selected answer.
            question.setAttribute("aria-expanded", String(!isExpanded));
            answer.hidden = isExpanded;

            // Change the plus/minus indicator.
            const icon = question.querySelector(".faq-icon");

            if (icon) {
                icon.textContent = isExpanded ? "+" : "−";
            }

        });

    });


    // =====================================================
    // 3. CAMPUS EVENTS SLIDER
    // =====================================================

    const events = [
        {
            category: "Upcoming Event",
            title: "Tech Fest 2026",
            description:
                "Explore technology, creativity, and student innovation."
        },
        {
            category: "Student Activity",
            title: "Cultural Festival",
            description:
                "Celebrate student talent, music, dance, and culture."
        },
        {
            category: "Learning Opportunity",
            title: "Coding Workshop",
            description:
                "Improve your programming skills through practical learning."
        }
    ];

    const eventCategory = document.getElementById("eventCategory");
    const eventTitle = document.getElementById("eventTitle");
    const eventDescription = document.getElementById("eventDescription");
    const slideIndicator = document.getElementById("slideIndicator");

    const prevEvent = document.getElementById("prevEvent");
    const nextEvent = document.getElementById("nextEvent");

    let currentEvent = 0;

    function showEvent(index) {

        if (
            !eventCategory ||
            !eventTitle ||
            !eventDescription ||
            !slideIndicator
        ) {
            return;
        }

        // Wrap around when reaching either end.
        currentEvent = (index + events.length) % events.length;

        const event = events[currentEvent];

        eventCategory.textContent = event.category;
        eventTitle.textContent = event.title;
        eventDescription.textContent = event.description;

        slideIndicator.textContent =
            `${currentEvent + 1} / ${events.length}`;

    }

    if (prevEvent) {

        prevEvent.addEventListener("click", function () {
            showEvent(currentEvent - 1);
        });

    }

    if (nextEvent) {

        nextEvent.addEventListener("click", function () {
            showEvent(currentEvent + 1);
        });

    }

    // Display the initial event.
    showEvent(0);


    // =====================================================
    // 4. MODAL POPUP
    // =====================================================

    const studentHubModal = document.getElementById("studentHubModal");
    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const modalDone = document.getElementById("modalDone");

    function closeStudentHubModal() {

        if (studentHubModal && studentHubModal.open) {
            studentHubModal.close();
        }

    }

    if (openModal && studentHubModal) {

        openModal.addEventListener("click", function () {

            if (!studentHubModal.open) {
                studentHubModal.showModal();
            }

        });

    }

    if (closeModal) {
        closeModal.addEventListener("click", closeStudentHubModal);
    }

    if (modalDone) {
        modalDone.addEventListener("click", closeStudentHubModal);
    }

    // Close the dialog when the user clicks its backdrop.
    if (studentHubModal) {

        studentHubModal.addEventListener("click", function (event) {

            if (event.target === studentHubModal) {
                closeStudentHubModal();
            }

        });

    }


    // =====================================================
    // 5. HAMBURGER MENU
    // =====================================================

    const menuToggle = document.getElementById("menuToggle");
    const primaryNavLinks = document.getElementById("primaryNavLinks");

    function closeNavigationMenu() {

        if (!menuToggle || !primaryNavLinks) {
            return;
        }

        primaryNavLinks.classList.remove("is-open");
        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Open navigation menu");

    }

    if (menuToggle && primaryNavLinks) {

        menuToggle.addEventListener("click", function () {

            const isOpen =
                menuToggle.getAttribute("aria-expanded") === "true";

            primaryNavLinks.classList.toggle("is-open", !isOpen);

            menuToggle.setAttribute("aria-expanded", String(!isOpen));

            menuToggle.setAttribute(
                "aria-label",
                isOpen ? "Open navigation menu" : "Close navigation menu"
            );

        });

        // Close the mobile menu after a navigation link is selected.
        primaryNavLinks.querySelectorAll("a").forEach(function (link) {

            link.addEventListener("click", closeNavigationMenu);

        });

        // Close the menu when Escape is pressed.
        document.addEventListener("keydown", function (event) {

            if (event.key === "Escape") {
                closeNavigationMenu();
            }

        });

    }


    // =====================================================
    // 6. LIGHT/DARK THEME WITH LOCALSTORAGE
    // =====================================================

    const themeToggle = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");
    const themeLabel = document.getElementById("themeLabel");

    const THEME_STORAGE_KEY = "studentHubTheme";

    function applyTheme(theme) {

        const isDark = theme === "dark";

        document.body.classList.toggle("dark-theme", isDark);

        if (themeToggle) {
            themeToggle.setAttribute("aria-pressed", String(isDark));

            themeToggle.setAttribute(
                "aria-label",
                isDark ? "Switch to light theme" : "Switch to dark theme"
            );
        }

        if (themeIcon) {
            themeIcon.textContent = isDark ? "☀️" : "🌙";
        }

        if (themeLabel) {
            themeLabel.textContent = isDark ? "Light Mode" : "Dark Mode";
        }

    }

    // Restore the saved theme, if available.
    let savedTheme = "light";

    try {

        const storedTheme = localStorage.getItem(THEME_STORAGE_KEY);

        if (storedTheme === "dark" || storedTheme === "light") {
            savedTheme = storedTheme;
        }

    } catch (error) {
        console.warn("Theme preference could not be loaded.", error);
    }

    applyTheme(savedTheme);

    if (themeToggle) {

        themeToggle.addEventListener("click", function () {

            const isDark = document.body.classList.contains("dark-theme");
            const newTheme = isDark ? "light" : "dark";

            applyTheme(newTheme);

            // Remember the preference for future visits.
            try {

                localStorage.setItem(THEME_STORAGE_KEY, newTheme);

            } catch (error) {
                console.warn("Theme preference could not be saved.", error);
            }

        });

    }


    // =====================================================
    // PRACTICAL 4 INITIALIZATION COMPLETE
    // =====================================================

    console.log("Practical 4 components initialized.");

});
