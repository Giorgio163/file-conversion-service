<?php

namespace App\Controller\Api;

use App\Entity\ConversionJob;
use App\Message\ConvertFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @OA\Info(title="File Conversion Service", version="1.0.0")
 */
#[Route('/api/jobs')]
final class ConversionJobController extends AbstractController
{
    private const INPUT_ALLOWED = ['csv', 'json', 'xlsx', 'ods'];
    private const OUTPUT_ALLOWED = ['json', 'xml'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $bus,
        private readonly string $projectDir
    )
    {}

    /**
     * @OA\Post(
     *   path="/api/jobs",
     *   summary="Create a conversion job",
     *   @OA\Response(response=202, description="Accepted"),
     *   @OA\Response(response=400, description="Bad request")
     * )
     */
    #[Route('', name: 'api_jobs_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $outputFormat = strtolower((string) $request->request->get('outputFormat', ''));

        if(!$file instanceof UploadedFile) {
            return $this->json(['error' => 'file is required'], 400);
        }

        if(!in_array($outputFormat, self::OUTPUT_ALLOWED, true)){
            return $this->json(['error'=> 'outputformat must be json or xml'], 400);
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if(!in_array($ext, self::INPUT_ALLOWED, true)){
            return $this->json(['error' => 'input must be csv,json,xlsx,ods'], 400);
        }

        $inputDir = $this->projectDir . '/var/storage/input';
        if(!is_dir($inputDir)) {
            if (!mkdir($inputDir, 0775, true) && !is_dir($inputDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $inputDir));
            }
        }

        $outputDir = $this->projectDir . '/var/storage/output';
        if(!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $outputDir));
            }
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $storedPath = $inputDir . '/' . $storedName;
        $file->move($inputDir, $storedName);

        $job = new ConversionJob($storedPath, $outputFormat);
        $this->em->persist($job);
        $this->em->flush();

        $this->bus->dispatch(new ConvertFileMessage($job->getIdAsString()));

        return $this->json([
            'id' => $job->getIdAsString(),
            'status' => $job->getStatus(),
            'statusUrl' => '/api/jobs/' . $job->getIdAsString(),
            'downloadUrl' => '/api/jobs/' . $job->getIdAsString() . '/download',
        ], 202);
    }

    /**
     * @OA\Get(
     *   path="/api/jobs/{id}",
     *   summary="Get job status",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    #[Route('/{id}', name: 'api_jobs_get', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $job = $this->em->getRepository(ConversionJob::class)->find($id);
        if(!$job instanceof ConversionJob) {
            return $this->json(['error' => 'not found'], 404);
        }

        return $this->json([
            'id' => $job->getIdAsString(),
            'status' => $job->getStatus(),
            'outputFormat' => $job->getOutputFormat(),
            'createdAt' => $job->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $job->getUpdatedAt()->format(DATE_ATOM),
            'downloadUrl' => $job->getStatus() === ConversionJob::STATUS_DONE
                ? '/api/jobs/' . $job->getIdAsString() . '/download'
                : null,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/api/jobs/{id}/download",
     *   summary="Download converted file",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="File"),
     *   @OA\Response(response=409, description="Not ready")
     * )
     */
    #[Route('/{id}/download', name: 'api_jobs_download', methods: ['GET'])]
    public function download(string $id):Response
    {
        $job =$this->em->getRepository(ConversionJob::class)->find($id);
        if (!$job instanceof ConversionJob) {
            return $this->json(['error' => 'not found'], 404);
        }

        if ($job->getStatus() !== ConversionJob::STATUS_DONE || !$job->getOutputPath()) {
            return $this->json(['error' => 'not ready'], 409);
        }

        return $this->file($job->getOutputPath());
    }
}
