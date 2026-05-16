<?php
declare(strict_types=1);

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . e(generateCsrfToken()) . '">';
}

function checkCsrf(): void
{
    $token = $_POST['_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function isRateLimited(string $ip, int $maxAttempts = 5, int $windowMinutes = 15): bool
{
    $db = getDb();
    $stmt = $db->prepare(
        'SELECT COUNT(*) FROM login_logs WHERE ip = :ip AND success = 0 AND created_at > datetime("now", :window)'
    );
    $stmt->execute([
        ':ip' => $ip,
        ':window' => "-{$windowMinutes} minutes",
    ]);
    return (int)$stmt->fetchColumn() >= $maxAttempts;
}

function logLogin(string $ip, string $userAgent, bool $success): void
{
    $db = getDb();
    $stmt = $db->prepare('INSERT INTO login_logs (ip, user_agent, success) VALUES (:ip, :ua, :s)');
    $stmt->execute([':ip' => $ip, ':ua' => $userAgent, ':s' => (int)$success]);
}

function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
