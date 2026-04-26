<?php
use App\Models\User;
ob_start();
?>
<section>
    <h2>Zarzadzanie uzytkownikami</h2>
    <table class="data-table">
        <thead>
        <tr><th>ID</th><th>Email</th><th>Imie i nazwisko</th><th>Rola</th><th>Akcje</th></tr>
        </thead>
        <tbody>
        <?php /** @var User[] $users */ foreach ($users as $u): ?>
            <tr>
                <td><?= (int) $u->id ?></td>
                <td><?= htmlspecialchars($u->email, ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($u->fullName(), ENT_QUOTES) ?></td>
                <td>
                    <form method="post" action="/admin/users/<?= (int) $u->id ?>/role" style="display:inline">
                        <select name="role">
                            <option value="klient"    <?= $u->role === 'klient'    ? 'selected' : '' ?>>klient</option>
                            <option value="pracownik" <?= $u->role === 'pracownik' ? 'selected' : '' ?>>pracownik</option>
                            <option value="admin"     <?= $u->role === 'admin'     ? 'selected' : '' ?>>admin</option>
                        </select>
                        <button class="btn-sm" type="submit">Zapisz</button>
                    </form>
                </td>
                <td>
                    <form method="post" action="/admin/users/<?= (int) $u->id ?>/delete"
                          onsubmit="return confirm('Usunac uzytkownika?')">
                        <button class="btn-sm btn-danger" type="submit">Usun</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php
$content = ob_get_clean();
$title = 'Uzytkownicy';
require __DIR__ . '/../../layout.php';
