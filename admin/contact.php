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
        $icon = trim($_POST['icon'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $visible = isset($_POST['visible']) ? 1 : 0;

        if ($label !== '' && $url !== '') {
            if ($id > 0) {
                $stmt = $db->prepare('UPDATE contacts SET icon=:icon, label=:label, url=:url, visible=:vis WHERE id=:id');
                $stmt->execute([':icon' => $icon, ':label' => $label, ':url' => $url, ':vis' => $visible, ':id' => $id]);
            } else {
                $stmt = $db->prepare('INSERT INTO contacts (icon, label, url, visible, sort_order) VALUES (:icon, :label, :url, :vis, (SELECT COALESCE(MAX(sort_order),0)+1 FROM contacts))');
                $stmt->execute([':icon' => $icon, ':label' => $label, ':url' => $url, ':vis' => $visible]);
            }
            setFlash('success', '联系方式已保存');
        }
        redirect('/admin/contact.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM contacts WHERE id = :id')->execute([':id' => $id]);
        setFlash('success', '已删除');
        redirect('/admin/contact.php');
    }
}

$contacts = $db->query('SELECT * FROM contacts ORDER BY sort_order')->fetchAll();
$editContact = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare('SELECT * FROM contacts WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
    $editContact = $stmt->fetch();
}

require_once __DIR__ . '/layout.php';
adminHeader('联系方式', 'contact');
?>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">
            <?= $editContact ? '编辑' : '添加联系方式' ?>
        </h2>
        <form method="POST" action="?action=<?= $editContact ? 'edit' : 'add' ?>" style="margin-bottom:32px;">
            <?= csrfField() ?>
            <?php if ($editContact): ?>
                <input type="hidden" name="id" value="<?= $editContact['id'] ?>">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="icon">图标 (emoji)</label>
                    <input type="text" id="icon" name="icon" value="<?= e($editContact['icon'] ?? '') ?>" placeholder="📧" required>
                </div>
                <div class="form-group">
                    <label for="label">显示文字</label>
                    <input type="text" id="label" name="label" value="<?= e($editContact['label'] ?? '') ?>" placeholder="qiu@example.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="url">链接 URL</label>
                <input type="text" id="url" name="url" value="<?= e($editContact['url'] ?? '') ?>" placeholder="mailto:qiu@example.com" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="visible" <?= ($editContact['visible'] ?? 1) ? 'checked' : '' ?>> 前台可见</label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">保存</button>
                <?php if ($editContact): ?>
                    <a href="/admin/contact.php" class="btn btn-secondary">取消</a>
                <?php endif; ?>
            </div>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">当前列表</h2>
        <?php foreach ($contacts as $c): ?>
            <div class="item-card">
                <div class="item-card-info">
                    <div class="item-card-title"><?= e($c['icon']) ?> <?= e($c['label']) ?></div>
                    <div class="item-card-meta"><?= e($c['url']) ?></div>
                </div>
                <div class="item-card-actions">
                    <a href="?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm">编辑</a>
                    <form method="POST" action="?action=delete" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('确定删除？')">删除</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($contacts)): ?>
            <p style="color:var(--muted-fg);font-size:0.85rem;">暂无联系方式。</p>
        <?php endif; ?>
<?php adminFooter(); ?>
