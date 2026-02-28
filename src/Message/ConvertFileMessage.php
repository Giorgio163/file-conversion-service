<?php

namespace App\Message;

final class ConvertFileMessage
{
    public function __construct(
        public readonly string $jobId
    ) {}
}
