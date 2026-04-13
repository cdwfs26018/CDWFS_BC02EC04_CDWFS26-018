<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;
    private ?string $adminToken = null;
    private ?string $clientToken = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->cleanDatabase();
        $this->createAdmin();
        $this->createClientUser();
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

    private function createAdmin(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($hasher->hashPassword($admin, 'admin123'));

        $this->em->persist($admin);
        $this->em->flush();

        $this->adminToken = $this->login('admin@test.com', 'admin123');
    }

    private function createClientUser(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'client@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Client',
            'prenom' => 'Test',
            'telephone' => '0600000001',
        ]));

        $this->clientToken = $this->login('client@test.com', 'password123');
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

    // ══════════════════════════════════════
    //  CREATE USER (ADMIN)
    // ══════════════════════════════════════

    public function testAdminCreateUserSuccess(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->adminToken), json_encode([
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CHAUFFEUR',
            'nom' => 'Nouveau',
            'prenom' => 'User',
            'telephone' => '0600000099',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur créé', $data['message']);
    }

    public function testAdminCreateUserDuplicateEmail(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->adminToken), json_encode([
            'email' => 'client@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Dup',
            'prenom' => 'User',
            'telephone' => '0600000099',
        ]));

        $this->assertResponseStatusCodeSame(409);
    }

    public function testAdminCreateUserInvalidRole(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->adminToken), json_encode([
            'email' => 'invalid@test.com',
            'password' => 'password123',
            'role' => 'ROLE_ADMIN',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testAdminCreateUserMissingFields(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->adminToken), json_encode([
            'email' => 'missing@test.com',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateUserAsNonAdmin(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->clientToken), json_encode([
            'email' => 'hack@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateUserUnauthenticated(): void
    {
        $this->client->request('POST', '/api/admin/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'noauth@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    // ══════════════════════════════════════
    //  UPDATE USER ROLE (ADMIN)
    // ══════════════════════════════════════

    public function testAdminUpdateRoleSuccess(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'client@test.com']);

        $this->client->request('PATCH', '/api/admin/users/' . $user->getId() . '/role', [], [], $this->authHeaders($this->adminToken), json_encode([
            'role' => 'ROLE_CHAUFFEUR',
        ]));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Rôle mis à jour', $data['message']);
        $this->assertContains('ROLE_CHAUFFEUR', $data['roles']);
    }

    public function testAdminUpdateRoleInvalid(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'client@test.com']);

        $this->client->request('PATCH', '/api/admin/users/' . $user->getId() . '/role', [], [], $this->authHeaders($this->adminToken), json_encode([
            'role' => 'ROLE_SUPER_ADMIN',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testUpdateRoleAsNonAdmin(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'client@test.com']);

        $this->client->request('PATCH', '/api/admin/users/' . $user->getId() . '/role', [], [], $this->authHeaders($this->clientToken), json_encode([
            'role' => 'ROLE_CHAUFFEUR',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    // ══════════════════════════════════════
    //  DELETE USER (ADMIN)
    // ══════════════════════════════════════

    public function testAdminDeleteUserSuccess(): void
    {
        // Créer un user à supprimer
        $this->client->request('POST', '/api/admin/users', [], [], $this->authHeaders($this->adminToken), json_encode([
            'email' => 'todelete@test.com',
            'password' => 'password123',
            'role' => 'ROLE_CLIENT',
            'nom' => 'Delete',
            'prenom' => 'Me',
            'telephone' => '0600000077',
        ]));

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'todelete@test.com']);

        $this->client->request('DELETE', '/api/admin/users/' . $user->getId(), [], [], $this->authHeaders($this->adminToken));

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur supprimé', $data['message']);
    }

    public function testAdminDeleteSelf(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);

        $this->client->request('DELETE', '/api/admin/users/' . $admin->getId(), [], [], $this->authHeaders($this->adminToken));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testAdminDeleteAdmin(): void
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $admin2 = new User();
        $admin2->setEmail('admin2@test.com');
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setPassword($hasher->hashPassword($admin2, 'admin123'));
        $this->em->persist($admin2);
        $this->em->flush();

        $this->client->request('DELETE', '/api/admin/users/' . $admin2->getId(), [], [], $this->authHeaders($this->adminToken));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Impossible de supprimer un admin', $data['message']);
    }

    public function testDeleteUserAsNonAdmin(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);

        $this->client->request('DELETE', '/api/admin/users/' . $admin->getId(), [], [], $this->authHeaders($this->clientToken));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteUserUnauthenticated(): void
    {
        $this->client->request('DELETE', '/api/admin/users/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
