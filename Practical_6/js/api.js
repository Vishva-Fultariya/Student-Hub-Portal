/* =========================================
   WDF PRACTICAL 6
   API / DATA FETCHING MODULE
   ========================================= */

/**
 * Fetch JSON data from an external JSON file.
 *
 * @param {string} filePath - Path to the JSON file
 * @returns {Promise<Array>} Parsed JSON data
 */
export async function fetchJSON(filePath) {

    try {

        const response = await fetch(filePath);

        /*
         * fetch() does not automatically reject the Promise
         * for HTTP errors such as 404.
         *
         * Therefore, we explicitly check response.ok.
         */
        if (!response.ok) {
            throw new Error(
                `HTTP Error: ${response.status} ${response.statusText}`
            );
        }

        /*
         * Convert the response body from JSON text
         * into a JavaScript value.
         */
        const data = await response.json();

        /*
         * Make sure the received data is an array.
         */
        if (!Array.isArray(data)) {
            throw new Error("Invalid JSON format: Expected an array.");
        }

        return data;

    } catch (error) {

        /*
         * Log the original error for developer testing.
         */
        console.error("JSON Fetch Error:", error);

        /*
         * Re-throw the error so app.js can display
         * a user-friendly error message.
         */
        throw error;
    }
}