<?php

namespace App\MessageHandler;

use App\Entity\ConversionJob;
use App\Message\ConvertFileMessage;
use App\Repository\ConversionJobRepository;
use App\Service\DummyConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ConvertFileMessageHandler
{
    public function __construct(
        private readonly ConversionJobRepository $jobs,
        private readonly EntityManagerInterface $em,
        private readonly DummyConverter $converter,
        private readonly string $projectDir,
    ){}

    public function __invoke(ConvertFileMessage $message): void
    {
        $job = $this->jobs->find($message->jobId);
        if( !$job instanceof ConversionJob){
            return;
        }

        $job->setStatus(ConversionJob::STATUS_PROCESSING);
        $this->em->flush();

        try {
            $content = $this->converter->convert($job->getInputPath(), $job->getOutputFormat());

            $ext = $job->getOutputFormat();
            $outPath = $this->projectDir . '/var/storage/output/' . $job->getIdAsString() . '.' . $ext;

            file_put_contents($outPath, $content);

            $job->setOutputPath($outPath);
            $job->setStatus(ConversionJob::STATUS_DONE);
        } catch (\Throwable $e) {
            $job->setStatus(ConversionJob::STATUS_FAILED);
        }

        $this->em->flush();
    }
}
