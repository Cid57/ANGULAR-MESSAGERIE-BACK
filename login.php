<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'header-init.php';
include 'jwt-helper.php';

// Transformer le JSON en objet PHP contenant les informations de l'utilisateur
$json = file_get_contents('php://input');

// Le convertir en objet PHP
$utilisateur = json_decode($json);

// Vérifier que l'utilisateur existe dans la base de données
$requete = $connexion->prepare("SELECT u.id, u.email, u.firstname, u.lastname, u.password, r.name as role 
                                FROM user as u
                                JOIN role as r ON u.id_role = r.id
                                WHERE email = :email");

$requete->execute([
    "email" => $utilisateur->email
]);

$utilisateurBdd = $requete->fetch();

if (!$utilisateurBdd) {
    http_response_code(401); // Utiliser 401 Unauthorized
    echo json_encode(["message" => "email ou mot de passe incorrect"]);
    exit();
}

// Vérifier si le mot de passe en clair de l'utilisateur est compatible avec le mot de passe hashé en BDD
if (!password_verify($utilisateur->password, $utilisateurBdd['password'])) {
    http_response_code(401); // Utiliser 401 Unauthorized
    echo json_encode(["message" => "email ou mot de passe incorrect"]);
    exit();
}

$jwt = generateJwt($utilisateurBdd);

echo json_encode(["jwt" => $jwt]);
