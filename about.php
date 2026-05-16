<?php
declare(strict_types=1);

require_once __DIR__ . '/core/config.php';
require_once ROOT_PATH . '/core/db.php';

$db = getDb();

// Load profile
$stmt = $db->query('SELECT key, value FROM profile');
$profile = [];
while ($row = $stmt->fetch()) {
    $profile[$row['key']] = $row['value'];
}

$name = $profile['name'] ?? 'QIU';
$bio = $profile['bio'] ?? '';
$subtitle = $profile['subtitle'] ?? '';
$role = $profile['role'] ?? '';
$year = $profile['year'] ?? '';
$location = $profile['location'] ?? '';
$navAbout = $profile['nav_about'] ?? '关于';
$navSkills = $profile['nav_skills'] ?? '技术栈';
$navProjects = $profile['nav_projects'] ?? '项目';
$navContact = $profile['nav_contact'] ?? '联系';

// Load awards
$awards = $db->query('SELECT * FROM awards WHERE visible = 1 ORDER BY sort_order, date DESC')->fetchAll();

// Load skills
$categories = $db->query('SELECT * FROM skill_categories ORDER BY sort_order')->fetchAll();
$skills = $db->query('SELECT * FROM skills ORDER BY sort_order')->fetchAll();

// Load stats
$stats = $db->query('SELECT * FROM stats ORDER BY sort_order')->fetchAll();

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$colorMap = ['green' => 'accent-green', 'magenta' => 'accent-magenta', 'cyan' => 'accent-cyan'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About // <?= esc($name) ?></title>
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
        <a href="/about.php" class="nav-link active"><?= esc($navAbout) ?></a>
        <a href="/#skills" class="nav-link"><?= esc($navSkills) ?></a>
        <a href="/projects.php" class="nav-link"><?= esc($navProjects) ?></a>
        <a href="/contact.php" class="nav-link"><?= esc($navContact) ?></a>
      </div>
    </div>
  </nav>

  <main class="page-content">
    <!-- Hero -->
    <section class="page-hero">
      <div class="container">
        <p class="hero-status"><span class="status-dot"></span><span>// ABOUT</span></p>
        <h1 class="page-hero-title"><?= esc($name) ?></h1>
        <p class="page-hero-sub"><?= esc($subtitle) ?></p>
      </div>
    </section>

    <!-- Bio -->
    <section class="section">
      <div class="container">
        <div class="about-detail-grid">
          <div class="about-bio-block">
            <h2 class="section-title"><span class="title-decoration">//</span> 个人介绍</h2>
            <div class="bio-text"><?= nl2br(esc($bio)) ?></div>
          </div>
          <div class="about-info-panel">
            <div class="card card-default">
              <div class="info-row"><span class="info-key">NAME</span><span class="info-val"><?= esc($name) ?></span></div>
              <div class="info-row"><span class="info-key">ROLE</span><span class="info-val"><?= esc($role) ?></span></div>
              <div class="info-row"><span class="info-key">YEAR</span><span class="info-val"><?= esc($year) ?></span></div>
              <div class="info-row"><span class="info-key">LOCATION</span><span class="info-val"><?= esc($location) ?></span></div>
            </div>
            <?php if (!empty($stats)): ?>
            <div class="about-mini-stats">
              <?php foreach ($stats as $stat): ?>
              <div class="mini-stat">
                <span class="mini-stat-val <?= $colorMap[$stat['color']] ?? 'accent-green' ?>"><?= esc($stat['value']) ?></span>
                <span class="mini-stat-label"><?= esc($stat['label']) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Skills -->
    <section class="section">
      <div class="container">
        <h2 class="section-title"><span class="title-decoration">//</span> 技术能力</h2>
        <div class="skills-grid">
          <?php foreach ($categories as $cat): ?>
          <div class="skill-category card card-holographic">
            <div class="corner-accent corner-tl"></div>
            <div class="corner-accent corner-tr"></div>
            <div class="corner-accent corner-bl"></div>
            <div class="corner-accent corner-br"></div>
            <h3 class="skill-category-title <?= $colorMap[$cat['color']] ?? 'accent-green' ?>"><?= esc($cat['name']) ?></h3>
            <ul class="skill-list">
              <?php foreach (array_filter($skills, fn($s) => $s['category_id'] == $cat['id']) as $skill): ?>
              <li class="skill-item"><span class="skill-dot"></span><?= esc($skill['name']) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Awards -->
    <?php if (!empty($awards)): ?>
    <section class="section">
      <div class="container">
        <h2 class="section-title"><span class="title-decoration">//</span> 荣誉奖项</h2>
        <div class="awards-timeline">
          <?php foreach ($awards as $award): ?>
          <div class="award-item">
            <div class="award-dot"></div>
            <div class="award-content card card-default">
              <div class="award-header">
                <h3 class="award-title"><?= esc($award['title']) ?></h3>
                <?php if ($award['date']): ?>
                <span class="award-date"><?= esc($award['date']) ?></span>
                <?php endif; ?>
              </div>
              <?php if ($award['organizer'] || $award['level']): ?>
              <p class="award-meta">
                <?= esc($award['organizer']) ?><?= $award['level'] ? ' · ' . esc($award['level']) : '' ?>
              </p>
              <?php endif; ?>
              <?php if ($award['description']): ?>
              <p class="award-desc"><?= esc($award['description']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
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
