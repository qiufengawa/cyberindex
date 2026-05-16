<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$action = $_GET['action'] ?? '';

// Handle login
if ($action === 'login' || !isLoggedIn()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'login' || $action === '')) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (isRateLimited($ip)) {
            $loginError = '登录尝试过多，请 15 分钟后再试';
        } else {
            $db = getDb();
            $stmt = $db->prepare('SELECT id, password_hash FROM admins WHERE username = :u');
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                logLogin($ip, $ua, true);
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $username;
                redirect('/admin/');
            } else {
                logLogin($ip, $ua, false);
                $loginError = '用户名或密码错误';
            }
        }
    }

    if (!isLoggedIn()) {
        renderLogin($loginError ?? null);
        exit;
    }
}

// Handle logout
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('/admin/?action=login');
}

// Dashboard
require_once __DIR__ . '/layout.php';
adminHeader('仪表盘', 'dashboard');

$db = getDb();
$projectCount = (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$skillCount = (int)$db->query('SELECT COUNT(*) FROM skills')->fetchColumn();
$awardCount = (int)$db->query('SELECT COUNT(*) FROM awards')->fetchColumn();
$contactCount = (int)$db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

// Recent login logs
$recentLogs = $db->query('SELECT * FROM login_logs ORDER BY created_at DESC LIMIT 5')->fetchAll();

// DB info
$configData = require ROOT_PATH . '/config.inc.php';
$dbPath = $configData['db_path'] ?? '';
$dbSize = file_exists($dbPath) ? round(filesize($dbPath) / 1024, 1) : 0;
?>

<div class="dash-grid">
    <div class="dash-card">
        <div class="dash-card-val accent-green"><?= $projectCount ?></div>
        <div class="dash-card-label">项目</div>
        <a href="/admin/projects.php" class="dash-card-link">管理 →</a>
    </div>
    <div class="dash-card">
        <div class="dash-card-val accent-magenta"><?= $skillCount ?></div>
        <div class="dash-card-label">技能</div>
        <a href="/admin/skills.php" class="dash-card-link">管理 →</a>
    </div>
    <div class="dash-card">
        <div class="dash-card-val accent-cyan"><?= $awardCount ?></div>
        <div class="dash-card-label">荣誉</div>
        <a href="/admin/awards.php" class="dash-card-link">管理 →</a>
    </div>
    <div class="dash-card">
        <div class="dash-card-val" style="color:var(--fg);"><?= $contactCount ?></div>
        <div class="dash-card-label">联系方式</div>
        <a href="/admin/contact.php" class="dash-card-link">管理 →</a>
    </div>
</div>

<div class="dash-panels">
    <div class="dash-panel">
        <h2 class="dash-panel-title">系统信息</h2>
        <div class="dash-info-row"><span class="dash-info-key">PHP 版本</span><span><?= PHP_VERSION ?></span></div>
        <div class="dash-info-row"><span class="dash-info-key">SQLite</span><span><?= \SQLite3::version()['versionString'] ?></span></div>
        <div class="dash-info-row"><span class="dash-info-key">数据库大小</span><span><?= $dbSize ?> KB</span></div>
        <div class="dash-info-row"><span class="dash-info-key">数据库路径</span><span class="dash-info-path"><?= e(basename(dirname($dbPath))) ?>/database.db</span></div>
        <div class="dash-info-row"><span class="dash-info-key">管理员</span><span><?= e($_SESSION['admin_user']) ?></span></div>
    </div>
    <div class="dash-panel">
        <h2 class="dash-panel-title">最近登录</h2>
        <?php if (empty($recentLogs)): ?>
            <p style="color:var(--muted-fg);font-size:0.8rem;">暂无记录</p>
        <?php else: ?>
            <?php foreach ($recentLogs as $log): ?>
            <div class="dash-log-row">
                <span class="dash-log-status <?= $log['success'] ? 'log-ok' : 'log-fail' ?>"><?= $log['success'] ? '✓' : '✗' ?></span>
                <span class="dash-log-ip"><?= e($log['ip']) ?></span>
                <span class="dash-log-time"><?= e(substr($log['created_at'], 5, 11)) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="dash-quick">
    <h2 class="dash-panel-title">快捷操作</h2>
    <div class="dash-quick-grid">
        <a href="/admin/profile.php" class="dash-quick-btn">编辑个人资料</a>
        <a href="/admin/projects.php?action=add" class="dash-quick-btn">添加新项目</a>
        <a href="/admin/awards.php?action=add" class="dash-quick-btn">添加荣誉</a>
        <a href="/admin/settings.php" class="dash-quick-btn">修改密码</a>
    </div>
</div>

<?php
adminFooter();

function renderLogin(?string $error): void {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN // QIU ADMIN</title>
    <link rel="stylesheet" href="/admin/assets/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card">
    <h1 class="login-title">ACCESS</h1>
    <p class="login-sub">&gt; 身份验证</p>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST" action="/admin/?action=login">
        <label for="username">USERNAME</label>
        <input type="text" id="username" name="username" autocomplete="username" required>
        <label for="password">PASSWORD</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <button type="submit" class="btn">AUTHENTICATE →</button>
    </form>
</div>
</body>
</html>
<?php
}
