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
$title = $profile['title'] ?? 'QIU';
$subtitle = $profile['subtitle'] ?? '';
$bio = $profile['bio'] ?? '';
$statusText = $profile['status_text'] ?? 'SYSTEM STATUS: ONLINE';
$role = $profile['role'] ?? 'Student Developer';
$year = $profile['year'] ?? 'Freshman';
$location = $profile['location'] ?? 'China';
$navAbout = $profile['nav_about'] ?? '关于';
$navSkills = $profile['nav_skills'] ?? '技术栈';
$navProjects = $profile['nav_projects'] ?? '项目';
$navContact = $profile['nav_contact'] ?? '联系';
$heroBtnProjects = $profile['hero_btn_projects'] ?? '查看项目';
$heroBtnContact = $profile['hero_btn_contact'] ?? '建立连接';
$aboutTitle = $profile['about_title'] ?? '关于我';
$aboutTerminalFile = $profile['about_terminal_file'] ?? 'about.sh';
$aboutTerminalCmd = $profile['about_terminal_cmd'] ?? 'cat ./about.txt';
$skillsTitle = $profile['skills_title'] ?? '技术栈';
$skillsDesc = $profile['skills_desc'] ?? '';
$projectsTitle = $profile['projects_title'] ?? '项目';
$projectsDesc = $profile['projects_desc'] ?? '';
$contactTitle = $profile['contact_title'] ?? '建立连接';
$contactDesc = $profile['contact_desc'] ?? '';
$contactTerminalFile = $profile['contact_terminal_file'] ?? 'connect.sh';
$footerText = $profile['footer_text'] ?? 'Designed & Built by ' . $name;
$footerQuote = $profile['footer_quote'] ?? '';

// Load skills
$categories = $db->query('SELECT * FROM skill_categories ORDER BY sort_order')->fetchAll();
$skills = $db->query('SELECT * FROM skills ORDER BY sort_order')->fetchAll();

// Load projects
$projects = $db->query('SELECT * FROM projects WHERE visible = 1 ORDER BY sort_order')->fetchAll();

// Load contacts
$contacts = $db->query('SELECT * FROM contacts WHERE visible = 1 ORDER BY sort_order')->fetchAll();

// Load stats
$stats = $db->query('SELECT * FROM stats ORDER BY sort_order')->fetchAll();

// Load awards
$awards = $db->query('SELECT * FROM awards WHERE visible = 1 ORDER BY sort_order, date DESC')->fetchAll();

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

$colorMap = [
    'green' => 'accent-green',
    'magenta' => 'accent-magenta',
    'cyan' => 'accent-cyan',
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($name) ?> // SYSTEM ONLINE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Scanline overlay -->
  <div class="scanlines" aria-hidden="true"></div>

  <!-- Navigation -->
  <nav class="nav">
    <div class="nav-inner">
      <a href="#" class="nav-logo">
        <span class="logo-bracket">[</span><?= esc($name) ?><span class="logo-bracket">]</span>
      </a>
      <div class="nav-links">
        <a href="/about.php" class="nav-link"><?= esc($navAbout) ?></a>
        <a href="#skills" class="nav-link"><?= esc($navSkills) ?></a>
        <a href="/projects.php" class="nav-link"><?= esc($navProjects) ?></a>
        <a href="/contact.php" class="nav-link"><?= esc($navContact) ?></a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-grid-bg" aria-hidden="true"></div>
    <div class="hero-content">
      <p class="hero-status">
        <span class="status-dot"></span>
        <span class="typing"><?= esc($statusText) ?></span>
      </p>
      <h1 class="hero-title cyber-glitch" data-text="<?= esc($title) ?>"><?= esc($title) ?></h1>
      <p class="hero-subtitle">
        <span class="prefix">&gt;</span> <?= esc($subtitle) ?>
      </p>
      <p class="hero-desc"><?= esc($bio) ?></p>
      <div class="hero-actions">
        <a href="/projects.php" class="btn btn-primary"><?= esc($heroBtnProjects) ?></a>
        <a href="/contact.php" class="btn btn-secondary"><?= esc($heroBtnContact) ?></a>
      </div>
    </div>
    <div class="hero-hud" aria-hidden="true">
      <div class="hud-panel">
        <div class="hud-header">// IDENTITY.SYS</div>
        <div class="hud-line"><span class="hud-key">NAME:</span> <?= esc($name) ?></div>
        <div class="hud-line"><span class="hud-key">ROLE:</span> <?= esc($role) ?></div>
        <div class="hud-line"><span class="hud-key">YEAR:</span> <?= esc($year) ?></div>
        <div class="hud-line"><span class="hud-key">LOCATION:</span> <?= esc($location) ?></div>
        <div class="hud-line"><span class="hud-key">STATUS:</span> <span class="accent-green">ACTIVE</span></div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="section section-about">
    <div class="container">
      <h2 class="section-title">
        <span class="title-decoration">//</span> <?= esc($aboutTitle) ?>
      </h2>
      <div class="about-grid">
        <div class="card card-terminal">
          <div class="terminal-header">
            <span class="dot dot-red"></span>
            <span class="dot dot-yellow"></span>
            <span class="dot dot-green"></span>
            <span class="terminal-title"><?= esc($aboutTerminalFile) ?></span>
          </div>
          <div class="terminal-body">
            <p class="terminal-line"><span class="prefix">$</span> <?= esc($aboutTerminalCmd) ?></p>
            <p class="terminal-output"><?= nl2br(esc($bio)) ?></p>
            <p class="terminal-line"><span class="prefix">$</span> <span class="cursor">_</span></p>
          </div>
        </div>
        <?php if (!empty($stats)): ?>
        <div class="about-stats">
          <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
              <div class="stat-value <?= $colorMap[$stat['color']] ?? 'accent-green' ?>"><?= esc($stat['value']) ?></div>
              <div class="stat-label"><?= esc($stat['label']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Skills Section -->
  <section id="skills" class="section section-skills">
    <div class="container">
      <h2 class="section-title">
        <span class="title-decoration">//</span> <?= esc($skillsTitle) ?>
      </h2>
      <p class="section-desc">
        <span class="prefix">&gt;</span> <?= esc($skillsDesc) ?>
      </p>
      <div class="skills-grid">
        <?php foreach ($categories as $cat): ?>
        <div class="skill-category card card-holographic">
          <div class="corner-accent corner-tl"></div>
          <div class="corner-accent corner-tr"></div>
          <div class="corner-accent corner-bl"></div>
          <div class="corner-accent corner-br"></div>
          <h3 class="skill-category-title <?= $colorMap[$cat['color']] ?? 'accent-green' ?>"><?= esc($cat['name']) ?></h3>
          <ul class="skill-list">
            <?php
            $catSkills = array_filter($skills, fn($s) => $s['category_id'] == $cat['id']);
            foreach ($catSkills as $skill):
            ?>
              <li class="skill-item"><span class="skill-dot"></span><?= esc($skill['name']) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Projects Section (Preview) -->
  <section id="projects" class="section section-projects">
    <div class="container">
      <h2 class="section-title">
        <span class="title-decoration">//</span> <?= esc($projectsTitle) ?>
      </h2>
      <p class="section-desc">
        <span class="prefix">&gt;</span> <?= esc($projectsDesc) ?>
      </p>
      <div class="projects-grid">
        <?php foreach (array_slice($projects, 0, 3) as $p): ?>
        <a href="/projects.php?id=<?= $p['id'] ?>" class="card card-default project-card" style="text-decoration:none;color:inherit;">
          <div class="project-tag"><?= esc($p['tag']) ?></div>
          <h3 class="project-title"><?= esc($p['title']) ?></h3>
          <p class="project-desc"><?= esc($p['description']) ?></p>
          <div class="project-tech">
            <?php foreach (json_decode($p['techs'], true) ?: [] as $tech): ?>
              <span class="tech-tag"><?= esc($tech) ?></span>
            <?php endforeach; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php if (count($projects) > 3): ?>
      <div style="text-align:center;margin-top:40px;">
        <a href="/projects.php" class="btn btn-primary">查看全部项目 →</a>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Awards Section -->
  <?php if (!empty($awards)): ?>
  <section id="awards" class="section section-awards">
    <div class="container">
      <h2 class="section-title">
        <span class="title-decoration">//</span> 荣誉奖项
      </h2>
      <div class="awards-grid">
        <?php foreach (array_slice($awards, 0, 4) as $award): ?>
        <div class="card card-default award-card">
          <div class="award-card-date"><?= esc($award['date']) ?></div>
          <h3 class="award-card-title"><?= esc($award['title']) ?></h3>
          <?php if ($award['organizer'] || $award['level']): ?>
          <p class="award-card-meta"><?= esc($award['organizer']) ?><?= $award['level'] ? ' · ' . esc($award['level']) : '' ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($awards) > 4): ?>
      <div style="text-align:center;margin-top:40px;">
        <a href="/about.php#awards" class="btn btn-primary">查看全部 →</a>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Contact Section -->
  <section id="contact" class="section section-contact">
    <div class="container">
      <h2 class="section-title">
        <span class="title-decoration">//</span> <?= esc($contactTitle) ?>
      </h2>
      <p class="section-desc">
        <span class="prefix">&gt;</span> <?= esc($contactDesc) ?>
      </p>
      <div class="contact-index-grid">
        <?php foreach ($contacts as $c): ?>
        <a href="<?= esc($c['url']) ?>" class="contact-index-item" target="_blank" rel="noopener">
          <span class="contact-index-icon"><?= $c['icon'] ?></span>
          <span class="contact-index-label"><?= esc($c['label']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <p class="footer-text">
          <span class="prefix">&gt;</span> <?= esc($footerText) ?> // <?= date('Y') ?>
        </p>
        <p class="footer-quote">
          "<?= esc($footerQuote) ?>"
        </p>
      </div>
    </div>
  </footer>

</body>
</html>
