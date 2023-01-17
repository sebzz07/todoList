<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
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
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'test1',
            'task[content]' => 'test1'
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('strong', 'Superbe !');
    }


}