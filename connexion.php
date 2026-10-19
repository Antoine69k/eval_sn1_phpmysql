<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}
$titrePage = 'Connexion';
$erreur = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $erreur = 'Veuillez renseigner votre email et votre mot de passe.';
    } else {
        $sth = $dbh->prepare('SELECT * FROM user WHERE email = :email');
        $sth->execute(['email' => $email]);
        $user = $sth->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            message('Vous êtes connecté.');
            header('Location: index.php'); exit;
        }
        $erreur = 'Identifiants invalides.';
    }
}
require 'header.php';
?>
<div class="narrow"><div class="card">
    <h1>Connexion</h1>
    <?php if ($erreur): ?><div class="flash flash-error"><?= e($erreur) ?></div><?php endif; ?>
    <form class="form" method="post">
        <div><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required autofocus></div>
        <div><label for="password">Mot de passe</label><input type="password" id="password" name="password" autocomplete="current-password" required></div>
        <button type="submit">Se connecter</button>
    </form>
    <p class="form-footer">Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
</div></div>
<?php require 'footer.php'; ?>
