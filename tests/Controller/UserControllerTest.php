<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;

class UserControllerTest extends WebTestCase
{
    public function testCreateUserPageIsRestricted()
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
    public function testCreateUserPageUnauthorizedForUser()
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
    public function testCreateUserPageAuthorizedForAdmin()
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

    public function testUsersListPageIsRestrictedForNoLoggedUser()
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/users');
        $this->assertSelectorTextContains('button', 'Se connecter');
    }

    /**
     * @throws Exception
     */
    public function testUsersListAuthorizedForAdmin()
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