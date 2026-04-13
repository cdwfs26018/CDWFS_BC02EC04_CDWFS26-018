<?php
// src/OpenApi/OpenApiFactory.php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $pathItems = $openApi->getPaths();

        // ═══════════════════════════════════════════════
        //  ADMIN
        // ═══════════════════════════════════════════════

        // POST /api/admin/users
        $pathItems->addPath('/api/admin/users', new Model\PathItem(
            post: new Model\Operation(
                operationId: 'adminCreateUser',
                tags: ['Admin'],
                summary: 'Créer un utilisateur (admin)',
                security: [['JWT' => []]],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['email', 'password', 'role'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'user@example.com'],
                                    'password' => ['type' => 'string', 'example' => 'motdepasse123'],
                                    'role' => ['type' => 'string', 'enum' => ['ROLE_CLIENT', 'ROLE_CHAUFFEUR'], 'example' => 'ROLE_CHAUFFEUR'],
                                    'nom' => ['type' => 'string', 'example' => 'Dupont'],
                                    'prenom' => ['type' => 'string', 'example' => 'Jean'],
                                    'telephone' => ['type' => 'string', 'example' => '0612345678'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '201' => new Model\Response(description: 'Utilisateur créé'),
                    '400' => new Model\Response(description: 'Champs requis manquants ou rôle invalide'),
                    '409' => new Model\Response(description: 'Email déjà utilisé'),
                    '422' => new Model\Response(description: 'Erreur de validation'),
                ]
            )
        ));

        // PATCH /api/admin/users/{id}/role
        $pathItems->addPath('/api/admin/users/{id}/role', new Model\PathItem(
            patch: new Model\Operation(
                operationId: 'adminUpdateUserRole',
                tags: ['Admin'],
                summary: 'Modifier le rôle d\'un utilisateur',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['role'],
                                'properties' => [
                                    'role' => ['type' => 'string', 'enum' => ['ROLE_CLIENT', 'ROLE_CHAUFFEUR'], 'example' => 'ROLE_CLIENT'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Rôle mis à jour'),
                    '400' => new Model\Response(description: 'Rôle invalide'),
                ]
            )
        ));

        // DELETE /api/admin/users/{id}
        $pathItems->addPath('/api/admin/users/{id}', new Model\PathItem(
            delete: new Model\Operation(
                operationId: 'adminDeleteUser',
                tags: ['Admin'],
                summary: 'Supprimer un utilisateur',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Utilisateur supprimé'),
                    '400' => new Model\Response(description: 'Impossible de supprimer un admin ou soi-même'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  ADRESSES
        // ═══════════════════════════════════════════════

        // GET + POST /api/adresses
        $pathItems->addPath('/api/adresses', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'adresseList',
                tags: ['Adresses'],
                summary: 'Lister toutes les adresses',
                security: [['JWT' => []]],
                responses: [
                    '200' => new Model\Response(description: 'Liste des adresses'),
                ]
            ),
            post: new Model\Operation(
                operationId: 'adresseCreate',
                tags: ['Adresses'],
                summary: 'Créer une adresse',
                security: [['JWT' => []]],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['rue', 'ville', 'code_postal', 'pays'],
                                'properties' => [
                                    'rue' => ['type' => 'string', 'example' => '12 rue de la Paix'],
                                    'ville' => ['type' => 'string', 'example' => 'Paris'],
                                    'code_postal' => ['type' => 'string', 'example' => '75001'],
                                    'pays' => ['type' => 'string', 'example' => 'France'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '201' => new Model\Response(description: 'Adresse créée'),
                    '400' => new Model\Response(description: 'JSON invalide'),
                    '422' => new Model\Response(description: 'Erreur de validation'),
                ]
            )
        ));

        // GET + PUT + DELETE /api/adresses/{id}
        $pathItems->addPath('/api/adresses/{id}', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'adresseShow',
                tags: ['Adresses'],
                summary: 'Voir une adresse',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Détail de l\'adresse'),
                ]
            ),
            put: new Model\Operation(
                operationId: 'adresseUpdate',
                tags: ['Adresses'],
                summary: 'Modifier une adresse',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'properties' => [
                                    'rue' => ['type' => 'string', 'example' => '12 rue de la Paix'],
                                    'ville' => ['type' => 'string', 'example' => 'Paris'],
                                    'code_postal' => ['type' => 'string', 'example' => '75001'],
                                    'pays' => ['type' => 'string', 'example' => 'France'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Adresse mise à jour'),
                    '400' => new Model\Response(description: 'JSON invalide'),
                    '422' => new Model\Response(description: 'Erreur de validation'),
                ]
            ),
            delete: new Model\Operation(
                operationId: 'adresseDelete',
                tags: ['Adresses'],
                summary: 'Supprimer une adresse',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Adresse supprimée'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  AUTH
        // ═══════════════════════════════════════════════

        // POST /api/login
        $pathItems->addPath('/api/login', new Model\PathItem(
            post: new Model\Operation(
                operationId: 'authLogin',
                tags: ['Auth'],
                summary: 'Connexion (obtenir un JWT)',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['email', 'password'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'admin@mail.com'],
                                    'password' => ['type' => 'string', 'example' => 'password123'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Token JWT retourné'),
                    '401' => new Model\Response(description: 'Identifiants invalides'),
                ]
            )
        ));

        // GET /api/me
        $pathItems->addPath('/api/me', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'authMe',
                tags: ['Auth'],
                summary: 'Profil de l\'utilisateur connecté',
                security: [['JWT' => []]],
                responses: [
                    '200' => new Model\Response(description: 'Profil utilisateur'),
                ]
            )
        ));

        // PUT /api/change-password
        $pathItems->addPath('/api/change-password', new Model\PathItem(
            put: new Model\Operation(
                operationId: 'authChangePassword',
                tags: ['Auth'],
                summary: 'Changer son mot de passe',
                security: [['JWT' => []]],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['current_password', 'new_password'],
                                'properties' => [
                                    'current_password' => ['type' => 'string', 'example' => 'ancien123'],
                                    'new_password' => ['type' => 'string', 'example' => 'nouveau456'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Mot de passe modifié'),
                    '400' => new Model\Response(description: 'Champs requis manquants'),
                    '401' => new Model\Response(description: 'Mot de passe actuel incorrect'),
                    '422' => new Model\Response(description: 'Nouveau mot de passe trop court'),
                ]
            )
        ));

        // DELETE /api/delete-account
        $pathItems->addPath('/api/delete-account', new Model\PathItem(
            delete: new Model\Operation(
                operationId: 'authDeleteAccount',
                tags: ['Auth'],
                summary: 'Supprimer son compte',
                security: [['JWT' => []]],
                responses: [
                    '200' => new Model\Response(description: 'Compte supprimé'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  CLIENTS
        // ═══════════════════════════════════════════════

        // GET /api/clients/{id}
        $pathItems->addPath('/api/clients/{id}', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'clientShow',
                tags: ['Clients'],
                summary: 'Consulter un client',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Détail du client'),
                    '403' => new Model\Response(description: 'Accès refusé'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  LIVRAISONS
        // ═══════════════════════════════════════════════

        // POST /api/livraisons
        $pathItems->addPath('/api/livraisons', new Model\PathItem(
            post: new Model\Operation(
                operationId: 'livraisonCreate',
                tags: ['Livraisons'],
                summary: 'Créer une livraison',
                security: [['JWT' => []]],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['tournee_id', 'client_id', 'adresse_id', 'heure_prevue'],
                                'properties' => [
                                    'tournee_id' => ['type' => 'string', 'example' => 'uuid-tournee'],
                                    'client_id' => ['type' => 'string', 'example' => 'uuid-client'],
                                    'adresse_id' => ['type' => 'string', 'example' => 'uuid-adresse'],
                                    'heure_prevue' => ['type' => 'string', 'example' => '2025-01-15 08:30:00'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '201' => new Model\Response(description: 'Livraison créée'),
                    '400' => new Model\Response(description: 'JSON invalide ou champs manquants'),
                    '404' => new Model\Response(description: 'Tournee, client ou adresse invalide'),
                ]
            )
        ));

        // PATCH /api/livraisons/{id}/statut
        $pathItems->addPath('/api/livraisons/{id}/statut', new Model\PathItem(
            patch: new Model\Operation(
                operationId: 'livraisonUpdateStatut',
                tags: ['Livraisons'],
                summary: 'Modifier le statut d\'une livraison',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['statut'],
                                'properties' => [
                                    'statut' => ['type' => 'string', 'example' => 'livree'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Statut mis à jour'),
                    '400' => new Model\Response(description: 'Statut invalide ou JSON invalide'),
                ]
            )
        ));

        // PATCH /api/livraisons/{id}/statut
        $pathItems->addPath('/api/livraisons/{id}/statut', new Model\PathItem(
            patch: new Model\Operation(
                operationId: 'livraisonUpdateStatut',
                tags: ['Livraisons'],
                summary: 'Modifier le statut d\'une livraison',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'required' => ['statut'],
                                'properties' => [
                                    'statut' => [
                                        'type' => 'string',
                                        'enum' => ['en_attente', 'en_cours', 'livree', 'echouee'],
                                        'example' => 'livree',
                                    ],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '200' => new Model\Response(description: 'Statut mis à jour'),
                    '400' => new Model\Response(description: 'Statut invalide'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  MARCHANDISES
        // ═══════════════════════════════════════════════

        // GET /api/marchandises
        $pathItems->addPath('/api/marchandises', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'marchandiseList',
                tags: ['Marchandises'],
                summary: 'Lister toutes les marchandises',
                security: [['JWT' => []]],
                responses: [
                    '200' => new Model\Response(description: 'Liste des marchandises'),
                ]
            )
        ));

        // ═══════════════════════════════════════════════
        //  TOURNÉES
        // ═══════════════════════════════════════════════

        // GET /api/me/tournees
        $pathItems->addPath('/api/me/tournees', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'meTournees',
                tags: ['Tournées'],
                summary: 'Mes tournées (chauffeur connecté)',
                security: [['JWT' => []]],
                responses: [
                    '200' => new Model\Response(description: 'Liste des tournées du chauffeur'),
                ]
            )
        ));

        // POST /api/tournees
        $pathItems->addPath('/api/tournees', new Model\PathItem(
            post: new Model\Operation(
                operationId: 'tourneeCreate',
                tags: ['Tournées'],
                summary: 'Créer une tournée',
                security: [['JWT' => []]],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => new Model\MediaType(
                            schema: new \ArrayObject([
                                'type' => 'object',
                                'properties' => [
                                    'chauffeur_id' => ['type' => 'string', 'description' => 'Requis si admin', 'example' => 'uuid-chauffeur'],
                                ],
                            ])
                        ),
                    ])
                ),
                responses: [
                    '201' => new Model\Response(description: 'Tournée créée'),
                    '400' => new Model\Response(description: 'chauffeur_id requis pour un admin'),
                    '404' => new Model\Response(description: 'Chauffeur introuvable'),
                ]
            )
        ));

        // GET /api/tournees/{id}/livraisons
        $pathItems->addPath('/api/tournees/{id}/livraisons', new Model\PathItem(
            get: new Model\Operation(
                operationId: 'tourneeLivraisons',
                tags: ['Tournées'],
                summary: 'Livraisons d\'une tournée',
                security: [['JWT' => []]],
                parameters: [
                    new Model\Parameter(name: 'id', in: 'path', required: true, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Liste des livraisons de la tournée'),
                ]
            )
        ));

        return $openApi;
    }
}
