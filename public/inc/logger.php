<?php

/**
 * Logger interne pour l’interface PHP
 * Compatible avec la table `logs` utilisée par Python
 */

function log_event(PDO $pdo, string $type, string $message, array $context = []): void
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO logs (type, message, context, created_at)
            VALUES (:type, :message, :context, NOW())
        ");

        $stmt->execute([
            ':type'    => $type,
            ':message' => $message,
            ':context' => json_encode($context, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Exception $e) {
        // On ne casse jamais l’interface si le log échoue
        error_log("Erreur logger PHP : " . $e->getMessage());
    }
}
