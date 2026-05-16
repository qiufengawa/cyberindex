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

// Get project by ID or list all
$projectId = (int)($_GET['id'] ?? 0);

function esc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

if ($projectId > 0) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = :id AND visible = 1');
    $stmt->execute([':id' => $projectId]);
    $project = $stmt->fetch();
    if (!$project) { http_response_code(404); exit('Project not found.'); }
} else {
    $project = null;
}

$projects = $db->query('SELECT * FROM projects WHERE visible = 1 ORDER BY sort_order')->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $project ? esc($project['title']) . ' // ' : '' ?>Projects // <?= esc($name) ?></title>
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
        <a href="/projects.php" class="nav-link active"><?= esc($navProjects) ?></a>
        <a href="/contact.php" class="nav-link"><?= esc($navContact) ?></a>
      </div>
    </div>
  </nav>

  <main class="page-content">
    <?php if ($project): ?>
    <!-- Single Project Detail -->
    <section class="page-hero">
      <div class="container">
        <p class="hero-status"><span class="status-dot"></span><span>// PROJECT DETAIL</span></p>
        <p class="project-tag" style="margin-bottom:8px;"><?= esc($project['tag']) ?></p>
        <h1 class="page-hero-title" style="font-size:clamp(2rem,6vw,4rem);"><?= esc($project['title']) ?></h1>
      </div>
    </section>
    <section class="section">
      <div class="container" style="max-width:800px;">
        <div class="project-detail-tech" style="margin-bottom:32px;">
          <?php foreach (json_decode($project['techs'], true) ?: [] as $tech): ?>
            <span class="tech-tag"><?= esc($tech) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="project-detail-desc">
          <p><?= nl2br(esc($project['description'])) ?></p>
        </div>
        <?php if (!empty($project['detail'])): ?>
        <div class="project-detail-body" style="margin-top:32px;">
          <?= nl2br(esc($project['detail'])) ?>
        </div>
        <?php endif; ?>
        <div class="project-detail-links" style="margin-top:40px;display:flex;gap:16px;">
          <?php if (!empty($project['source_url'])): ?>
            <a href="<?= esc($project['source_url']) ?>" class="btn btn-primary" target="_blank" rel="noopener">源码 →</a>
          <?php endif; ?>
          <?php if (!empty($project['demo_url'])): ?>
            <a href="<?= esc($project['demo_url']) ?>" class="btn btn-secondary" target="_blank" rel="noopener">演示 →</a>
          <?php endif; ?>
        </div>
        <a href="/projects.php" class="back-link" style="display:inline-block;margin-top:40px;color:var(--muted-fg);font-size:0.85rem;text-decoration:none;">← 返回项目列表</a>
      </div>
    </section>

    <?php else: ?>
    <!-- Projects List -->
    <section class="page-hero">
      <div class="container">
        <p class="hero-status"><span class="status-dot"></span><span>// PROJECTS</span></p>
        <h1 class="page-hero-title">项目经历</h1>
        <p class="page-hero-sub"><?= esc($profile['projects_desc'] ?? '') ?></p>
      </div>
    </section>
    <section class="section">
      <div class="container">
        <div class="projects-grid">
          <?php foreach ($projects as $p): ?>
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
        <?php if (empty($projects)): ?>
          <p style="color:var(--muted-fg);font-size:0.9rem;">暂无项目展示。</p>
        <?php endif; ?>
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
