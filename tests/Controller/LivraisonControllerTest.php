<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class LivraisonControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;
    private ?string $clientToken = null;
    private ?string $chauffeurToken = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->cleanDatabase();
        $this->createUsers();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $tables = ['colis', 'livraison', 'chauffeur', 'client', 'user'];
        foreach ($tables as $table) {
            try {
                $connection->executeStatement("DELETE FROM `$table`");
            } catch (\Exception $e) {
            }
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        $this->em->clear();
    }

    private function createUsers(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'client@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'ClientNom',
            'prenom' => 'ClientPrenom',
            'telephone' => '0600000001',
        ]));

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'chauffeur@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CHAUFFEUR',
            'nom' => 'ChauffeurNom',
            'prenom' => 'ChauffeurPrenom',
            'telephone' => '0600000002',
        ]));

        $this->clientToken = $this->login('client@test.com', 'password123');
        $this->chauffeurToken = $this->login('chauffeur@test.com', 'password123');
    }

    private function login(string $email, string $password): string
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        return $data['token'];
    }

    private function authHeaders(string $token): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
    }

    private function createColis(): int
    {
        $this->client->request('POST', '/api/colis', [], [], $this->authHeaders($this->clientToken), json_encode([
            'description' => 'Colis livraison',
            'poids' => 3.0,
            'adresse_depart' => '1 rue A',
            'adresse_arrivee' => '2 rue B',
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);
        return $data['colis_id'];
    }

    // ══════════════════════════════════════
    //  CREATE LIVRAISON
    // ══════════════════════════════════════

    public function testCreateLivraisonSuccess(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Livraison créée', $data['message']);
    }

    public function testCreateLivraisonAsClient(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->clientToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateLivraisonColisNotFound(): void
    {
        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => 99999,
        ]));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateLivraisonUnauthenticated(): void
    {
        $this->client->request('POST', '/api/livraisons', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'colis_id' => 1,
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    // ══════════════════════════════════════
    //  GET MES LIVRAISONS
    // ══════════════════════════════════════

    public function testGetMesLivraisonsSuccess(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $this->client->request('GET', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function testGetMesLivraisonsUnauthenticated(): void
    {
        $this->client->request('GET', '/api/livraisons', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    // ══════════════════════════════════════
    //  GET LIVRAISON BY ID
    // ══════════════════════════════════════

    public function testGetLivraisonById(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('GET', '/api/livraisons/' . $livraisonId, [], [], $this->authHeaders($this->chauffeurToken));

        $this->assertResponseIsSuccessful();
    }

    public function testGetLivraisonNotFound(): void
    {
        $this->client->request('GET', '/api/livraisons/99999', [], [], $this->authHeaders($this->chauffeurToken));

        $this->assertResponseStatusCodeSame(404);
    }

    // ══════════════════════════════════════
    //  UPDATE STATUT LIVRAISON
    // ══════════════════════════════════════

    public function testUpdateStatutSuccess(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('PATCH', '/api/livraisons/' . $livraisonId . '/statut', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'statut' => 'en_cours',
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Statut mis à jour', $data['message']);
        $this->assertEquals('en_cours', $data['statut']);
    }

    public function testUpdateStatutToLivree(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('PATCH', '/api/livraisons/' . $livraisonId . '/statut', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'statut' => 'livree',
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('livree', $data['statut']);
    }

    public function testUpdateStatutInvalid(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('PATCH', '/api/livraisons/' . $livraisonId . '/statut', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'statut' => 'statut_invalide',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateStatutAsClient(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('PATCH', '/api/livraisons/' . $livraisonId . '/statut', [], [], $this->authHeaders($this->clientToken), json_encode([
            'statut' => 'en_cours',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateStatutUnauthenticated(): void
    {
        $this->client->request('PATCH', '/api/livraisons/1/statut', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'statut' => 'en_cours',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    // ══════════════════════════════════════
    //  DELETE LIVRAISON
    // ══════════════════════════════════════

    public function testDeleteLivraisonSuccess(): void
    {
        $colisId = $this->createColis();

        $this->client->request('POST', '/api/livraisons', [], [], $this->authHeaders($this->chauffeurToken), json_encode([
            'colis_id' => $colisId,
        ]));

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $livraisonId = $createData['livraison_id'];

        $this->client->request('DELETE', '/api/livraisons/' . $livraisonId, [], [], $this->authHeaders($this->chauffeurToken));

        $this->assertResponseIsSuccessful();
    }

    public function testDeleteLivraisonNotFound(): void
    {
        $this->client->request('DELETE', '/api/livraisons/99999', [], [], $this->authHeaders($this->chauffeurToken));

        $this->assertResponseStatusCodeSame(404);
    }
}
