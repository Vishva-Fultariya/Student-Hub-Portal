
/* =========================================================
   WDF Practical 5
   Student Registration Form
   Frontend Validation + Real-Time Validation
   Canvas CAPTCHA
   ========================================================= */


/* =========================================================
   1. DOM REFERENCES
   ========================================================= */

const registrationForm = document.getElementById("registrationForm");

const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const mobileInput = document.getElementById("mobile");

const passwordInput = document.getElementById("password");
const confirmPasswordInput =
    document.getElementById("confirmPassword");

const courseInput = document.getElementById("course");

const yearInputs =
    document.querySelectorAll('input[name="year"]');

const genderInputs =
    document.querySelectorAll('input[name="gender"]');

const termsInput = document.getElementById("terms");

const captchaCanvas =
    document.getElementById("captchaCanvas");

const captchaInput =
    document.getElementById("captchaInput");

const refreshCaptchaButton =
    document.getElementById("refreshCaptcha");

const successMessage =
    document.getElementById("successMessage");

const strengthText =
    document.getElementById("strengthText");


/* =========================================================
   2. ERROR MESSAGE ELEMENTS
   ========================================================= */

const nameError = document.getElementById("nameError");
const emailError = document.getElementById("emailError");
const mobileError = document.getElementById("mobileError");

const passwordError =
    document.getElementById("passwordError");

const confirmPasswordError =
    document.getElementById("confirmPasswordError");

const courseError =
    document.getElementById("courseError");

const yearError =
    document.getElementById("yearError");

const genderError =
    document.getElementById("genderError");

const termsError =
    document.getElementById("termsError");

const captchaError =
    document.getElementById("captchaError");


/* =========================================================
   3. REGULAR EXPRESSIONS
   ========================================================= */

/*
 * Name:
 * Allows alphabets and spaces.
 */
const nameRegex =
    /^[A-Za-z]+(?:\s[A-Za-z]+)*$/;


/*
 * Email:
 * Basic email format validation.
 */
const emailRegex =
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


/*
 * Mobile:
 * Indian 10-digit mobile number
 * beginning with 6, 7, 8 or 9.
 */
const mobileRegex =
    /^[6-9]\d{9}$/;


/*
 * Password:
 *
 * At least:
 * - 8 characters
 * - one uppercase letter
 * - one lowercase letter
 * - one number
 * - one special character
 */
const passwordRegex =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;


/* =========================================================
   4. GENERAL ERROR HANDLING FUNCTIONS
   ========================================================= */

/*
 * Displays an error message.
 */
function showError(input, errorElement, message) {

    errorElement.textContent = message;

    input.classList.remove("input-success");
    input.classList.add("input-error");

    input.setAttribute("aria-invalid", "true");
}


/*
 * Clears an error message.
 */
function clearError(input, errorElement) {

    errorElement.textContent = "";

    input.classList.remove("input-error");
    input.classList.add("input-success");

    input.setAttribute("aria-invalid", "false");
}


/*
 * Clears validation state without marking
 * the field as valid.
 */
function resetValidationState(input, errorElement) {

    errorElement.textContent = "";

    input.classList.remove("input-error");
    input.classList.remove("input-success");

    input.removeAttribute("aria-invalid");
}


/* =========================================================
   5. NAME VALIDATION
   ========================================================= */

function validateName() {

    const name = nameInput.value.trim();

    if (name === "") {

        showError(
            nameInput,
            nameError,
            "Full name is required."
        );

        return false;
    }

    if (name.length < 2) {

        showError(
            nameInput,
            nameError,
            "Name must contain at least 2 characters."
        );

        return false;
    }

    if (!nameRegex.test(name)) {

        showError(
            nameInput,
            nameError,
            "Name can contain only letters and spaces."
        );

        return false;
    }

    clearError(nameInput, nameError);

    return true;
}


/* =========================================================
   6. EMAIL VALIDATION
   ========================================================= */

function validateEmail() {

    const email = emailInput.value.trim();

    if (email === "") {

        showError(
            emailInput,
            emailError,
            "Email address is required."
        );

        return false;
    }

    if (!emailRegex.test(email)) {

        showError(
            emailInput,
            emailError,
            "Please enter a valid email address."
        );

        return false;
    }

    clearError(emailInput, emailError);

    return true;
}


/* =========================================================
   7. MOBILE VALIDATION
   ========================================================= */

function validateMobile() {

    const mobile = mobileInput.value.trim();

    if (mobile === "") {

        showError(
            mobileInput,
            mobileError,
            "Mobile number is required."
        );

        return false;
    }

    if (!mobileRegex.test(mobile)) {

        showError(
            mobileInput,
            mobileError,
            "Enter a valid 10-digit Indian mobile number."
        );

        return false;
    }

    clearError(mobileInput, mobileError);

    return true;
}


/* =========================================================
   8. PASSWORD STRENGTH
   ========================================================= */

function calculatePasswordStrength(password) {

    let score = 0;

    if (password.length >= 8) {
        score++;
    }

    if (/[a-z]/.test(password)) {
        score++;
    }

    if (/[A-Z]/.test(password)) {
        score++;
    }

    if (/\d/.test(password)) {
        score++;
    }

    if (/[@$!%*?&]/.test(password)) {
        score++;
    }

    if (score <= 2) {
        return "Weak";
    }

    if (score <= 4) {
        return "Medium";
    }

    return "Strong";
}


/*
 * Updates the password strength text.
 */
function updatePasswordStrength() {

    const password = passwordInput.value;

    if (password === "") {

        strengthText.textContent =
            "Password strength";

        strengthText.removeAttribute("data-strength");

        return;
    }

    const strength =
        calculatePasswordStrength(password);

    strengthText.textContent =
        "Password strength: " + strength;

    strengthText.setAttribute(
        "data-strength",
        strength.toLowerCase()
    );
}


/* =========================================================
   9. PASSWORD VALIDATION
   ========================================================= */

function validatePassword() {

    const password = passwordInput.value;

    updatePasswordStrength();

    if (password === "") {

        showError(
            passwordInput,
            passwordError,
            "Password is required."
        );

        return false;
    }

    if (password.length < 8) {

        showError(
            passwordInput,
            passwordError,
            "Password must contain at least 8 characters."
        );

        return false;
    }

    if (!passwordRegex.test(password)) {

        showError(
            passwordInput,
            passwordError,
            "Password must contain uppercase, lowercase, number and special character."
        );

        return false;
    }

    clearError(passwordInput, passwordError);

    return true;
}


/* =========================================================
   10. CONFIRM PASSWORD VALIDATION
   ========================================================= */

function validateConfirmPassword() {

    const password = passwordInput.value;
    const confirmPassword =
        confirmPasswordInput.value;

    if (confirmPassword === "") {

        showError(
            confirmPasswordInput,
            confirmPasswordError,
            "Please confirm your password."
        );

        return false;
    }

    if (password !== confirmPassword) {

        showError(
            confirmPasswordInput,
            confirmPasswordError,
            "Passwords do not match."
        );

        return false;
    }

    clearError(
        confirmPasswordInput,
        confirmPasswordError
    );

    return true;
}


/* =========================================================
   11. COURSE VALIDATION
   ========================================================= */

function validateCourse() {

    if (courseInput.value === "") {

        showError(
            courseInput,
            courseError,
            "Please select your course."
        );

        return false;
    }

    clearError(courseInput, courseError);

    return true;
}


/* =========================================================
   12. YEAR VALIDATION
   ========================================================= */

function validateYear() {

    let selectedYear = false;

    yearInputs.forEach(function (radio) {

        if (radio.checked) {
            selectedYear = true;
        }

    });


    if (!selectedYear) {

        yearError.textContent =
            "Please select your year.";

        return false;
    }

    yearError.textContent = "";

    return true;
}


/* =========================================================
   13. GENDER VALIDATION
   ========================================================= */

function validateGender() {

    let selectedGender = false;

    genderInputs.forEach(function (radio) {

        if (radio.checked) {
            selectedGender = true;
        }

    });


    if (!selectedGender) {

        genderError.textContent =
            "Please select your gender.";

        return false;
    }

    genderError.textContent = "";

    return true;
}


/* =========================================================
   14. TERMS VALIDATION
   ========================================================= */

function validateTerms() {

    if (!termsInput.checked) {

        termsError.textContent =
            "You must accept the terms and conditions.";

        return false;
    }

    termsError.textContent = "";

    return true;
}


/* =========================================================
   15. REAL-TIME VALIDATION
   ========================================================= */

/*
 * Intermediate Extension:
 * Validation occurs while the user interacts
 * with the form.
 */


/* Name */
nameInput.addEventListener("input", validateName);


/* Email */
emailInput.addEventListener("input", validateEmail);


/* Mobile */
mobileInput.addEventListener("input", validateMobile);


/* Password */
passwordInput.addEventListener("input", function () {

    validatePassword();

    /*
     * Re-check confirm password whenever the
     * original password changes.
     */
    if (confirmPasswordInput.value !== "") {
        validateConfirmPassword();
    }

});


/* Confirm Password */
confirmPasswordInput.addEventListener(
    "input",
    validateConfirmPassword
);


/* Course */
courseInput.addEventListener(
    "change",
    validateCourse
);


/* Year */
yearInputs.forEach(function (radio) {

    radio.addEventListener(
        "change",
        validateYear
    );

});


/* Gender */
genderInputs.forEach(function (radio) {

    radio.addEventListener(
        "change",
        validateGender
    );

});


/* Terms */
termsInput.addEventListener(
    "change",
    validateTerms
);


/* =========================================================
   16. FORM SUBMISSION
   ========================================================= */

registrationForm.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();

        const isNameValid =
            validateName();

        const isEmailValid =
            validateEmail();

        const isMobileValid =
            validateMobile();

        const isPasswordValid =
            validatePassword();

        const isConfirmPasswordValid =
            validateConfirmPassword();

        const isCourseValid =
            validateCourse();

        const isYearValid =
            validateYear();

        const isGenderValid =
            validateGender();

        const isTermsValid =
            validateTerms();

        const isCaptchaValid =
            validateCaptcha();


        const isFormValid =
            isNameValid &&
            isEmailValid &&
            isMobileValid &&
            isPasswordValid &&
            isConfirmPasswordValid &&
            isCourseValid &&
            isYearValid &&
            isGenderValid &&
            isTermsValid &&
            isCaptchaValid;


        if (isFormValid) {

            successMessage.textContent =
                "✓ Registration successful! All validation checks passed.";

            successMessage.style.display =
                "block";

            successMessage.scrollIntoView({
                behavior: "smooth",
                block: "nearest"
            });

        } else {

            successMessage.textContent = "";

            successMessage.style.display =
                "none";

            /*
             * Move focus to the first invalid
             * form control.
             */
            focusFirstInvalidField();
        }

    }
);


/* =========================================================
   17. FOCUS FIRST INVALID FIELD
   ========================================================= */

function focusFirstInvalidField() {

    const invalidField =
        registrationForm.querySelector(
            ".input-error"
        );

    if (invalidField) {
        invalidField.focus();
    }

}


/* =========================================================
   18. RESET FORM
   ========================================================= */

registrationForm.addEventListener(
    "reset",
    function () {

        setTimeout(function () {

            /*
             * Clear validation states.
             */
            resetValidationState(
                nameInput,
                nameError
            );

            resetValidationState(
                emailInput,
                emailError
            );

            resetValidationState(
                mobileInput,
                mobileError
            );

            resetValidationState(
                passwordInput,
                passwordError
            );

            resetValidationState(
                confirmPasswordInput,
                confirmPasswordError
            );

            resetValidationState(
                courseInput,
                courseError
            );


            /*
             * Clear radio/checkbox messages.
             */
            yearError.textContent = "";
            genderError.textContent = "";
            termsError.textContent = "";


            /*
             * Reset password strength.
             */
            strengthText.textContent =
                "Password strength";

            strengthText.removeAttribute(
                "data-strength"
            );


            /*
             * Hide success message.
             */
            successMessage.textContent = "";

            successMessage.style.display =
                "none";


            /*
             * Generate a fresh CAPTCHA.
             */
            generateCaptcha();

            captchaInput.value = "";

            resetValidationState(
                captchaInput,
                captchaError
            );

        }, 0);

    }
);


/* =========================================================
   19. CANVAS CAPTCHA
   ========================================================= */

/*
 * Advanced Extension:
 * Custom CAPTCHA using HTML5 Canvas.
 */


/*
 * Canvas drawing context.
 */
const captchaContext =
    captchaCanvas.getContext("2d");


/*
 * Stores the current CAPTCHA value.
 */
let currentCaptcha = "";


/*
 * Characters used to generate CAPTCHA.
 *
 * Ambiguous characters such as:
 * 0, O, 1 and I are excluded.
 */
const captchaCharacters =
    "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";


/*
 * Generates a random CAPTCHA string.
 */
function createCaptchaText(length) {

    let captcha = "";

    for (let i = 0; i < length; i++) {

        const randomIndex =
            Math.floor(
                Math.random() *
                captchaCharacters.length
            );

        captcha +=
            captchaCharacters[randomIndex];
    }

    return captcha;
}


/*
 * Generates and draws CAPTCHA.
 */
function generateCaptcha() {

    currentCaptcha =
        createCaptchaText(6);

    /*
     * Clear canvas.
     */
    captchaContext.clearRect(
        0,
        0,
        captchaCanvas.width,
        captchaCanvas.height
    );


    /*
     * Background.
     */
    captchaContext.fillStyle =
        "#f8fafc";

    captchaContext.fillRect(
        0,
        0,
        captchaCanvas.width,
        captchaCanvas.height
    );


    /*
     * Draw random noise lines.
     */
    for (let i = 0; i < 7; i++) {

        captchaContext.beginPath();

        captchaContext.moveTo(
            Math.random() *
            captchaCanvas.width,

            Math.random() *
            captchaCanvas.height
        );

        captchaContext.lineTo(
            Math.random() *
            captchaCanvas.width,

            Math.random() *
            captchaCanvas.height
        );

        captchaContext.strokeStyle =
            "rgba(37, 99, 235, 0.35)";

        captchaContext.lineWidth = 1;

        captchaContext.stroke();
    }


    /*
     * Draw random dots.
     */
    for (let i = 0; i < 35; i++) {

        captchaContext.beginPath();

        captchaContext.arc(
            Math.random() *
            captchaCanvas.width,

            Math.random() *
            captchaCanvas.height,

            1.5,

            0,

            Math.PI * 2
        );

        captchaContext.fillStyle =
            "rgba(71, 85, 105, 0.45)";

        captchaContext.fill();
    }


    /*
     * Draw CAPTCHA characters.
     */
    captchaContext.font =
        "bold 32px Arial";

    captchaContext.textBaseline =
        "middle";

    const characterSpacing = 38;

    for (let i = 0; i < currentCaptcha.length; i++) {

        const character =
            currentCaptcha[i];

        const x =
            18 + i * characterSpacing;

        const y =
            captchaCanvas.height / 2;

        /*
         * Small random rotation.
         */
        const angle =
            (Math.random() - 0.5) * 0.4;

        captchaContext.save();

        captchaContext.translate(x, y);

        captchaContext.rotate(angle);

        captchaContext.fillStyle =
            "#1e3a8a";

        captchaContext.fillText(
            character,
            0,
            0
        );

        captchaContext.restore();
    }

}


/*
 * CAPTCHA validation.
 */
function validateCaptcha() {

    const enteredCaptcha =
        captchaInput.value
            .trim()
            .toUpperCase();


    if (enteredCaptcha === "") {

        showError(
            captchaInput,
            captchaError,
            "Please enter the CAPTCHA."
        );

        return false;
    }


    if (enteredCaptcha !== currentCaptcha) {

        showError(
            captchaInput,
            captchaError,
            "CAPTCHA does not match. Please try again."
        );

        return false;
    }


    clearError(
        captchaInput,
        captchaError
    );

    return true;
}


/*
 * Real-time CAPTCHA validation.
 */
captchaInput.addEventListener(
    "input",
    function () {

        /*
         * Don't show an error until the user
         * has entered something.
         */
        if (captchaInput.value.trim() !== "") {
            validateCaptcha();
        }

    }
);


/*
 * Refresh CAPTCHA button.
 */
refreshCaptchaButton.addEventListener(
    "click",
    function () {

        generateCaptcha();

        captchaInput.value = "";

        resetValidationState(
            captchaInput,
            captchaError
        );

        captchaInput.focus();

    }
);


/* =========================================================
   20. INITIALIZE CAPTCHA
   ========================================================= */

generateCaptcha();
