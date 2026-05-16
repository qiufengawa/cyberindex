<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

$db = getDb();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare('SELECT password_hash FROM admins WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!password_verify($current, $admin['password_hash'])) {
            setFlash('error', '当前密码错误');
        } elseif (strlen($new) < 8) {
            setFlash('error', '新密码至少 8 个字符');
        } elseif ($new !== $confirm) {
            setFlash('error', '两次密码不一致');
        } else {
            $hash = password_hash($new, PASSWORD_ARGON2ID);
            $stmt = $db->prepare('UPDATE admins SET password_hash = :h, updated_at = datetime("now") WHERE id = :id');
            $stmt->execute([':h' => $hash, ':id' => $_SESSION['admin_id']]);
            setFlash('success', '密码已更新');
        }
        redirect('/admin/settings.php');
    }
}

// Load login logs
$logs = $db->query('SELECT * FROM login_logs ORDER BY created_at DESC LIMIT 20')->fetchAll();

require_once __DIR__ . '/layout.php';
adminHeader('系统设置', 'settings');
?>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">修改密码</h2>
        <form method="POST" action="?action=password" style="max-width:400px;margin-bottom:48px;">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="current_password">当前密码</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">新密码 (至少8位)</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">确认新密码</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">更新密码</button>
            </div>
        </form>

        <h2 style="font-size:0.9rem;color:var(--accent);margin-bottom:12px;text-transform:uppercase;letter-spacing:0.1em;">登录日志 (最近20条)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>IP</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><?= e($log['ip']) ?></td>
                    <td style="color:<?= $log['success'] ? 'var(--accent)' : 'var(--destructive)' ?>">
                        <?= $log['success'] ? '成功' : '失败' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <tr><td colspan="3" style="color:var(--muted-fg)">暂无记录</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
<?php adminFooter(); ?>
