<?php
function adminHeader(string $pageTitle, string $activePage = ''): void {
    $db = getDb();
    $projectCount = (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $skillCount = (int)$db->query('SELECT COUNT(*) FROM skills')->fetchColumn();
    $awardCount = (int)$db->query('SELECT COUNT(*) FROM awards')->fetchColumn();
    $contactCount = (int)$db->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

    $menu = [
        ['url' => '/admin/', 'label' => '仪表盘', 'key' => 'dashboard'],
        ['url' => '/admin/profile.php', 'label' => '个人资料', 'key' => 'profile'],
        ['url' => '/admin/skills.php', 'label' => '技术栈', 'key' => 'skills'],
        ['url' => '/admin/projects.php', 'label' => '项目', 'key' => 'projects'],
        ['url' => '/admin/awards.php', 'label' => '荣誉奖项', 'key' => 'awards'],
        ['url' => '/admin/contact.php', 'label' => '联系方式', 'key' => 'contact'],
        ['url' => '/admin/settings.php', 'label' => '系统设置', 'key' => 'settings'],
    ];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> // QIU ADMIN</title>
    <link rel="stylesheet" href="/admin/assets/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <nav class="admin-nav">
        <div class="nav-brand">[QIU] <span class="nav-sub">ADMIN</span></div>
        <ul class="nav-menu">
            <?php foreach ($menu as $item): ?>
            <li><a href="<?= $item['url'] ?>"<?= $activePage === $item['key'] ? ' class="active"' : '' ?>><?= $item['label'] ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="nav-footer">
            <a href="/" target="_blank">查看前台 ↗</a>
            <a href="/admin/?action=logout">退出登录</a>
        </div>
    </nav>
    <main class="admin-main">
        <h1 class="page-title">// <?= e($pageTitle) ?></h1>
        <?php
        $flash = getFlash();
        if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
        <?php endif;
}

function adminFooter(): void {
?>
    </main>
</div>
</body>
</html>
<?php
}
