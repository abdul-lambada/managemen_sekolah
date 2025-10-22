<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
require_once $basePath . '/config/app.php';
require_once $basePath . '/config/database.php';
require_once $basePath . '/autoload.php';
require_once $basePath . '/helpers/app.php';

date_default_timezone_set('Asia/Jakarta');

$options = getopt('', ['limit::']);
$batchLimit = isset($options['limit']) ? max(1, (int) $options['limit']) : 20;

$pdo = db();

$configStmt = $pdo->query('SELECT * FROM whatsapp_config ORDER BY id ASC LIMIT 1');
$config = $configStmt->fetch();

if (!$config || empty($config['api_key'])) {
    fwrite(STDERR, "[ERROR] Konfigurasi WhatsApp tidak ditemukan atau API key kosong.\n");
    exit(1);
}

$pendingStmt = $pdo->prepare('SELECT * FROM whatsapp_logs WHERE status = :status ORDER BY created_at ASC LIMIT :limit');
$pendingStmt->bindValue(':status', 'pending');
$pendingStmt->bindValue(':limit', $batchLimit, PDO::PARAM_INT);
$pendingStmt->execute();
$jobs = $pendingStmt->fetchAll();

$successCount = 0;
$failureCount = 0;
$processedCount = count($jobs);

if (!$jobs) {
    updateSystemStat($pdo, 'whatsapp_dispatch_last_run', json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'processed' => 0,
        'success' => 0,
        'failure' => 0,
        'status' => 'idle',
        'message' => 'Tidak ada pesan pending.'
    ], JSON_UNESCAPED_UNICODE));

    fwrite(STDOUT, "Tidak ada pesan pending.\n");
    exit(0);
}

foreach ($jobs as $job) {
    $payload = buildPayload($job, $config);

    try {
        $response = sendFonnteRequest($config['api_url'] ?? 'https://api.fonnte.com', $config['api_key'], $payload);
        $status = ($response['status'] ?? false) ? 'success' : 'failed';
        $detail = $response['detail'] ?? ($response['message'] ?? '');
        $messageId = null;

        if (!empty($response['id'])) {
            $messageId = is_array($response['id']) ? implode(',', $response['id']) : (string) $response['id'];
        }

        updateLog($pdo, (int) $job['id'], $status, $messageId, json_encode($response, JSON_UNESCAPED_UNICODE));

        if ($status === 'success') {
            $successCount++;
            markAutomationLog($pdo, (int) $job['id'], true, null);
            fwrite(STDOUT, "[OK] Pesan ke {$job['phone_number']} berhasil dikirim.\n");
        } else {
            $failureCount++;
            markAutomationLog($pdo, (int) $job['id'], false, $detail);
            fwrite(STDOUT, "[FAIL] Pesan ke {$job['phone_number']} gagal: {$detail}\n");
        }
    } catch (Throwable $e) {
        $failureCount++;
        fwrite(STDERR, "[ERROR] Pengiriman ke {$job['phone_number']} gagal: {$e->getMessage()}\n");
        updateLog($pdo, (int) $job['id'], 'failed', null, $e->getMessage());
        markAutomationLog($pdo, (int) $job['id'], false, $e->getMessage());
    }
}

updateSystemStat($pdo, 'whatsapp_dispatch_last_run', json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'processed' => $processedCount,
    'success' => $successCount,
    'failure' => $failureCount,
    'status' => $failureCount > 0 ? 'warning' : 'success',
    'message' => $failureCount > 0 ? 'Beberapa pesan gagal dikirim.' : 'Seluruh pesan berhasil dikirim.'
], JSON_UNESCAPED_UNICODE));

fwrite(STDOUT, "Selesai. Berhasil: {$successCount}, Gagal: {$failureCount}.\n");
exit($failureCount > 0 ? 2 : 0);

/**
 * Build payload for Fonnte API.
 */
function buildPayload(array $job, array $config): array
{
    $payload = [
        'target' => $job['phone_number'],
    ];

    if (!empty($job['message'])) {
        $payload['message'] = $job['message'];
    }

    if (!empty($job['template_name'])) {
        $payload['template'] = $job['template_name'];
    }

    if (!empty($config['device_id'])) {
        $payload['device'] = $config['device_id'];
    }

    if (!empty($config['delay'])) {
        $payload['delay'] = (int) $config['delay'];
    }

    if (!empty($config['callback_url'])) {
        $payload['url'] = $config['callback_url'];
    }

    return $payload;
}

/**
 * Send request to Fonnte API.
 */
function sendFonnteRequest(string $apiUrl, string $apiKey, array $payload): array
{
    $ch = curl_init($apiUrl . '/send');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $apiKey,
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $result = curl_exec($ch);

    if ($result === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('cURL error: ' . $error);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($result, true);

    if ($httpCode >= 400 || $decoded === null) {
        throw new RuntimeException('Invalid response: ' . $result);
    }

    return $decoded;
}

/**
 * Update log entry with status/outcome.
 */
function updateLog(PDO $pdo, int $id, string $status, ?string $messageId, ?string $response): void
{
    $stmt = $pdo->prepare('UPDATE whatsapp_logs SET status = :status, message_id = :message_id, response = :response, retry_count = CASE WHEN :is_success = 1 THEN retry_count ELSE retry_count + 1 END, sent_at = CASE WHEN :is_success = 1 THEN NOW() ELSE sent_at END, updated_at = NOW() WHERE id = :id');
    $stmt->execute([
        'status' => $status,
        'message_id' => $messageId,
        'response' => $response,
        'is_success' => $status === 'success' ? 1 : 0,
        'id' => $id,
    ]);
}

function markAutomationLog(PDO $pdo, int $logId, bool $success, ?string $error): void
{
    $stmt = $pdo->prepare(
        'UPDATE whatsapp_automation_logs SET message_sent = :sent, error_message = :error, updated_at = NOW()
         WHERE whatsapp_log_id = :log_id'
    );
    $stmt->execute([
        'sent' => $success ? 1 : 0,
        'error' => $error,
        'log_id' => $logId,
    ]);
}

function updateSystemStat(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('INSERT INTO system_stats (stat_key, stat_value, updated_at) VALUES (:key1, :value1, NOW()) ON DUPLICATE KEY UPDATE stat_value = :value2, updated_at = NOW()');
    $stmt->execute([
        'key1' => $key,
        'value1' => $value,
        'value2' => $value,
    ]);
}
