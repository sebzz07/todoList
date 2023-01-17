<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 9; ++$i) {
            $admin = new User();
            $admin->setUsername('admin' . $i)
                ->setEmail('admin' . $i . '@test.com')
                ->setRoles(['ROLE_ADMIN'])
                ->setPassword($this->userPasswordHasher->hashPassword($admin, 'admin'));

            $user = new User();
            $user->setUsername('user' . $i)
                ->setEmail('user' . $i . '@test.com')
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->userPasswordHasher->hashPassword($user, 'user'));

            for ($j = 1; $j <= 8; ++$j) {
                $toggle = ($j % 2 == 0) ? true : false;

                $task = new Task();
                $task->setUser($admin)
                    ->setTitle('task n°' . $j . 'of admin n°' . $i)
                    ->setContent('Lorem ipsum dolor sit amet consectetur
            adipisicing, elit. Libero tenetur beatae repellendus possimus magni
            quae! Impedit soluta sit.')
                    ->toggle($toggle);
                $manager->persist($task);

                $userTask = new Task();
                $userTask->setUser($user)
                    ->setTitle('task n°' . $j . 'of user n°' . $i)
                    ->setContent('Lorem ipsum dolor sit amet consectetur
            adipisicing, elit. Libero tenetur beatae repellendus possimus magni
            quae! Impedit.')
                    ->toggle($toggle);
                $manager->persist($userTask);
            }
            $manager->persist($admin);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
