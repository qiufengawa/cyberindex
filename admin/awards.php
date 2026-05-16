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
        $awardTitle = trim($_POST['title'] ?? '');
        $organizer = trim($_POST['organizer'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $visible = isset($_POST['visible']) ? 1 : 0;

        if ($awardTitle !== '') {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE awards SET title=:t, organizer=:o, level=:l, date=:d, description=:desc, visible=:vis WHERE id=:id');
                $stmt->execute([':t' => $awardTitle, ':o' => $organizer, ':l' => $level, ':d' => $date, ':desc' => $description, ':vis' => $visible, ':id' => $id]);
            } else {
                $stmt = $db->prepare('INSERT INTO awards (title, organizer, level, date, description, visible, sort_order) VALUES (:t, :o, :l, :d, :desc, :vis, (SELECT COALESCE(MAX(sort_order),0)+1 FROM awards))');
                $stmt->execute([':t' => $awardTitle, ':o' => $organizer, ':l' => $level, ':d' => $date, ':desc' => $description, ':vis' => $visible]);
            }
            setFlash('success', '已保存');
        }
        redirect('/admin/awards.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM awards WHERE id = :id')->execute([':id' => $id]);
        setFlash('success', '已删除');
        redirect('/admin/awards.php');
    }
}

$awards = $db->query('SELECT * FROM awards ORDER BY sort_order')->fetchAll();
$editAward = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare('SELECT * FROM awards WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
    $editAward = $stmt->fetch();
}

require_once __DIR__ . '/layout.php';
adminHeader('荣誉奖项', 'awards');
?>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">
            <?= $editAward ? '编辑奖项' : '添加奖项' ?>
        </h2>
        <form method="POST" action="?action=<?= $editAward ? 'edit' : 'add' ?>" style="margin-bottom:32px;">
            <?= csrfField() ?>
            <?php if ($editAward): ?>
                <input type="hidden" name="id" value="<?= $editAward['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="title">奖项名称</label>
                <input type="text" id="title" name="title" value="<?= e($editAward['title'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="organizer">主办方</label>
                    <input type="text" id="organizer" name="organizer" value="<?= e($editAward['organizer'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="level">级别 (如: 国家级一等奖)</label>
                    <input type="text" id="level" name="level" value="<?= e($editAward['level'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="date">获奖时间 (如: 2025-06)</label>
                <input type="text" id="date" name="date" value="<?= e($editAward['date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="description">描述 (可选)</label>
                <textarea id="description" name="description" rows="3"><?= e($editAward['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="visible" <?= ($editAward['visible'] ?? 1) ? 'checked' : '' ?>> 前台可见</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">保存</button>
                <?php if ($editAward): ?>
                    <a href="/admin/awards.php" class="btn btn-secondary">取消</a>
                <?php endif; ?>
            </div>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">奖项列表</h2>
        <?php foreach ($awards as $a): ?>
            <div class="item-card">
                <div class="item-card-info">
                    <div class="item-card-title"><?= e($a['title']) ?></div>
                    <div class="item-card-meta"><?= e($a['organizer']) ?><?= $a['level'] ? ' · ' . e($a['level']) : '' ?><?= $a['date'] ? ' · ' . e($a['date']) : '' ?></div>
                </div>
                <div class="item-card-actions">
                    <a href="?action=edit&id=<?= $a['id'] ?>" class="btn btn-sm">编辑</a>
                    <form method="POST" action="?action=delete" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($awards)): ?>
            <p style="color:var(--muted-fg);font-size:0.85rem;">暂无奖项。</p>
        <?php endif; ?>
<?php adminFooter(); ?>
