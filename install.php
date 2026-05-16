<?php
declare(strict_types=1);

define('ROOT_PATH', __DIR__);

if (file_exists(ROOT_PATH . '/config.inc.php')) {
    http_response_code(403);
    exit('Already installed.');
}

$errors = [];
$step = (int)($_GET['step'] ?? 1);

// Step 1: Environment check
function checkEnvironment(): array
{
    $checks = [];
    $checks['php_version'] = [
        'label' => 'PHP >= 8.3',
        'pass' => version_compare(PHP_VERSION, '8.3.0', '>='),
        'value' => PHP_VERSION,
    ];
    $checks['sqlite3'] = [
        'label' => 'SQLite3 扩展',
        'pass' => extension_loaded('pdo_sqlite'),
        'value' => extension_loaded('pdo_sqlite') ? '已加载' : '未加载',
    ];
    $checks['writable'] = [
        'label' => '项目目录可写',
        'pass' => is_writable(ROOT_PATH),
        'value' => is_writable(ROOT_PATH) ? '可写' : '不可写',
    ];
    $checks['argon2id'] = [
        'label' => 'Argon2id 支持',
        'pass' => defined('PASSWORD_ARGON2ID'),
        'value' => defined('PASSWORD_ARGON2ID') ? '支持' : '不支持',
    ];
    return $checks;
}

// Step 2: Process installation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $siteTitle = trim($_POST['site_title'] ?? '');

    if (strlen($username) < 3) {
        $errors[] = '用户名至少 3 个字符';
    }
    if (strlen($password) < 8) {
        $errors[] = '密码至少 8 个字符';
    }
    if ($password !== $passwordConfirm) {
        $errors[] = '两次密码不一致';
    }
    if (empty($siteTitle)) {
        $siteTitle = 'QIU';
    }

    if (empty($errors)) {
        $hashDir = bin2hex(random_bytes(16));
        $dataPath = ROOT_PATH . '/data/' . $hashDir;

        if (!mkdir($dataPath, 0750, true)) {
            $errors[] = '无法创建数据目录';
        } else {
            $dbFile = $dataPath . '/database.db';
            $hmacKey = bin2hex(random_bytes(32));

            try {
                $pdo = new PDO('sqlite:' . $dbFile);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec('PRAGMA journal_mode=WAL');
                $pdo->exec('PRAGMA foreign_keys=ON');

                $schema = <<<'SQL'
CREATE TABLE admins (
    id INTEGER PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    totp_secret TEXT DEFAULT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE profile (
    id INTEGER PRIMARY KEY,
    key TEXT NOT NULL UNIQUE,
    value TEXT NOT NULL,
    updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE skill_categories (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    color TEXT NOT NULL DEFAULT 'green',
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE skills (
    id INTEGER PRIMARY KEY,
    category_id INTEGER NOT NULL REFERENCES skill_categories(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE projects (
    id INTEGER PRIMARY KEY,
    tag TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    detail TEXT DEFAULT '',
    techs TEXT NOT NULL DEFAULT '[]',
    source_url TEXT DEFAULT '',
    demo_url TEXT DEFAULT '',
    cover_url TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    visible INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE awards (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    organizer TEXT DEFAULT '',
    level TEXT DEFAULT '',
    date TEXT DEFAULT '',
    description TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    visible INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE contacts (
    id INTEGER PRIMARY KEY,
    icon TEXT NOT NULL,
    label TEXT NOT NULL,
    url TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    visible INTEGER DEFAULT 1
);

CREATE TABLE stats (
    id INTEGER PRIMARY KEY,
    value TEXT NOT NULL,
    label TEXT NOT NULL,
    color TEXT NOT NULL DEFAULT 'green',
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE login_logs (
    id INTEGER PRIMARY KEY,
    ip TEXT NOT NULL,
    user_agent TEXT,
    success INTEGER NOT NULL,
    created_at TEXT DEFAULT (datetime('now'))
);
SQL;
                $pdo->exec($schema);

                // Insert admin
                $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:u, :p)');
                $stmt->execute([
                    ':u' => $username,
                    ':p' => password_hash($password, PASSWORD_ARGON2ID),
                ]);

                // Insert default profile
                $defaults = [
                    'name' => $siteTitle,
                    'title' => $siteTitle,
                    'subtitle' => '大一学生 / 全栈开发者 / 数字世界探索者',
                    'bio' => '在代码与创意的交汇处构建未来。热衷于将想法转化为像素与逻辑，用技术解决真实世界的问题。',
                    'status_text' => 'SYSTEM STATUS: ONLINE',
                    'role' => 'Student Developer',
                    'year' => 'Freshman',
                    'location' => 'China',
                    'nav_about' => '关于',
                    'nav_skills' => '技术栈',
                    'nav_projects' => '项目',
                    'nav_contact' => '联系',
                    'hero_btn_projects' => '查看项目',
                    'hero_btn_contact' => '建立连接',
                    'about_title' => '关于我',
                    'about_terminal_file' => 'about.sh',
                    'about_terminal_cmd' => 'cat ./about.txt',
                    'skills_title' => '技术栈',
                    'skills_desc' => '以下是我目前掌握和正在学习的技术。持续进化中...',
                    'projects_title' => '项目',
                    'projects_desc' => '精选项目展示。每一个都是学习与实践的结晶。',
                    'contact_title' => '建立连接',
                    'contact_desc' => '无论是项目合作、技术交流，还是单纯想聊聊天，都欢迎联系我。',
                    'contact_terminal_file' => 'connect.sh',
                    'footer_text' => 'Designed & Built by ' . $siteTitle,
                    'footer_quote' => 'The future is already here — it\'s just not evenly distributed.',
                ];
                $stmt = $pdo->prepare('INSERT INTO profile (key, value) VALUES (:k, :v)');
                foreach ($defaults as $k => $v) {
                    $stmt->execute([':k' => $k, ':v' => $v]);
                }

                // Generate config file
                $configContent = "<?php\nreturn [\n"
                    . "    'db_path' => " . var_export($dbFile, true) . ",\n"
                    . "    'hmac_key' => " . var_export($hmacKey, true) . ",\n"
                    . "    'installed_at' => " . var_export(date('Y-m-d H:i:s'), true) . ",\n"
                    . "];\n";

                file_put_contents(ROOT_PATH . '/config.inc.php', $configContent);
                chmod(ROOT_PATH . '/config.inc.php', 0640);
                chmod($dataPath, 0750);
                chmod($dbFile, 0640);

                $step = 3;
            } catch (\Exception $ex) {
                $errors[] = '数据库初始化失败: ' . $ex->getMessage();
            }
        }
    }
}

$envChecks = checkEnvironment();
$allPass = !in_array(false, array_column($envChecks, 'pass'));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 // QIU INDEX</title>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600&family=Orbitron:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0f;
            --fg: #e0e0e0;
            --card: #12121a;
            --muted: #1c1c2e;
            --muted-fg: #6b7280;
            --accent: #00ff88;
            --accent-secondary: #ff00ff;
            --border: #2a2a3a;
            --destructive: #ff3366;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--fg);
            font-family: "JetBrains Mono", monospace;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .installer {
            background: var(--card);
            border: 1px solid var(--border);
            max-width: 560px;
            width: 100%;
            padding: 40px;
            clip-path: polygon(0 5px,5px 0,calc(100% - 5px) 0,100% 5px,100% calc(100% - 5px),calc(100% - 5px) 100%,5px 100%,0 calc(100% - 5px));
        }
        .installer-title {
            font-family: "Orbitron", monospace;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
            color: var(--accent);
        }
        .installer-step {
            font-size: 0.8rem;
            color: var(--muted-fg);
            margin-bottom: 32px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .check-list { list-style: none; margin-bottom: 24px; }
        .check-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }
        .check-pass { color: var(--accent); }
        .check-fail { color: var(--destructive); }
        label {
            display: block;
            font-size: 0.8rem;
            color: var(--muted-fg);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
            margin-top: 16px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--accent);
            font-family: inherit;
            font-size: 0.9rem;
            padding: 10px 14px;
            outline: none;
            transition: border-color 200ms;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 5px #00ff8840; }
        .errors {
            background: rgba(255, 51, 102, 0.1);
            border: 1px solid var(--destructive);
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 0.8rem;
            color: var(--destructive);
        }
        .errors li { margin-left: 16px; margin-bottom: 4px; }
        .btn {
            display: inline-block;
            font-family: inherit;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 12px 28px;
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent);
            cursor: pointer;
            margin-top: 24px;
            transition: all 150ms;
        }
        .btn:hover { background: var(--accent); color: var(--bg); }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .success-msg { color: var(--accent); line-height: 1.8; font-size: 0.9rem; }
        .success-msg a { color: var(--accent-secondary); }
    </style>
</head>
<body>
<div class="installer">

<?php if ($step === 1): ?>
    <h1 class="installer-title">安装向导</h1>
    <p class="installer-step">Step 1/2 // 环境检查</p>
    <ul class="check-list">
    <?php foreach ($envChecks as $check): ?>
        <li class="check-item">
            <span><?= $check['label'] ?></span>
            <span class="<?= $check['pass'] ? 'check-pass' : 'check-fail' ?>">
                <?= $check['value'] ?>
            </span>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php if ($allPass): ?>
        <a href="?step=2" class="btn">下一步 →</a>
    <?php else: ?>
        <p style="color:var(--destructive);font-size:0.85rem;">环境不满足要求，请先解决以上问题。</p>
    <?php endif; ?>

<?php elseif ($step === 2): ?>
    <h1 class="installer-title">安装向导</h1>
    <p class="installer-step">Step 2/2 // 管理员配置</p>
    <?php if (!empty($errors)): ?>
        <ul class="errors">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="POST" action="?step=2">
        <label for="username">管理员用户名</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>" required minlength="3" autocomplete="off">
        <label for="password">密码 (至少8位)</label>
        <input type="password" id="password" name="password" required minlength="8">
        <label for="password_confirm">确认密码</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <label for="site_title">站点标题</label>
        <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($siteTitle ?? 'QIU', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn">开始安装</button>
    </form>

<?php elseif ($step === 3): ?>
    <h1 class="installer-title">安装完成</h1>
    <p class="installer-step">// SYSTEM INITIALIZED</p>
    <div class="success-msg">
        <p>✓ 数据库已创建</p>
        <p>✓ 管理员账户已建立</p>
        <p>✓ 配置文件已生成</p>
        <br>
        <p style="color:var(--muted-fg);font-size:0.8rem;word-break:break-all;">
            数据库路径: <span style="color:var(--accent);"><?= htmlspecialchars($dbFile ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        </p>
        <br>
        <p>→ <a href="/admin/">进入后台管理</a></p>
        <p>→ <a href="/">查看前台</a></p>
    </div>

<?php endif; ?>

</div>
</body>
</html>
