<?php


namespace App\Tests\Controller;

use App\Service\PhoneHelper;
use DateTime;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    private function cleanSchema($client) {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $em->getMetadataFactory()->getAllMetadata();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $tool->dropSchema($metaData);
        try {
            $tool->createSchema($metaData);
        } catch (ToolsException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    public function testAddPhone()
    {
        $client = static::createClient();

        // Recreate schema
        $this->cleanSchema($client);

        $params = [
            'phone' => '0034677589123'
        ];

        $client->request('POST', '/api/v1/verifications/add', $params);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testVerify()
    {
        $client = static::createClient();

        // Recreate schema
        $this->cleanSchema($client);

        $params = [
            'phone' => '0034677589123'
        ];

        // Creates phone first
        $client->request('POST', '/api/v1/verifications/add', $params);

        $content = json_decode($client->getResponse()->getContent());

        $this->assertFalse($content->error);
        $this->assertEquals(Response::HTTP_OK, $content->code);

        // and verify it
        $params = [
            'verificationId' => $content->data->verificationId,
            'code' => $content->data->code
        ];
        $client->request('POST', '/api/v1/verifications/verify', $params);

        $this->assertFalse($content->error);
        $this->assertEquals(Response::HTTP_OK, $content->code);
    }
}