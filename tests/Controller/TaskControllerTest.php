<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    /**
     * @throws Exception
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
     * @throws Exception
     */
    public function testUsersListUnauthorizedForUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
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
     * @throws Exception
     */
    public function testUsersNotAllowedToAccessTasksOfAnotherUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
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
     * @throws Exception
     */
    public function testUsersEditTaskWithSuccess(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $taskRepository = static::getContainer()->get(TaskRepository::class);

        // retrieve the test user
        /** @var User $testUser */
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
     * @throws Exception
     */
    public function testUsersToggleTaskWithSuccess(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
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
     * @throws Exception
     */
    public function testUsersNotAllowedToToggleTasksOfAnotherUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test userAdmin
        /** @var User $testUserA */
        $testUserA = $userRepository->findOneBy(['username' => 'user1']);

        // retrieve the test user
        /** @var User $testUserB */
        $testUserB = $userRepository->findOneBy(['username' => 'user2']);

        /** @var Task $task */
        $task = $testUserB->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUserA);
        $client->request('GET', '/tasks/'.$task->getId().'/toggle');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('html div.alert-danger', "Oops ! Vous ne possédez pas de droit suffisant pour changer cette tâche");
    }

    /**
     * @throws Exception
     */
    public function testUserAdminNotAllowedToToggleTasksOfAnotherUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test userAdmin
        /** @var User $testUserAdmin */
        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin1']);


        // retrieve the test user
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        /** @var Task $task */
        $task = $testUser->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUserAdmin);
        $client->request('GET', '/tasks/'.$task->getId().'/toggle');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('html div.alert-danger', "Oops ! Vous ne pouvez pas changer la tâche d'un autre utilisateur");

    }


    /**
     * @throws Exception
     */
    public function testUserDeleteHisTask(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        /** @var Task $task */
        $task = $testUser->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUser);
        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-success', "La tâche a bien été supprimée.");

    }

    /**
     * @throws Exception
     */
    public function testUserAdminDeleteHisTask(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test userAdmin
        /** @var User $testUserAdmin */
        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin1']);

        /** @var Task $task */
        $task = $testUserAdmin->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUserAdmin);
        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-success', "La tâche a bien été supprimée.");

    }

    /**
     * @throws Exception
     */
    public function testUserDeleteTaskOfAnOtherUser(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test userAdmin
        /** @var User $testUserA */
        $testUserA = $userRepository->findOneBy(['username' => 'user1']);

        // retrieve the test user
        /** @var User $testUserB */
        $testUserB = $userRepository->findOneBy(['username' => 'user2']);

        /** @var Task $task */
        $task = $testUserB->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUserA);
        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-danger', "Oops ! Vous ne pouvez supprimer que vos propres tâches");
    }

    /**
     * @throws Exception
     */
    public function testAdminDeleteTaskOfAnOtherUser(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test userAdmin
        /** @var User $testUserAdmin */
        $testUserAdmin = $userRepository->findOneBy(['username' => 'admin1']);

        // retrieve the test user
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'user1']);

        /** @var Task $task */
        $task = $testUser->getTasks()->first();

        // simulate $testUser being logged in
        $client->loginUser($testUserAdmin);
        $client->request('GET', '/tasks/'.$task->getId().'/delete');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html div.alert-danger', "Oops ! Vous ne pouvez pas supprimer la tâche d'un autre utilisateur");
    }
}
