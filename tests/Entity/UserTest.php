<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUsername(): User
    {
        $user = new user();
        $user->setUsername('userAdmin');
        $this->assertSame('userAdmin', $user->getUsername());

        return $user;
    }

    /**
     * @depends testUsername
     */
    public function testPassword(User $user): User
    {
        $user->setPassword('adminPwd');
        $this->assertSame('adminPwd', $user->getPassword());

        return $user;
    }

    /**
     * @depends testPassword
     */
    public function testEmail(User $user): User
    {
        $user->setEmail('admin@email.com');
        $this->assertSame('admin@email.com', $user->getEmail());

        return $user;
    }
}
