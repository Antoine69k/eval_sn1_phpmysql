<?php
require 'config.php';
$titrePage = 'Événements à venir';
$categories = ['concert', 'conference', 'atelier', 'sport'];
$categorie = $_GET['categorie'] ?? '';
if (!in_array($categorie, $categories, true)) {
    $categorie = '';
}

// Afficher seulement les événements validés et à venir.
$sql = 'SELECT * FROM event WHERE is_validated = 1 AND date > NOW()';

// Ajouter une condition uniquement si une catégorie a été choisie.
if ($categorie !== '') {
    $sql .= ' AND category = :category';
}
$sql .= ' ORDER BY date ASC';

$sth = $dbh->prepare($sql);
if ($categorie !== '') {
    $sth->execute(['category' => $categorie]);
} else {
    $sth->execute();
}
$evenements = $sth->fetchAll();
require 'header.php';
?>
<section class="hero" style="background-image: url('images/rock.jpg')">
    <div class="hero-content">
        <span class="hero-kicker">Agenda local</span>
        <h1>Les événements près de chez vous</h1>
        <p>Concerts, conférences, ateliers et sport : trouvez votre prochaine sortie ou proposez la vôtre.</p>
        <a class="btn" href="ajouter.php">+ Ajouter un événement</a>
    </div>
</section>
<div class="section-head">
    <h2>À venir</h2>
    <ul class="legend">
        <li><a class="<?= $categorie === '' ? 'active' : '' ?>" href="index.php">Toutes</a></li>
        <?php foreach ($categories as $cat): ?>
            <li class="cat-<?= e($cat) ?>"><a class="<?= $categorie === $cat ? 'active' : '' ?>" href="index.php?categorie=<?= e($cat) ?>"><?= e(categorieNom($cat)) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php if (count($evenements) === 0): ?>
    <p class="empty">Aucun événement à venir pour le moment.</p>
<?php else: ?>
    <ul class="event-list">
        <?php foreach ($evenements as $event): ?>
            <li class="event-card cat-<?= e($event['category']) ?>">
                <div class="event-media">
                    <img class="event-image" src="<?= e(imageEvenement($event)) ?>" alt="">
                    <div class="event-date"><span class="day"><?= e(date('d', strtotime($event['date']))) ?></span><span class="month"><?= e(moisAgenda($event['date'])) ?></span></div>
                    <span class="badge"><?= e(categorieNom($event['category'])) ?></span>
                </div>
                <h3 class="event-title"><a href="evenement.php?id=<?= (int)$event['id'] ?>"><?= e($event['title']) ?></a></h3>
                <span class="muted"><?= e(dateAgenda($event['date'])) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php require 'footer.php'; ?>
