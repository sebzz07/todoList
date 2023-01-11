<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TaskController extends AbstractController
{

    #[Route(path: '/tasks/create', name: 'task_create')]
    #[IsGranted('ROLE_USER', message: "Vous devez être connecter avec un compte utilisateur")]
    public function createAction(Request $request, EntityManagerInterface $em): RedirectResponse|Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $task->setUser($user);
            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/tasks/{isDone}', name: 'task_list')]
    #[IsGranted('ROLE_USER', message: "Vous devez être connecter avec un compte utilisateur")]
    public function listAction(TaskRepository $taskRepository, bool $isDone = false): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userId = $user->getId();
        if (null === $userId) {
            $this->addFlash('error', 'Connectez vous pour accèder à votre liste de taches');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('task/list.html.twig', ['tasks' => $taskRepository->findBy([ "user" => [$user, null], "isDone" => $isDone ])]);
    }

    #[Route(path: '/tasks/{id}/edit', name: 'task_edit')]
    #[IsGranted('ROLE_USER', message: "Vous devez être connecter avec un compte utilisateur")]
    public function editAction(Task $task, TaskRepository $taskRepository, Request $request): RedirectResponse|Response
    {
        if($task->getUser() !== $this->getUser()){
            $this->addFlash('error', 'Vous ne possédez pas de droit suffisant pour editer cette tâche');
            return $this->redirectToRoute('task_list');
        }
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->add($task, true);

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route(path: '/tasks/{id}/toggle', name: 'task_toggle')]
    #[IsGranted('ROLE_USER', message: "Vous devez être connecter avec un compte utilisateur")]
    public function toggleTaskAction(Task $task, TaskRepository $taskRepository): RedirectResponse
    {
        if($this->getUser()->getRoles() == "ROLE_USER" and $task->getUser() !== $this->getUser()){
            $this->addFlash('error', 'Vous ne possédez pas de droit suffisant pour changer cette tâche');
            return $this->redirectToRoute('task_list');
        }
        if($this->getUser()->getRoles() == "ROLE_ADMIN" and $task->getUser() !== ($this->getUser()||null)){
            $this->addFlash('error', "Vous ne pouvez pas changer la tâche d'un autre utilisateur");
            return $this->redirectToRoute('task_list');
        }
        $task->toggle(!$task->isDone());
        $taskRepository->add($task, true);

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route(path: '/tasks/{id}/delete', name: 'task_delete')]
    #[IsGranted('ROLE_USER', message: "Vous devez être connecter avec un compte utilisateur")]
    public function deleteTaskAction(Task $task, EntityManagerInterface $em): RedirectResponse
    {
        if($this->getUser()->getRoles() == "ROLE_USER" and $task->getUser() !== $this->getUser()){
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres tâches ');
            return $this->redirectToRoute('task_list');
        }
        if($this->getUser()->getRoles() == "ROLE_ADMIN" and $task->getUser() !== ($this->getUser()||null)){
            $this->addFlash('error', "Vous ne pouvez pas supprimer la tâche d'un autre utilisateur");
            return $this->redirectToRoute('task_list');
        }
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
