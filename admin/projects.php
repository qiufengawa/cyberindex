<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

$db = getDb();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $tag = trim($_POST['tag'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $techs = trim($_POST['techs'] ?? '');
        $sourceUrl = trim($_POST['source_url'] ?? '');
        $demoUrl = trim($_POST['demo_url'] ?? '');
        $detail = trim($_POST['detail'] ?? '');
        $visible = isset($_POST['visible']) ? 1 : 0;

        $techsArray = array_map('trim', explode(',', $techs));
        $techsJson = json_encode(array_filter($techsArray), JSON_UNESCAPED_UNICODE);

        if ($title !== '' && $description !== '') {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE projects SET tag=:tag, title=:title, description=:desc, detail=:detail, techs=:techs, source_url=:src, demo_url=:demo, visible=:vis WHERE id=:id');
                $stmt->execute([':tag' => $tag, ':title' => $title, ':desc' => $description, ':detail' => $detail, ':techs' => $techsJson, ':src' => $sourceUrl, ':demo' => $demoUrl, ':vis' => $visible, ':id' => $id]);
            } else {
                $stmt = $db->prepare('INSERT INTO projects (tag, title, description, detail, techs, source_url, demo_url, visible, sort_order) VALUES (:tag, :title, :desc, :detail, :techs, :src, :demo, :vis, (SELECT COALESCE(MAX(sort_order),0)+1 FROM projects))');
                $stmt->execute([':tag' => $tag, ':title' => $title, ':desc' => $description, ':detail' => $detail, ':techs' => $techsJson, ':src' => $sourceUrl, ':demo' => $demoUrl, ':vis' => $visible]);
            }
            setFlash('success', '项目已保存');
        }
        redirect('/admin/projects.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM projects WHERE id = :id')->execute([':id' => $id]);
        setFlash('success', '项目已删除');
        redirect('/admin/projects.php');
    }
}

$projects = $db->query('SELECT * FROM projects ORDER BY sort_order')->fetchAll();
$editProject = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
    $editProject = $stmt->fetch();
}

require_once __DIR__ . '/layout.php';
adminHeader('项目管理', 'projects');
?>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">
            <?= $editProject ? '编辑项目' : '添加项目' ?>
        </h2>
        <form method="POST" action="?action=<?= $editProject ? 'edit' : 'add' ?>" style="margin-bottom:32px;">
            <?= csrfField() ?>
            <?php if ($editProject): ?>
                <input type="hidden" name="id" value="<?= $editProject['id'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="tag">标签 (如 WEB APP / TOOL / AI)</label>
                    <input type="text" id="tag" name="tag" value="<?= e($editProject['tag'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="title">项目名称</label>
                    <input type="text" id="title" name="title" value="<?= e($editProject['title'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="description">简短描述 (首页卡片展示)</label>
                <textarea id="description" name="description" required><?= e($editProject['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="detail">详细介绍 (项目详情页展示，可选)</label>
                <textarea id="detail" name="detail" rows="6"><?= e($editProject['detail'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="techs">技术标签 (逗号分隔)</label>
                <input type="text" id="techs" name="techs" value="<?= e($editProject ? implode(', ', json_decode($editProject['techs'], true) ?: []) : '') ?>" placeholder="React, TypeScript, Node.js">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="source_url">源码链接</label>
                    <input type="url" id="source_url" name="source_url" value="<?= e($editProject['source_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="demo_url">演示链接</label>
                    <input type="url" id="demo_url" name="demo_url" value="<?= e($editProject['demo_url'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="visible" <?= ($editProject['visible'] ?? 1) ? 'checked' : '' ?>> 前台可见</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">保存</button>
                <?php if ($editProject): ?>
                    <a href="/admin/projects.php" class="btn btn-secondary">取消</a>
                <?php endif; ?>
            </div>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">项目列表</h2>
        <?php foreach ($projects as $p): ?>
            <div class="item-card">
                <div class="item-card-info">
                    <div class="item-card-title"><?= e($p['title']) ?></div>
                    <div class="item-card-meta"><?= e($p['tag']) ?> · <?= $p['visible'] ? '可见' : '隐藏' ?></div>
                </div>
                <div class="item-card-actions">
                    <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm">编辑</a>
                    <form method="POST" action="?action=delete" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
            <p style="color:var(--muted-fg);font-size:0.85rem;">暂无项目，请添加。</p>
        <?php endif; ?>
<?php adminFooter(); ?>
