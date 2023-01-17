<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends WebTestCase
{
    public function testDisplayLoginPage()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('alert alert-danger');

    }
    public function testLoginWithBadCredentials()
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'fakeUser',
            '_password' => 'fakePwd'
        ]);
        $client->submit($form);
        //$this->assertResponseRedirects('/login');
        //$client->followRedirect();
        $this->assertSelectorTextContains('button', 'Se connecter');
        $this->assertSelectorExists('div','Invalid credentials.');
    }

    public function testLoginWithRightCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'admin1',
            '_password' => 'admin'
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Bienvenue admin1');
    }
}