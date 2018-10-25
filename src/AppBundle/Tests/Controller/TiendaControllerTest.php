<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TiendaControllerTest extends WebTestCase
{
    public function testTienda()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/tienda');
    }

}
