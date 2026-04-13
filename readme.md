===============================
📦 API LIVRAISON - DOCUMENTATION
===============================

📍 URL DE BASE
--------------
http://localhost:8000

Toutes les routes API sont préfixées par :
http://localhost:8000/api


===============================
📘 DOCUMENTATION API (SWAGGER)
===============================

Interface Swagger (API Platform) :
➡️ http://localhost:8000/api/docs

Permet de :
- Visualiser toutes les routes
- Tester les endpoints directement
- Voir les formats JSON attendus

🔐 AUTHENTIFICATION DANS SWAGGER

Cliquer sur "Authorize" puis entrer :

Bearer VOTRE_TOKEN_JWT


===============================
🔐 AUTHENTIFICATION
===============================

POST /api/login

Body JSON :
{
"email": "admin@mail.com",
"password": "password"
}

Réponse :
{
"id": "uuid",
"email": "...",
"roles": [...],
"token": "JWT"
}

➡️ Le token doit être utilisé dans les requêtes suivantes :
Header :
Authorization: Bearer TOKEN


===============================
👤 ADMIN
===============================

Créer un utilisateur :
POST /api/admin/users

{
"email": "user@mail.com",
"password": "password123",
"role": "ROLE_CLIENT",
"nom": "Dupont",
"prenom": "Jean",
"telephone": "0600000000"
}

Modifier rôle :
PATCH /api/admin/users/{id}/role

{
"role": "ROLE_CHAUFFEUR"
}

Supprimer utilisateur :
DELETE /api/admin/users/{id}


===============================
🚚 TOURNÉES
===============================

Voir ses tournées :
GET /api/me/tournees

Créer une tournée :
POST /api/tournees

(Admin peut préciser chauffeur_id)


===============================
📦 LIVRAISONS
===============================

Créer une livraison :
POST /api/livraisons

{
"tournee_id": "uuid",
"client_id": "uuid",
"adresse_id": "uuid",
"heure_prevue": "2026-04-15 14:30"
}

Modifier statut :
PATCH /api/livraisons/{id}/statut

{
"statut": "EN_COURS"
}


===============================
🏠 ADRESSES
===============================

GET /api/adresses
POST /api/adresses
PUT /api/adresses/{id}
DELETE /api/adresses/{id}


===============================
📦 MARCHANDISES
===============================

GET /api/marchandises


===============================
⚠️ IMPORTANT
===============================

- Les IDs sont des UUID
- Ils doivent être envoyés au format string :
  "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"

- Le backend utilise des UUID binaires → conversion automatique côté Symfony

- Toutes les routes /api (sauf login/register/docs) nécessitent un token JWT


===============================
🛠️ TECH STACK
===============================

- Symfony 7
- API Platform (Swagger)
- Doctrine ORM
- JWT (LexikJWTAuthenticationBundle)
- MySQL (UUID binaire)


===============================
✅ CONSEILS
===============================

- Toujours tester avec Swagger
- Vérifier le token JWT
- Vérifier les UUID envoyés
- Utiliser les bons rôles (ADMIN, CHAUFFEUR, CLIENT)
  ===============================
  👑 CRÉATION D’UN ADMIN
  ===============================

Pour créer le premier utilisateur administrateur, une commande Symfony est disponible.

📌 Commande :

php bin/console app:create-admin

Ensuite, suivre les instructions dans le terminal :

- Email
- Mot de passe

➡️ Le compte sera automatiquement créé avec le rôle :
ROLE_ADMIN

===============================
⚠️ IMPORTANT
===============================

- Aucun admin n'est créé par défaut
- Cette commande doit être utilisée une seule fois au début
- L'admin peut ensuite créer d'autres utilisateurs via l'API

===============================
🧪 UTILISATION
===============================

1. Créer un admin via la commande
2. Se connecter avec :

POST /api/login

3. Récupérer le token JWT
4. Utiliser ce token dans Swagger ou Postman

Authorization: Bearer VOTRE_TOKEN

5. Accéder aux routes /api/admin/*

