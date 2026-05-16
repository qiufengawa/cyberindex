<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

$db = getDb();
$action = $_GET['action'] ?? 'list';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? 'green';
        if ($name !== '') {
            $stmt = $db->prepare('INSERT INTO skill_categories (name, color, sort_order) VALUES (:n, :c, (SELECT COALESCE(MAX(sort_order),0)+1 FROM skill_categories))');
            $stmt->execute([':n' => $name, ':c' => $color]);
            setFlash('success', '分类已添加');
        }
        redirect('/admin/skills.php');
    }

    if ($action === 'add_skill') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($name !== '' && $categoryId > 0) {
            $stmt = $db->prepare('INSERT INTO skills (category_id, name, sort_order) VALUES (:c, :n, (SELECT COALESCE(MAX(sort_order),0)+1 FROM skills WHERE category_id = :c2))');
            $stmt->execute([':c' => $categoryId, ':n' => $name, ':c2' => $categoryId]);
            setFlash('success', '技能已添加');
        }
        redirect('/admin/skills.php');
    }

    if ($action === 'delete_skill') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM skills WHERE id = :id')->execute([':id' => $id]);
        setFlash('success', '已删除');
        redirect('/admin/skills.php');
    }

    if ($action === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM skill_categories WHERE id = :id')->execute([':id' => $id]);
        setFlash('success', '分类已删除');
        redirect('/admin/skills.php');
    }
}

// Load data
$categories = $db->query('SELECT * FROM skill_categories ORDER BY sort_order')->fetchAll();
$skills = $db->query('SELECT s.*, sc.name as category_name FROM skills s JOIN skill_categories sc ON s.category_id = sc.id ORDER BY s.sort_order')->fetchAll();

require_once __DIR__ . '/layout.php';
adminHeader('技术栈管理', 'skills');
?>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">添加分类</h2>
        <form method="POST" action="?action=add_category" style="display:flex;gap:12px;margin-bottom:32px;align-items:flex-end;">
            <?= csrfField() ?>
            <div style="flex:1">
                <label for="cat_name">分类名称</label>
                <input type="text" id="cat_name" name="name" placeholder="如：前端开发" required>
            </div>
            <div>
                <label for="cat_color">颜色</label>
                <select id="cat_color" name="color">
                    <option value="green">绿色 (accent)</option>
                    <option value="magenta">品红 (secondary)</option>
                    <option value="cyan">青色 (tertiary)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm">添加</button>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">添加技能</h2>
        <form method="POST" action="?action=add_skill" style="display:flex;gap:12px;margin-bottom:32px;align-items:flex-end;">
            <?= csrfField() ?>
            <div>
                <label for="skill_cat">所属分类</label>
                <select id="skill_cat" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1">
                <label for="skill_name">技能名称</label>
                <input type="text" id="skill_name" name="name" placeholder="如：React / Next.js" required>
            </div>
            <button type="submit" class="btn btn-sm">添加</button>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">当前技能列表</h2>
        <?php foreach ($categories as $cat): ?>
            <div class="item-card" style="flex-direction:column;align-items:stretch;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="item-card-title" style="color:var(--accent-<?= $cat['color'] === 'magenta' ? 'secondary' : ($cat['color'] === 'cyan' ? 'tertiary' : '') ?>);"><?= e($cat['name']) ?></span>
                    <form method="POST" action="?action=delete_category" style="margin:0;">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('删除分类将同时删除其下所有技能，确定？')">删除分类</button>
                    </form>
                </div>
                <?php
                $catSkills = array_filter($skills, fn($s) => $s['category_id'] == $cat['id']);
                foreach ($catSkills as $skill):
                ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-top:1px solid var(--border);">
                        <span style="font-size:0.85rem;"><?= e($skill['name']) ?></span>
                        <form method="POST" action="?action=delete_skill" style="margin:0;">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $skill['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">×</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($catSkills)): ?>
                    <p style="font-size:0.8rem;color:var(--muted-fg);padding-top:8px;">暂无技能</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
<?php adminFooter(); ?>
