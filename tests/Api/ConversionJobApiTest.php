<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ConversionJobApiTest extends WebTestCase
{
    public function testCreateJobDispatchesMessage() : void
    {
        $client = static::createClient();

        $tmp = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($tmp, "a,b\n1,2\n");

        $uploaded = new UploadedFile(
            $tmp,
            'sample.csv',
            'text/csv',
            null,
            true
        );

        try {
            $client->request('POST', '/api/jobs', [
                'outputFormat' => 'json',
            ], [
                'file' => $uploaded,
            ]
            );

            $this->assertResponseStatusCodeSame(202);

            $data = json_decode($client->getResponse()->getContent() ?? '', true);
            self::assertIsArray($data);
            self::assertArrayHasKey('id', $data);
            self::assertArrayHasKey('status', $data);
            self::assertContains($data['status'], ['PENDING', 'DONE']);
        } finally {
            @unlink($tmp);
        }
    }

    public function testGetJobNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/jobs/00000000-0000-0000-0000-000000000000');
        $this->assertResponseStatusCodeSame(404);
    }
}
