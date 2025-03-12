<?php

declare(strict_types=1);

namespace App\Domains\Shared\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RemarkData extends Data
{
    public function __construct(
        public readonly string $gds,
        public readonly string $recordLocator,
        public readonly string $reference,
        public readonly string $text,
        public readonly ?array $passengerRefs,
        public readonly ?array $segmentRefs,
    ) {}

    public static function fromSabre(array $data): self
    {
        return new self(
            gds: 'SABRE',
            recordLocator: $data['recordLocator'],
            reference: $data['reference'],
            text: $data['text'],
            passengerRefs: $data['passengerRefs'] ?? null,
            segmentRefs: $data['segmentRefs'] ?? null,
        );
    }

    public static function fromAmadeus(array $data): self
    {
        return new self(
            gds: 'AMADEUS',
            recordLocator: $data['recordLocator'],
            reference: $data['reference'],
            text: $data['freetext'],
            passengerRefs: $data['passengerRefs'] ?? null,
            segmentRefs: $data['segmentRefs'] ?? null,
        );
    }
}
