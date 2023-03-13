<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
class UserControllerTest extends WebTestCase
{

    public function testCreateUserPageIsRestricted() : void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/users/create');
        $this->assertSelectorTextContains('button', 'Se connecter');
    }

    /**
     *
     * @throws Exception
     */
    public function testCreateUserPageUnauthorizedForUser() : void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws Exception
     */
    public function testCreateUserPageAuthorizedForAdmin() : void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $client->loginUser($testAdminUser);
        $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
    }

    /**
     * @throws Exception
     */
    public function testCreateAUserPageByAdmin() : void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $client->loginUser($testAdminUser);
        $crawler = $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'NewUser';
        $form['user[password][first]'] = 'NewUser';
        $form['user[password][second]'] = 'NewUser';
        $form['user[email]'] = 'NewUser@email.fr';
        $form['user[role]']->select('ROLE_USER');
        $client->submit($form);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-success', "L'utilisateur a bien été ajouté.");

    }

    public function testUsersListPageIsRestrictedForNoLoggedUser() : void
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/users');
        $this->assertSelectorTextContains('button', 'Se connecter');
    }

    /**
     * @throws Exception
     */
    public function testUsersListAuthorizedForAdmin() : void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);


        // retrieve the test user
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $client->loginUser($testAdminUser);
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }
}