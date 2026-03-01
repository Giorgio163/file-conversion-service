<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiDocJsonController
{
    #[Route('/api/doc.json', name: 'api_doc_json', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'File Conversion Service',
                'version' => '1.0.0',
            ],
            'paths' => [
                '/api/jobs' => [
                    'post' => [
                        'summary' => 'Create a conversion job',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'multipart/form-data' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['file', 'outputFormat'],
                                        'properties' => [
                                            'file' => ['type' => 'string', 'format' => 'binary'],
                                            'outputFormat' => ['type' => 'string', 'enum' => ['json', 'xml']],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '202' => [
                                'description' => 'Accepted',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'string'],
                                                'status' => ['type' => 'string'],
                                                'statusUrl' => ['type' => 'string'],
                                                'downloadUrl' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '400' => ['description' => 'Bad request'],
                        ],
                    ],
                ],
                '/api/jobs/{id}' => [
                    'get' => [
                        'summary' => 'Get job status',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'OK'],
                            '404' => ['description' => 'Not found'],
                        ],
                    ],
                ],
                '/api/jobs/{id}/download' => [
                    'get' => [
                        'summary' => 'Download converted file',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'string'],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'File'],
                            '404' => ['description' => 'Not found'],
                            '409' => ['description' => 'Not ready'],
                        ],
                    ],
                ],
            ],
        ];

        return new JsonResponse($spec);
    }
}
