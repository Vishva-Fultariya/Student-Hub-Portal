
<?php

/**
 * Upload an event poster.
 *
 * Returns:
 * [
 *   "success" => true/false,
 *   "filename" => "stored filename or null",
 *   "error" => "error message or null
 * ]
 */
function upload_poster(array $file): array
{
    $result = [
        "success" => false,
        "filename" => null,
        "error" => null
    ];

    // No poster is allowed when creating or editing an event.
    if (!isset($file["error"]) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        $result["success"] = true;
        return $result;
    }

    if (is_array($file["name"] ?? null)) {
        $result["error"] = "Invalid file upload.";
        return $result;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $result["error"] = match ($file["error"]) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                "Poster must not exceed the allowed file size.",
            UPLOAD_ERR_PARTIAL =>
                "The poster upload was incomplete. Please try again.",
            default =>
                "Poster upload failed. Please try again."
        };
        return $result;
    }

    $maxSize = 2 * 1024 * 1024;

    if (!isset($file["size"]) || $file["size"] <= 0) {
        $result["error"] = "The uploaded file is empty or invalid.";
        return $result;
    }

    if ($file["size"] > $maxSize) {
        $result["error"] = "Poster size must not exceed 2 MB.";
        return $result;
    }

    if (
        empty($file["tmp_name"]) ||
        !is_uploaded_file($file["tmp_name"])
    ) {
        $result["error"] = "Invalid uploaded file.";
        return $result;
    }

    $extension = strtolower(
        pathinfo($file["name"] ?? "", PATHINFO_EXTENSION)
    );

    $allowedTypes = [
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png",
        "webp" => "image/webp"
    ];

    if (!isset($allowedTypes[$extension])) {
        $result["error"] =
            "Invalid file type. Only JPG, JPEG, PNG, and WebP are allowed.";
        return $result;
    }

    // Verify the actual image content, not just the filename.
    $imageInfo = @getimagesize($file["tmp_name"]);

    if (
        $imageInfo === false ||
        !isset($imageInfo["mime"]) ||
        $imageInfo["mime"] !== $allowedTypes[$extension]
    ) {
        $result["error"] =
            "The file is not a valid image of the selected type.";
        return $result;
    }

    $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . "uploads";

    if (
        !is_dir($uploadDirectory) &&
        !mkdir($uploadDirectory, 0755, true) &&
        !is_dir($uploadDirectory)
    ) {
        $result["error"] = "The upload directory could not be created.";
        return $result;
    }

    if (!is_writable($uploadDirectory)) {
        $result["error"] = "The upload directory is not writable.";
        return $result;
    }

    // Generate an unpredictable filename; never trust the original name.
    try {
        $newFilename = bin2hex(random_bytes(16)) . "." . $extension;
    } catch (Throwable $e) {
        $result["error"] = "Could not generate a safe filename.";
        return $result;
    }

    $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $newFilename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        $result["error"] = "Could not save the uploaded poster.";
        return $result;
    }

    $result["success"] = true;
    $result["filename"] = $newFilename;

    return $result;
}
?>
