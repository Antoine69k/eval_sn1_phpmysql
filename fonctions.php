<?php
// Protéger le texte affiché dans une page HTML.
function e($texte) {
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

// Afficher une date sous la forme 19/10/2026 14h00.
function dateAgenda($date) {
    return date('d/m/Y H\hi', strtotime($date));
}

// Afficher le mois en français sur les cartes d'événement.
function moisAgenda($date) {
    $mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $numero = (int)date('n', strtotime($date));
    return $mois[$numero - 1];
}

// Transformer le nom enregistré en base en nom affiché.
function categorieNom($categorie) {
    $noms = [
        'concert' => 'Concert',
        'conference' => 'Conférence',
        'atelier' => 'Atelier',
        'sport' => 'Sport'
    ];
    return $noms[$categorie] ?? 'Autre';
}

// Si un événement n'a pas d'image, utiliser celle de sa catégorie.
function imageEvenement($event) {
    if (!empty($event['image'])) {
        return $event['image'];
    }
    $categorie = $event['category'];
    if (in_array($categorie, ['concert', 'conference', 'atelier', 'sport'], true)) {
        return 'images/' . $categorie . '.jpg';
    }
    return 'images/concert.jpg';
}

// Vérifier si l'utilisateur connecté est administrateur.
function estAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Garder un message en session pour l'afficher sur la page suivante.
function message($texte, $type = 'success') {
    $_SESSION['flash'] = $texte;
    $_SESSION['flash_type'] = $type;
}
