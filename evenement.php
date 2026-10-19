<?php
require 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sth = $dbh->prepare('SELECT * FROM event WHERE id = :id');
$sth->execute(['id' => $id]);
$event = $sth->fetch();
if (!$event) {
    http_response_code(404);
    $titrePage = 'Page introuvable';
    require 'header.php';
    ?>
    <div class="error-page">
        <p class="code">404</p>
        <h1>Cet événement n'existe pas</h1>
        <p class="muted">La page que vous cherchez a été supprimée ou n'a jamais existé.</p>
        <a class="btn" href="index.php">Retour aux événements</a>
    </div>
    <?php
    require 'footer.php';
    exit;
}

// Inscription/désinscription : une seule réservation par utilisateur.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit;
    }
    $userId = (int)$_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    if ($action === 'desinscrire') {
        $sth = $dbh->prepare('DELETE FROM reservation WHERE event_id = :event_id AND user_id = :user_id');
        $sth->execute(['event_id' => $id, 'user_id' => $userId]);
        message('Vous êtes désinscrit de cet événement.');
    } elseif ($action === 'inscrire') {
        // On bloque temporairement cet événement pendant la réservation.
        // Cela évite que deux personnes prennent la dernière place en même temps.
        try {
            $dbh->beginTransaction();
            $sth = $dbh->prepare('SELECT * FROM event WHERE id = :id FOR UPDATE');
            $sth->execute(['id' => $id]);
            $aReserver = $sth->fetch();
            $sth = $dbh->prepare('SELECT COUNT(*) FROM reservation WHERE event_id = :event_id');
            $sth->execute(['event_id' => $id]);
            $nb = (int)$sth->fetchColumn();
            $sth = $dbh->prepare('SELECT COUNT(*) FROM reservation WHERE event_id = :event_id AND user_id = :user_id');
            $sth->execute(['event_id' => $id, 'user_id' => $userId]);
            $deja = (int)$sth->fetchColumn();
            if (!$aReserver || !$aReserver['is_validated'] || strtotime($aReserver['date']) <= time()) {
                message('Cet événement n’est pas ouvert aux inscriptions.', 'error');
            } elseif ($deja > 0) {
                message('Vous êtes déjà inscrit à cet événement.', 'error');
            } elseif ($nb >= (int)$aReserver['places']) {
                message('Cet événement est complet.', 'error');
            } else {
                $sth = $dbh->prepare('INSERT INTO reservation (user_id, event_id) VALUES (:user_id, :event_id)');
                $sth->execute(['user_id' => $userId, 'event_id' => $id]);
                message('Votre inscription est confirmée !');
            }
            // Enregistrer définitivement la réservation.
            $dbh->commit();
        } catch (PDOException $e) {
            // En cas de problème, annuler la réservation en cours.
            if ($dbh->inTransaction()) $dbh->rollBack();
            message('Impossible de réserver. Veuillez réessayer.', 'error');
        }
    }
    header('Location: evenement.php?id=' . $id);
    exit;
}

$sth = $dbh->prepare('SELECT COUNT(*) FROM reservation WHERE event_id = :event_id');
$sth->execute(['event_id' => $id]);
$nbInscrits = (int)$sth->fetchColumn();
$restantes = max(0, (int)$event['places'] - $nbInscrits);
$pourcentage = (int)min(100, ($nbInscrits / max(1, (int)$event['places'])) * 100);
$estInscrit = false;
if (isset($_SESSION['user_id'])) {
    $sth = $dbh->prepare('SELECT COUNT(*) FROM reservation WHERE event_id = :event_id AND user_id = :user_id');
    $sth->execute(['event_id' => $id, 'user_id' => (int)$_SESSION['user_id']]);
    $estInscrit = (int)$sth->fetchColumn() > 0;
}
$titrePage = $event['title'];
require 'header.php';
?>
<a class="back-link" href="index.php">← Retour aux événements</a>
<article class="cat-<?= e($event['category']) ?>">
    <header class="detail-hero" style="background-image: url('<?= e(imageEvenement($event)) ?>')">
        <div class="detail-hero-content">
            <span class="badge"><?= e(categorieNom($event['category'])) ?></span>
            <h1><?= e($event['title']) ?></h1>
            <p><?= e(dateAgenda($event['date'])) ?></p>
        </div>
    </header>
    <div class="detail-grid">
        <section class="card">
            <h2>Informations</h2>
            <p><?= nl2br(e($event['information'])) ?></p>
        </section>
        <aside class="card booking">
            <p class="places"><strong><?= $restantes ?></strong> places restantes sur <?= (int)$event['places'] ?></p>
            <div class="progress"><span style="width: <?= $pourcentage ?>%"></span></div>
            <?php if ($estInscrit): ?>
                <p class="muted">Vous êtes inscrit à cet événement.</p>
                <form method="post"><input type="hidden" name="action" value="desinscrire"><button class="btn btn-secondary btn-block" type="submit">Se désinscrire</button></form>
            <?php elseif (!$event['is_validated'] || strtotime($event['date']) <= time()): ?>
                <p class="muted">Inscriptions non disponibles pour cet événement.</p>
            <?php elseif ($restantes === 0): ?>
                <p class="places"><span class="badge badge-full">Complet</span></p>
                <button class="btn btn-block" disabled>Complet</button>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-block" href="connexion.php">Connectez-vous pour vous inscrire</a>
            <?php else: ?>
                <form method="post"><input type="hidden" name="action" value="inscrire"><button class="btn btn-block" type="submit">S'inscrire</button></form>
            <?php endif; ?>
        </aside>
    </div>
</article>
<?php require 'footer.php'; ?>
