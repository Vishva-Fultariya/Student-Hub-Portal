/* =========================================
   WDF PRACTICAL 6
   MAIN APPLICATION MODULE
   ========================================= */

import { fetchJSON } from "./api.js";

import { renderRecords } from "./render.js";

import {
    searchRecords,
    filterRecords,
    sortRecords,
    paginateRecords
} from "./utils.js";


/* =========================================
   DATASET CONFIGURATION
   ========================================= */

const datasetPaths = {
    events: "data/events.json",
    students: "data/students.json",
    faqs: "data/faqs.json"
};

const datasetNames = {
    events: "Events",
    students: "Students",
    faqs: "FAQs"
};


/* =========================================
   APPLICATION STATE
   ========================================= */

let currentDataset = "events";

let currentData = [];

let processedData = [];

let currentPage = 1;

const recordsPerPage = 5;


/* =========================================
   DOM ELEMENTS
   ========================================= */

const datasetSelect =
    document.getElementById("datasetSelect");

const searchInput =
    document.getElementById("searchInput");

const filterSelect =
    document.getElementById("filterSelect");

const sortSelect =
    document.getElementById("sortSelect");

const datasetTitle =
    document.getElementById("datasetTitle");

const recordCount =
    document.getElementById("recordCount");

const statusMessage =
    document.getElementById("statusMessage");

const dataContainer =
    document.getElementById("dataContainer");

const previousButton =
    document.getElementById("previousButton");

const nextButton =
    document.getElementById("nextButton");

const pageNumbers =
    document.getElementById("pageNumbers");


/* =========================================
   FILTER CONFIGURATION
   ========================================= */

/**
 * Return the field used for filtering.
 */
function getFilterField(datasetType) {

    if (datasetType === "students") {
        return "department";
    }

    return "category";
}


/**
 * Populate filter options according
 * to the selected dataset.
 */
function populateFilters(records, datasetType) {

    const filterField =
        getFilterField(datasetType);

    const uniqueValues = [
        ...new Set(
            records.map(record =>
                record[filterField]
            )
        )
    ];

    filterSelect.innerHTML = "";


    const allOption =
        document.createElement("option");

    allOption.value = "all";

    allOption.textContent = "All";

    filterSelect.appendChild(allOption);


    uniqueValues.forEach(value => {

        const option =
            document.createElement("option");

        option.value = value;

        option.textContent = value;

        filterSelect.appendChild(option);
    });
}


/* =========================================
   SORT OPTIONS
   ========================================= */

/**
 * Populate sort options according
 * to the selected dataset.
 */
function populateSortOptions(datasetType) {

    sortSelect.innerHTML = "";


    const defaultOption =
        document.createElement("option");

    defaultOption.value = "default";

    defaultOption.textContent = "Default Order";

    sortSelect.appendChild(defaultOption);


    let options = [];


    /* -----------------------------------------
       EVENTS
       ----------------------------------------- */

    if (datasetType === "events") {

        options = [
            {
                value: "title-asc",
                text: "Title A → Z"
            },
            {
                value: "title-desc",
                text: "Title Z → A"
            },
            {
                value: "date-asc",
                text: "Date: Earliest → Latest"
            },
            {
                value: "date-desc",
                text: "Date: Latest → Earliest"
            }
        ];
    }


    /* -----------------------------------------
       STUDENTS
       ----------------------------------------- */

    if (datasetType === "students") {

        options = [
            {
                value: "name-asc",
                text: "Name A → Z"
            },
            {
                value: "name-desc",
                text: "Name Z → A"
            },
            {
                value: "semester-asc",
                text: "Semester: Low → High"
            },
            {
                value: "semester-desc",
                text: "Semester: High → Low"
            }
        ];
    }


    /* -----------------------------------------
       FAQs
       ----------------------------------------- */

    if (datasetType === "faqs") {

        options = [
            {
                value: "question-asc",
                text: "Question A → Z"
            },
            {
                value: "question-desc",
                text: "Question Z → A"
            },
            {
                value: "category-asc",
                text: "Category A → Z"
            },
            {
                value: "category-desc",
                text: "Category Z → A"
            }
        ];
    }


    options.forEach(item => {

        const option =
            document.createElement("option");

        option.value = item.value;

        option.textContent = item.text;

        sortSelect.appendChild(option);
    });
}


/* =========================================
   PAGINATION
   ========================================= */

function renderPagination(records) {

    const totalPages =
        Math.ceil(
            records.length / recordsPerPage
        );

    pageNumbers.innerHTML = "";


    previousButton.disabled =
        currentPage === 1;


    nextButton.disabled =
        currentPage === totalPages ||
        totalPages === 0;


    for (
        let page = 1;
        page <= totalPages;
        page++
    ) {

        const button =
            document.createElement("button");

        button.type = "button";

        button.className = "page-button";

        button.textContent = page;


        if (page === currentPage) {

            button.classList.add("active");
        }


        button.addEventListener(
            "click",
            () => {

                currentPage = page;

                updateDisplayedData(
                    processedData
                );
            }
        );


        pageNumbers.appendChild(button);
    }
}


/* =========================================
   DISPLAY DATA
   ========================================= */

function updateDisplayedData(records) {

    const totalPages =
        Math.ceil(
            records.length / recordsPerPage
        );


    if (totalPages === 0) {

        currentPage = 1;

    } else if (
        currentPage > totalPages
    ) {

        currentPage = totalPages;
    }


    const pageData =
        paginateRecords(
            records,
            currentPage,
            recordsPerPage
        );


    renderRecords(
        pageData,
        currentDataset,
        dataContainer
    );


    renderPagination(records);
}


/* =========================================
   SEARCH + FILTER + SORT PIPELINE
   ========================================= */

function applyDataOperations(
    resetPage = true
) {

    let result =
        [...currentData];


    /* -----------------------------------------
       SEARCH
       ----------------------------------------- */

    const searchTerm =
        searchInput.value;

    result =
        searchRecords(
            result,
            searchTerm
        );


    /* -----------------------------------------
       FILTER
       ----------------------------------------- */

    const filterField =
        getFilterField(
            currentDataset
        );

    const selectedFilter =
        filterSelect.value;

    result =
        filterRecords(
            result,
            filterField,
            selectedFilter
        );


    /* -----------------------------------------
       SORT
       ----------------------------------------- */

    const selectedSort =
        sortSelect.value;

    result =
        sortRecords(
            result,
            selectedSort,
            currentDataset
        );


    processedData = result;


    if (resetPage) {

        currentPage = 1;
    }


    recordCount.textContent =
        processedData.length;


    updateDisplayedData(
        processedData
    );


    /* -----------------------------------------
       STATUS MESSAGE
       ----------------------------------------- */

    if (
        searchTerm.trim() === "" &&
        selectedFilter === "all" &&
        selectedSort === "default"
    ) {

        statusMessage.textContent =
            `Successfully loaded ${currentData.length} ${datasetNames[currentDataset].toLowerCase()} records.`;

    } else {

        statusMessage.textContent =
            `Showing ${processedData.length} matching ${datasetNames[currentDataset].toLowerCase()} records.`;
    }
}


/* =========================================
   LOAD DATASET
   ========================================= */

async function loadDataset(datasetType) {

    currentDataset =
        datasetType;


    const filePath =
        datasetPaths[datasetType];


    /* -----------------------------------------
       LOADING STATE
       ----------------------------------------- */

    statusMessage.textContent =
        `Loading ${datasetNames[datasetType].toLowerCase()}...`;
    
    statusMessage.className =
        "status-message loading";


    dataContainer.innerHTML = "";

    pageNumbers.innerHTML = "";


    try {

        const data =
            await fetchJSON(filePath);


        currentData =
            data;


        processedData =
            data;


        currentPage =
            1;


        datasetTitle.textContent =
            datasetNames[datasetType];


        populateFilters(
            data,
            datasetType
        );


        populateSortOptions(
            datasetType
        );


        searchInput.value = "";


        applyDataOperations();


        statusMessage.className =
            "status-message success";


        console.log(
            `${datasetNames[datasetType]} data loaded:`,
            data
        );

        console.log(
            `Total records: ${data.length}`
        );

    } catch (error) {

        currentData = [];

        processedData = [];

        recordCount.textContent = "0";

        datasetTitle.textContent =
            datasetNames[datasetType];


        dataContainer.innerHTML = `
            <div class="empty-message">
                <h3>Unable to load data</h3>
                <p>
                    Please check the JSON file and server.
                </p>
            </div>
        `;


        previousButton.disabled = true;

        nextButton.disabled = true;

        pageNumbers.innerHTML = "";


        statusMessage.textContent =
            `Unable to load ${datasetNames[datasetType].toLowerCase()} data. Please check the JSON file and server.`;

        statusMessage.className =
            "status-message error";


        console.error(
            "Dataset loading error:",
            error
        );
    }
}


/* =========================================
   DATASET SELECTOR
   ========================================= */

datasetSelect.addEventListener(
    "change",
    () => {

        const selectedDataset =
            datasetSelect.value;

        loadDataset(
            selectedDataset
        );
    }
);


/* =========================================
   SEARCH
   ========================================= */

searchInput.addEventListener(
    "input",
    () => {

        applyDataOperations(true);
    }
);


/* =========================================
   FILTER
   ========================================= */

filterSelect.addEventListener(
    "change",
    () => {

        applyDataOperations(true);
    }
);


/* =========================================
   SORT
   ========================================= */

sortSelect.addEventListener(
    "change",
    () => {

        applyDataOperations(true);
    }
);


/* =========================================
   PREVIOUS PAGE
   ========================================= */

previousButton.addEventListener(
    "click",
    () => {

        if (currentPage > 1) {

            currentPage--;

            updateDisplayedData(
                processedData
            );
        }
    }
);


/* =========================================
   NEXT PAGE
   ========================================= */

nextButton.addEventListener(
    "click",
    () => {

        const totalPages =
            Math.ceil(
                processedData.length /
                recordsPerPage
            );


        if (
            currentPage < totalPages
        ) {

            currentPage++;

            updateDisplayedData(
                processedData
            );
        }
    }
);


/* =========================================
   INITIAL DATASET
   ========================================= */

loadDataset("events");