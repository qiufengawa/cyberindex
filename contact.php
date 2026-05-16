<?php
declare(strict_types=1);

require_once __DIR__ . '/core/config.php';
require_once ROOT_PATH . '/core/db.php';

$db = getDb();

$stmt = $db->query('SELECT key, value FROM profile');
$profile = [];
while ($row = $stmt->fetch()) {
    $profile[$row['key']] = $row['value'];
}

$name = $profile['name'] ?? 'QIU';
$navAbout = $profile['nav_about'] ?? '关于';
$navSkills = $profile['nav_skills'] ?? '技术栈';
$navProjects = $profile['nav_projects'] ?? '项目';
$navContact = $profile['nav_contact'] ?? '联系';
$contactTitle = $profile['contact_title'] ?? '建立连接';
$contactDesc = $profile['contact_desc'] ?? '';

$contacts = $db->query('SELECT * FROM contacts WHERE visible = 1 ORDER BY sort_order')->fetchAll();

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact // <?= esc($name) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="scanlines" aria-hidden="true"></div>

  <nav class="nav">
    <div class="nav-inner">
      <a href="/" class="nav-logo"><span class="logo-bracket">[</span><?= esc($name) ?><span class="logo-bracket">]</span></a>
      <div class="nav-links">
        <a href="/about.php" class="nav-link"><?= esc($navAbout) ?></a>
        <a href="/#skills" class="nav-link"><?= esc($navSkills) ?></a>
        <a href="/projects.php" class="nav-link"><?= esc($navProjects) ?></a>
        <a href="/contact.php" class="nav-link active"><?= esc($navContact) ?></a>
      </div>
    </div>
  </nav>

  <main class="page-content">
    <section class="page-hero">
      <div class="container">
        <p class="hero-status"><span class="status-dot"></span><span>// CONTACT</span></p>
        <h1 class="page-hero-title"><?= esc($contactTitle) ?></h1>
        <p class="page-hero-sub"><?= esc($contactDesc) ?></p>
      </div>
    </section>

    <section class="section">
      <div class="container" style="max-width:700px;">
        <div class="contact-grid">
          <?php foreach ($contacts as $c): ?>
          <a href="<?= esc($c['url']) ?>" class="contact-card card card-default" target="_blank" rel="noopener">
            <span class="contact-card-icon"><?= $c['icon'] ?></span>
            <span class="contact-card-label"><?= esc($c['label']) ?></span>
            <span class="contact-card-arrow">→</span>
          </a>
          <?php endforeach; ?>
        </div>
        <?php if (empty($contacts)): ?>
          <p style="color:var(--muted-fg);font-size:0.9rem;">暂无联系方式。</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <p class="footer-text"><span class="prefix">&gt;</span> <?= esc($profile['footer_text'] ?? '') ?> // <?= date('Y') ?></p>
      </div>
    </div>
  </footer>
</body>
</html>
