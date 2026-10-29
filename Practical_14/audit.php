
<?php

function log_audit(
    PDO $pdo,
    ?int $userId,
    string $action,
    string $details = ''
): void {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

    $sql = "INSERT INTO audit_logs
            (user_id, action, details, ip_address)
            VALUES (:user_id, :action, :details, :ip_address)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':user_id' => $userId,
        ':action' => $action,
        ':details' => $details,
        ':ip_address' => $ipAddress
    ]);
}
