<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    /**
     * @throws \Exception
     */
    public function testUsersListAuthorizedForAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testAdminUser */
        $testAdminUser = $userRepository->findOneBy(['username' => 'admin1']);

        // simulate $testUser being logged in
        $client->loginUser($testAdminUser);
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'test1',
            'task[content]' => 'test1',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('strong', 'Superbe !');
    }

    /**
     * @throws \Exception
     */
    public function testUsersListUnauthorizedForUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'test1',
            'task[content]' => 'test1',
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('strong', 'Superbe !');
    }

    /**
     * @throws \Exception
     */
    public function testUsersNotAllowedToAccessTasksOfAnotherUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/tasks/21/edit');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('strong', 'Oops !');
    }

    /**
     * @throws \Exception
     */
    public function testUsersEditTaskWithSuccess(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/tasks/2/edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'ModifiedTitle',
            'task[content]' => 'ModifiedContent',
        ]);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('div', 'La tâche a bien été modifiée.');

        $ModifiedTask = $taskRepository->findOneBy(['id' => '2']);
        $this->assertStringContainsString('ModifiedTitle', $ModifiedTask->getTitle());
        $this->assertStringContainsString('ModifiedContent', $ModifiedTask->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testUsersToggleTaskWithSuccess(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $client->followRedirects();
        $crawler = $client->request('GET', '/tasks/6/toggle');
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('div', 'La tâche a bien été modifiée.');
    }

    /**
     * @throws \Exception
     */
    public function testUsersNotAllowedToToggleTasksOfAnotherUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/tasks/22/toggle');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('strong', 'Oops !');
    }

    /**
     * @throws \Exception
     */
    public function testUserDeleteHisTask(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'user1']);
        $task = $testUser->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.success', 'La tâche a bien été supprimée.');

        // $ModifiedTask = $taskRepository->findOneBy(['id' => $task->getId()]);
        // $this->assertTrue($ModifiedTask);
    }
}
