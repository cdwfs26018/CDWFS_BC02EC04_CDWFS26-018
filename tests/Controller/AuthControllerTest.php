<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        $users = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $this->em->remove($user);
        }
        $this->em->flush();
    }

    // ══════════════════════════════════════
    //  REGISTER
    // ══════════════════════════════════════

    public function testRegisterSuccess(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0612345678',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Compte créé avec succès', $data['message']);
        $this->assertEquals('test@example.com', $data['user']['email']);
    }

    public function testRegisterDuplicateEmail(): void
    {
        // Premier enregistrement
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0612345678',
        ]));

        // Deuxième enregistrement avec le même email
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password456',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'telephone' => '0698765432',
        ]));

        $this->assertResponseStatusCodeSame(409);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Email déjà utilisé', $data['message']);
    }

    public function testRegisterMissingFields(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterInvalidRole(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'ROLE_ADMIN',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0612345678',
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Rôle invalide', $data['message']);
    }

    public function testRegisterInvalidJson(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid json');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterAsChauffeur(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'chauffeur@example.com',
            'password' => 'password123',
            'role' => 'ROLE_CHAUFFEUR',
            'nom' => 'Martin',
            'prenom' => 'Pierre',
            'telephone' => '0698765432',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('ROLE_CHAUFFEUR', $data['user']['roles']);
    }

    // ══════════════════════════════════════
    //  LOGIN
    // ══════════════════════════════════════

    public function testLoginSuccess(): void
    {
        // Créer un utilisateur d'abord
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'login@example.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '0612345678',
        ]));

        // Login
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginInvalidCredentials(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
