/* =========================================
   WDF PRACTICAL 6
   UTILITY FUNCTIONS MODULE
   ========================================= */


/* =========================================
   SEARCH
   ========================================= */

/**
 * Search records across all available fields.
 *
 * @param {Array} records
 * @param {string} searchTerm
 * @returns {Array}
 */
export function searchRecords(records, searchTerm) {

    const query = searchTerm.trim().toLowerCase();

    if (query === "") {
        return records;
    }

    return records.filter(record => {

        const searchableText =
            Object.values(record)
                .join(" ")
                .toLowerCase();

        return searchableText.includes(query);
    });
}


/* =========================================
   FILTER
   ========================================= */

/**
 * Filter records using a selected field.
 *
 * @param {Array} records
 * @param {string} filterField
 * @param {string} filterValue
 * @returns {Array}
 */
export function filterRecords(
    records,
    filterField,
    filterValue
) {

    if (
        filterField === "all" ||
        filterValue === "all"
    ) {
        return records;
    }

    return records.filter(record =>
        String(record[filterField]) === String(filterValue)
    );
}


/* =========================================
   SORT
   ========================================= */

/**
 * Sort records according to the selected option.
 *
 * @param {Array} records
 * @param {string} sortOption
 * @param {string} datasetType
 * @returns {Array}
 */
export function sortRecords(
    records,
    sortOption,
    datasetType
) {

    const sortedRecords = [...records];


    /* -----------------------------------------
       DEFAULT ORDER
       ----------------------------------------- */

    if (sortOption === "default") {
        return sortedRecords;
    }


    /* =========================================
       EVENTS
       ========================================= */

    if (datasetType === "events") {

        if (sortOption === "title-asc") {

            return sortedRecords.sort((a, b) =>
                a.title.localeCompare(b.title)
            );
        }


        if (sortOption === "title-desc") {

            return sortedRecords.sort((a, b) =>
                b.title.localeCompare(a.title)
            );
        }


        if (sortOption === "date-asc") {

            return sortedRecords.sort((a, b) =>
                new Date(a.date) - new Date(b.date)
            );
        }


        if (sortOption === "date-desc") {

            return sortedRecords.sort((a, b) =>
                new Date(b.date) - new Date(a.date)
            );
        }
    }


    /* =========================================
       STUDENTS
       ========================================= */

    if (datasetType === "students") {

        if (sortOption === "name-asc") {

            return sortedRecords.sort((a, b) =>
                a.name.localeCompare(b.name)
            );
        }


        if (sortOption === "name-desc") {

            return sortedRecords.sort((a, b) =>
                b.name.localeCompare(a.name)
            );
        }


        if (sortOption === "semester-asc") {

            return sortedRecords.sort((a, b) =>
                Number(a.semester) - Number(b.semester)
            );
        }


        if (sortOption === "semester-desc") {

            return sortedRecords.sort((a, b) =>
                Number(b.semester) - Number(a.semester)
            );
        }
    }


    /* =========================================
       FAQs
       ========================================= */

    if (datasetType === "faqs") {

        if (sortOption === "question-asc") {

            return sortedRecords.sort((a, b) =>
                a.question.localeCompare(b.question)
            );
        }


        if (sortOption === "question-desc") {

            return sortedRecords.sort((a, b) =>
                b.question.localeCompare(a.question)
            );
        }


        if (sortOption === "category-asc") {

            return sortedRecords.sort((a, b) =>
                a.category.localeCompare(b.category)
            );
        }


        if (sortOption === "category-desc") {

            return sortedRecords.sort((a, b) =>
                b.category.localeCompare(a.category)
            );
        }
    }


    return sortedRecords;
}


/* =========================================
   PAGINATION
   ========================================= */

/**
 * Return only the records for the current page.
 *
 * @param {Array} records
 * @param {number} currentPage
 * @param {number} recordsPerPage
 * @returns {Array}
 */
export function paginateRecords(
    records,
    currentPage,
    recordsPerPage
) {

    const startIndex =
        (currentPage - 1) * recordsPerPage;

    const endIndex =
        startIndex + recordsPerPage;

    return records.slice(startIndex, endIndex);
}