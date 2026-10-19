<?php
require 'config.php';
if (!estAdmin()) {
    header('Location: index.php'); exit;
}
$sth = $dbh->prepare('SELECT * FROM event WHERE is_validated = 1 ORDER BY date ASC');
$sth->execute();
$evenements = $sth->fetchAll();
$titrePage = 'Inscrits aux événements';
require 'header.php';
?>
<div class="section-head"><div><h1>Inscrits aux événements</h1><p class="muted">Liste des participants pour chaque événement validé.</p></div></div>
<?php if (count($evenements) === 0): ?>
    <p class="empty">Aucun événement validé pour le moment.</p>
<?php endif; ?>
<?php foreach ($evenements as $event): ?>
    <?php
    $sth = $dbh->prepare('SELECT user.email, reservation.created_at FROM reservation INNER JOIN user ON user.id = reservation.user_id WHERE reservation.event_id = :event_id ORDER BY reservation.created_at ASC');
    $sth->execute(['event_id' => $event['id']]);
    $inscrits = $sth->fetchAll();
    $nombre = count($inscrits);
    ?>
    <section class="card attendees"><div class="attendees-head">
        <div>
            <h2><a href="evenement.php?id=<?= (int)$event['id'] ?>"><?= e($event['title']) ?></a></h2>
            <span class="badge cat-<?= e($event['category']) ?>"><?= e(categorieNom($event['category'])) ?></span>
            <span class="muted"><?= e(dateAgenda($event['date'])) ?></span>
        </div>
        <p class="places">
            <?php if ($nombre >= (int)$event['places']): ?><span class="badge badge-full">Complet</span><?php endif; ?>
            <strong><?= $nombre ?></strong> inscrit(s) sur <?= (int)$event['places'] ?> places
        </p>
    </div>
    <?php if ($nombre === 0): ?>
        <p class="empty">Aucun inscrit pour le moment.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table"><thead><tr><th>Email</th><th>Inscrit le</th></tr></thead><tbody>
            <?php foreach ($inscrits as $inscrit): ?>
            <tr><td><?= e($inscrit['email']) ?></td><td><?= e(dateAgenda($inscrit['created_at'])) ?></td></tr>
            <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
    </section>
<?php endforeach; ?>
<?php require 'footer.php'; ?>
