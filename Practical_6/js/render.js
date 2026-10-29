/* =========================================
   WDF PRACTICAL 6
   DATA RENDERING MODULE
   ========================================= */

/**
 * Render records according to the selected dataset.
 *
 * @param {Array} records - Records to display
 * @param {string} datasetType - events, students, or faqs
 * @param {HTMLElement} container - Target container
 */
export function renderRecords(records, datasetType, container) {

    container.innerHTML = "";

    /* =========================================
       EMPTY STATE
       ========================================= */

    if (records.length === 0) {

        const emptyCard = document.createElement("div");

        emptyCard.className = "empty-message";

        emptyCard.innerHTML = `
            <h3>No matching records found</h3>
            <p>
                Try changing your search or filter options.
            </p>
        `;

        container.appendChild(emptyCard);

        return;
    }


    /* =========================================
       EVENTS
       ========================================= */

    if (datasetType === "events") {

        records.forEach(event => {

            const card = document.createElement("article");
            card.className = "data-card";

            const cardHeader = document.createElement("div");
            cardHeader.className = "card-header";

            const title = document.createElement("h3");
            title.className = "card-title";
            title.textContent = event.title;

            const id = document.createElement("span");
            id.className = "card-id";
            id.textContent = `#${event.id}`;

            cardHeader.appendChild(title);
            cardHeader.appendChild(id);


            const category = document.createElement("span");
            category.className = "card-category";
            category.textContent = event.category;


            const description = document.createElement("p");
            description.className = "card-description";
            description.textContent = event.description;


            const details = document.createElement("div");
            details.className = "card-details";


            const dateDetail = document.createElement("p");
            dateDetail.innerHTML =
                `<strong>Date:</strong> ${event.date}`;


            const locationDetail = document.createElement("p");
            locationDetail.innerHTML =
                `<strong>Location:</strong> ${event.location}`;


            const organizerDetail = document.createElement("p");
            organizerDetail.innerHTML =
                `<strong>Organizer:</strong> ${event.organizer}`;


            details.appendChild(dateDetail);
            details.appendChild(locationDetail);
            details.appendChild(organizerDetail);


            card.appendChild(cardHeader);
            card.appendChild(category);
            card.appendChild(description);
            card.appendChild(details);

            container.appendChild(card);
        });

        return;
    }


    /* =========================================
       STUDENTS
       ========================================= */

    if (datasetType === "students") {

        records.forEach(student => {

            const card = document.createElement("article");
            card.className = "data-card";


            const cardHeader = document.createElement("div");
            cardHeader.className = "card-header";


            const title = document.createElement("h3");
            title.className = "card-title";
            title.textContent = student.name;


            const id = document.createElement("span");
            id.className = "card-id";
            id.textContent = `#${student.id}`;


            cardHeader.appendChild(title);
            cardHeader.appendChild(id);


            const category = document.createElement("span");
            category.className = "card-category";
            category.textContent = student.status;


            const description = document.createElement("p");
            description.className = "card-description";
            description.textContent = student.email;


            const details = document.createElement("div");
            details.className = "card-details";


            const courseDetail = document.createElement("p");
            courseDetail.innerHTML =
                `<strong>Course:</strong> ${student.course}`;


            const semesterDetail = document.createElement("p");
            semesterDetail.innerHTML =
                `<strong>Semester:</strong> ${student.semester}`;


            const departmentDetail = document.createElement("p");
            departmentDetail.innerHTML =
                `<strong>Department:</strong> ${student.department}`;


            const cityDetail = document.createElement("p");
            cityDetail.innerHTML =
                `<strong>City:</strong> ${student.city}`;


            details.appendChild(courseDetail);
            details.appendChild(semesterDetail);
            details.appendChild(departmentDetail);
            details.appendChild(cityDetail);


            card.appendChild(cardHeader);
            card.appendChild(category);
            card.appendChild(description);
            card.appendChild(details);

            container.appendChild(card);
        });

        return;
    }


    /* =========================================
       FAQs
       ========================================= */

    if (datasetType === "faqs") {

        records.forEach(faq => {

            const card = document.createElement("article");
            card.className = "data-card";


            const cardHeader = document.createElement("div");
            cardHeader.className = "card-header";


            const title = document.createElement("h3");
            title.className = "card-title";
            title.textContent = faq.question;


            const id = document.createElement("span");
            id.className = "card-id";
            id.textContent = `#${faq.id}`;


            cardHeader.appendChild(title);
            cardHeader.appendChild(id);


            const category = document.createElement("span");
            category.className = "card-category";
            category.textContent = faq.category;


            const description = document.createElement("p");
            description.className = "card-description";
            description.textContent = faq.answer;


            card.appendChild(cardHeader);
            card.appendChild(category);
            card.appendChild(description);

            container.appendChild(card);
        });

        return;
    }


    /* =========================================
       UNKNOWN DATASET
       ========================================= */

    const errorCard = document.createElement("div");

    errorCard.className = "empty-message";

    errorCard.innerHTML = `
        <h3>Unsupported dataset</h3>
        <p>
            The selected dataset cannot be displayed.
        </p>
    `;

    container.appendChild(errorCard);
}


/* =========================================
   BACKWARD COMPATIBILITY
   ========================================= */

/**
 * Existing Events renderer.
 * Kept temporarily so the current app.js
 * does not break before Step 14B.
 */
export function renderEvents(events, container) {

    renderRecords(events, "events", container);
}