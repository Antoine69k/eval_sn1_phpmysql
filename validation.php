<?php
require 'config.php';
if (!estAdmin()) {
    header('Location: index.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $sth = $dbh->prepare('UPDATE event SET is_validated = 1 WHERE id = :id AND is_validated = 0');
    $sth->execute(['id' => $id]);
    message('Événement validé.');
    header('Location: validation.php'); exit;
}
$sth = $dbh->prepare('SELECT * FROM event WHERE is_validated = 0 ORDER BY date ASC');
$sth->execute();
$evenements = $sth->fetchAll();
$titrePage = 'Événements à valider';
require 'header.php';
?>
<div class="section-head"><div><h1>Événements à valider</h1><p class="muted"><?= count($evenements) ?> événement(s) en attente de validation.</p></div></div>
<?php if (count($evenements) === 0): ?>
    <p class="empty">Aucun événement en attente 🎉</p>
<?php else: ?>
    <div class="card table-wrap"><table class="table"><thead><tr><th>Événement</th><th>Date</th><th>Catégorie</th><th>Places</th><th>Statut</th><th></th></tr></thead><tbody>
    <?php foreach ($evenements as $event): ?>
        <tr>
            <td><a href="evenement.php?id=<?= (int)$event['id'] ?>"><?= e($event['title']) ?></a></td>
            <td><?= e(dateAgenda($event['date'])) ?></td>
            <td><span class="badge cat-<?= e($event['category']) ?>"><?= e(categorieNom($event['category'])) ?></span></td>
            <td><?= (int)$event['places'] ?></td>
            <td><span class="badge badge-pending">Non validé</span></td>
            <td><form method="post"><input type="hidden" name="id" value="<?= (int)$event['id'] ?>"><button type="submit" class="btn btn-small btn-success">Valider</button></form></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
<?php endif; ?>
<?php require 'footer.php'; ?>
