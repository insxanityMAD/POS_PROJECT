<?php
declare(strict_types=1);

/**
 * Record an entry in system_logs.
 * Call this right after a successful create/update/delete/status-change.
 */
function logAction(PDO $pdo, ?int $userId, string $action, string $module, ?int $recordId = null, ?string $description = null): void
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO system_logs (user_id, action, module, record_id, description, ip_address)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->execute([
            $userId,
            $action,
            $module,
            $recordId,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (PDOException $e) {
        // Never let a logging failure break the actual operation
        error_log('logAction failed: ' . $e->getMessage());
    }
}
