<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();
require_once __DIR__ . '/layout.php';

$db = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $fields = [
        'name', 'title', 'subtitle', 'bio', 'status_text',
        'role', 'year', 'location',
        'nav_about', 'nav_skills', 'nav_projects', 'nav_contact',
        'hero_btn_projects', 'hero_btn_contact',
        'about_title', 'about_terminal_file', 'about_terminal_cmd',
        'skills_title', 'skills_desc',
        'projects_title', 'projects_desc',
        'contact_title', 'contact_desc', 'contact_terminal_file',
        'footer_text', 'footer_quote',
    ];
    foreach ($fields as $field) {
        $value = trim($_POST[$field] ?? '');
        $stmt = $db->prepare('INSERT INTO profile (key, value, updated_at) VALUES (:k, :v, datetime("now"))
            ON CONFLICT(key) DO UPDATE SET value = :v, updated_at = datetime("now")');
        $stmt->execute([':k' => $field, ':v' => $value]);
    }
    setFlash('success', '已保存');
    redirect('/admin/profile.php');
}

$stmt = $db->query('SELECT key, value FROM profile');
$profile = [];
while ($row = $stmt->fetch()) {
    $profile[$row['key']] = $row['value'];
}

adminHeader('个人资料', 'profile');
?>

<form method="POST">
    <?= csrfField() ?>

    <div class="form-section">
        <h2 class="form-section-title">基本信息</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="name">显示名称</label>
                <input type="text" id="name" name="name" value="<?= e($profile['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="title">Hero 大标题</label>
                <input type="text" id="title" name="title" value="<?= e($profile['title'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="subtitle">副标题</label>
            <input type="text" id="subtitle" name="subtitle" value="<?= e($profile['subtitle'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="bio">个人简介</label>
            <textarea id="bio" name="bio" rows="4"><?= e($profile['bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="status_text">状态栏文字</label>
            <input type="text" id="status_text" name="status_text" value="<?= e($profile['status_text'] ?? '') ?>">
        </div>
    </div>

    <div class="form-section">
        <h2 class="form-section-title">HUD 面板</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="role">角色</label>
                <input type="text" id="role" name="role" value="<?= e($profile['role'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="year">年级</label>
                <input type="text" id="year" name="year" value="<?= e($profile['year'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="location">位置</label>
            <input type="text" id="location" name="location" value="<?= e($profile['location'] ?? '') ?>">
        </div>
    </div>

    <div class="form-section">
        <h2 class="form-section-title">导航 & 按钮</h2>
        <div class="form-row">
            <div class="form-group"><label>导航-关于</label><input type="text" name="nav_about" value="<?= e($profile['nav_about'] ?? '关于') ?>"></div>
            <div class="form-group"><label>导航-技术栈</label><input type="text" name="nav_skills" value="<?= e($profile['nav_skills'] ?? '技术栈') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>导航-项目</label><input type="text" name="nav_projects" value="<?= e($profile['nav_projects'] ?? '项目') ?>"></div>
            <div class="form-group"><label>导航-联系</label><input type="text" name="nav_contact" value="<?= e($profile['nav_contact'] ?? '联系') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>按钮-项目</label><input type="text" name="hero_btn_projects" value="<?= e($profile['hero_btn_projects'] ?? '查看项目') ?>"></div>
            <div class="form-group"><label>按钮-联系</label><input type="text" name="hero_btn_contact" value="<?= e($profile['hero_btn_contact'] ?? '建立连接') ?>"></div>
        </div>
    </div>

    <div class="form-section">
        <h2 class="form-section-title">板块文字</h2>
        <div class="form-row">
            <div class="form-group"><label>关于-标题</label><input type="text" name="about_title" value="<?= e($profile['about_title'] ?? '关于我') ?>"></div>
            <div class="form-group"><label>关于-终端文件名</label><input type="text" name="about_terminal_file" value="<?= e($profile['about_terminal_file'] ?? 'about.sh') ?>"></div>
        </div>
        <div class="form-group"><label>关于-终端命令</label><input type="text" name="about_terminal_cmd" value="<?= e($profile['about_terminal_cmd'] ?? 'cat ./about.txt') ?>"></div>
        <div class="form-row">
            <div class="form-group"><label>技术栈-标题</label><input type="text" name="skills_title" value="<?= e($profile['skills_title'] ?? '技术栈') ?>"></div>
            <div class="form-group"><label>技术栈-描述</label><input type="text" name="skills_desc" value="<?= e($profile['skills_desc'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>项目-标题</label><input type="text" name="projects_title" value="<?= e($profile['projects_title'] ?? '项目') ?>"></div>
            <div class="form-group"><label>项目-描述</label><input type="text" name="projects_desc" value="<?= e($profile['projects_desc'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>联系-标题</label><input type="text" name="contact_title" value="<?= e($profile['contact_title'] ?? '建立连接') ?>"></div>
            <div class="form-group"><label>联系-终端文件名</label><input type="text" name="contact_terminal_file" value="<?= e($profile['contact_terminal_file'] ?? 'connect.sh') ?>"></div>
        </div>
        <div class="form-group"><label>联系-描述</label><input type="text" name="contact_desc" value="<?= e($profile['contact_desc'] ?? '') ?>"></div>
    </div>

    <div class="form-section">
        <h2 class="form-section-title">页脚</h2>
        <div class="form-group"><label>页脚文字</label><input type="text" name="footer_text" value="<?= e($profile['footer_text'] ?? '') ?>"></div>
        <div class="form-group"><label>页脚引言</label><input type="text" name="footer_quote" value="<?= e($profile['footer_quote'] ?? '') ?>"></div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">保存所有更改</button>
    </div>
</form>

<?php adminFooter();
