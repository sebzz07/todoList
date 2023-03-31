<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateUserPageIsRestricted(): void
    {
        
        $this->client->followRedirects();
        $this->client->request('GET', '/users/create');
        $this->assertSelectorTextContains('button', 'Se connecter');
    }

    /**
     * @throws Exception
     */
    public function testCreateUserPageUnauthorizedForUser(): void
    {
        $this->client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws Exception
     */
    public function testCreateUserPageAuthorizedForAdmin(): void
    {
        
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testAdminUser */
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $this->client->loginUser($testAdminUser);
        $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }

    /**
     * @throws Exception
     */
    public function testCreateAUserByAdmin(): void
    {
        
        $this->client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testAdminUser */
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $this->client->loginUser($testAdminUser);
        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'NewUser';
        $form['user[password][first]'] = 'NewUser';
        $form['user[password][second]'] = 'NewUser';
        $form['user[email]'] = 'NewUser@email.fr';
        $form['user[role]']->select('ROLE_USER');
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-success', "L'utilisateur a bien été ajouté.");
    }

    public function testUsersListPageIsRestrictedForNoLoggedUser(): void
    {
        
        $this->client->followRedirects();
        $this->client->request('GET', '/users');
        $this->assertSelectorTextContains('button', 'Se connecter');
    }

    /**
     * @throws Exception
     */
    public function testUsersListAuthorizedForAdmin(): void
    {
        
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testAdminUser */
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $this->client->loginUser($testAdminUser);
        $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    /**
     * @throws Exception
     */
    public function testEditAUserByAdmin(): void
    {
        
        $this->client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);
        $idOfEditedUser = $userRepository->findOneBy(['username' => 'user1'])->getId();

        // simulate $testUser being logged in
        $this->client->loginUser($testAdminUser);
        $crawler = $this->client->request('GET', '/users/'.$idOfEditedUser.'/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier user1');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'user1edited';
        $form['user[password][first]'] = 'useredited';
        $form['user[password][second]'] = 'useredited';
        $form['user[email]'] = 'user1editied@test.com';
        $form['user[role]']->select('ROLE_ADMIN');
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-success', "L'utilisateur a bien été modifié.");

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $ModifiedUser = $userRepository->findOneBy(['id' => $idOfEditedUser]);
        $checkPassword = $passwordHasher->isPasswordValid($ModifiedUser, 'useredited');

        $this->assertStringContainsString('user1edited', $ModifiedUser->getUsername());
        $this->assertStringContainsString('user1editied@test.com', $ModifiedUser->getEmail());
        $this->assertTrue($checkPassword);
        $this->assertContains('ROLE_ADMIN', $ModifiedUser->getRoles());
    }
}
