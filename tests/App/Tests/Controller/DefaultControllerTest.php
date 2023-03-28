<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
    public function testHomepage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testH1Homepage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List');
    }
}
