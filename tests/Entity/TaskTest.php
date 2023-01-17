<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;


class TaskTest extends TestCase
{
    public function testTitle()
    {
        $task = new Task();
        $task->setTitle("title");
        $this->assertSame("title", $task->getTitle());
    }

    public function testContent()
    {
        $task = new Task();
        $task->setContent("content");
        $this->assertSame("content", $task->getContent());
    }

    public function testIsDone()
    {
        $task = new Task();
        $task->toggle(true);
        $this->assertSame(true, $task->isDone());
    }
}