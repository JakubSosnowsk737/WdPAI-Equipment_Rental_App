<?php
use App\Core\Session;
use App\Models\User;
$currentId = Session::userId();
ob_start();
?>
<section>
    <h2>Zarządzanie użytkownikami</h2>
    <table class="data-table">
        <thead>
        <tr><th>ID</th><th>E-mail</th><th>Imię i nazwisko</th><th>Rola</th><th>Akcje</th></tr>
        </thead>
        <tbody>
        <?php /** @var User[] $users */ foreach ($users as $u): ?>
            <?php $isSelf = ((int) $u->id === (int) $currentId); ?>
            <tr>
                <td><?= (int) $u->id ?></td>
                <td><?= htmlspecialchars($u->email, ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($u->fullName(), ENT_QUOTES) ?></td>
                <td>
                    <?php if ($isSelf): ?>
                        <span class="role-badge"><?= htmlspecialchars($u->role, ENT_QUOTES) ?></span>
                        <span class="muted">(Twoje konto)</span>
                    <?php else: ?>
                        <form method="post" action="/admin/users/<?= (int) $u->id ?>/role" style="display:inline">
                            <?= App\Core\Csrf::field() ?>
                            <select name="role">
                                <option value="klient"    <?= $u->role === 'klient'    ? 'selected' : '' ?>>klient</option>
                                <option value="pracownik" <?= $u->role === 'pracownik' ? 'selected' : '' ?>>pracownik</option>
                                <option value="admin"     <?= $u->role === 'admin'     ? 'selected' : '' ?>>admin</option>
                            </select>
                            <button class="btn-sm" type="submit">Zapisz</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isSelf): ?>
                        <span class="muted">—</span>
                    <?php else: ?>
                        <form method="post" action="/admin/users/<?= (int) $u->id ?>/delete"
                              onsubmit="return confirm('Usunąć użytkownika?')">
                            <?= App\Core\Csrf::field() ?>
                            <button class="btn-sm btn-danger" type="submit">Usuń</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php
$content = ob_get_clean();
$title = 'Użytkownicy';
require __DIR__ . '/../../layout.php';
