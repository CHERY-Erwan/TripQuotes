<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Shared\Data\RemarkData;
use Illuminate\Support\Collection;

interface RemarkServiceInterface
{
    public function syncWithDatabase(string $recordLocator, string $gds): void;
    public function syncWithGds(string $recordLocator, string $gds): void;
    public function upsertRemark(RemarkData $remark, string $pattern, ?string $deletePattern = null): void;
    public function upsertRemarksWithGroupPattern(Collection $newRemarks, string $groupPattern, Collection $remarkPatterns): void;
}
