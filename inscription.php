<?php
require 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$titrePage = 'Créer un compte';
$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Vérifier les informations saisies.
    if ($email === '' || $password === '') {
        $erreur = 'Veuillez renseigner votre email et votre mot de passe.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez indiquer un email valide.';
    } else {
        // Un compte avec cet email existe-t-il déjà ?
        $sth = $dbh->prepare('SELECT id FROM user WHERE email = :email');
        $sth->execute(['email' => $email]);

        if ($sth->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            try {
                // Toujours enregistrer un mot de passe hashé.
                $sth = $dbh->prepare('INSERT INTO user (email, password, role) VALUES (:email, :password, :role)');
                $sth->execute([
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'user'
                ]);
                message('Compte créé ! Vous pouvez vous connecter.');
                header('Location: connexion.php');
                exit;
            } catch (PDOException $e) {
                $erreur = 'Impossible de créer le compte. Cet email est peut-être déjà utilisé.';
            }
        }
    }
}
require 'header.php';
?>
<div class="narrow"><div class="card">
    <h1>Créer un compte</h1>
    <?php if ($erreur): ?><div class="flash flash-error"><?= e($erreur) ?></div><?php endif; ?>
    <form class="form" method="post">
        <div><label for="email" class="required">Email</label><input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required></div>
        <div><label for="password" class="required">Mot de passe</label><input type="password" id="password" name="password" autocomplete="new-password" required></div>
        <button type="submit">Créer mon compte</button>
    </form>
    <p class="form-footer">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
</div></div>
<?php require 'footer.php'; ?>
