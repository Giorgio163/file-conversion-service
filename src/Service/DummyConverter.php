<?php

namespace App\Service;

final class DummyConverter
{
    public function convert(string $inputPath, string $outputFormat): string
    {
        sleep(125);

        $payload = [
            'source' => basename($inputPath),
            'convertedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'note' => 'Dummy conversion output'
        ];

        if ($outputFormat === 'json') {
            return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        }

        $xml = new \SimpleXMLElement('<conversion/>');
        foreach ($payload as $k => $v) {
            $xml->addChild($k, htmlspecialchars((string) $v, ENT_XML1));
        }
        return $xml->asXML() ?: "<conversion />\n";
    }
}
