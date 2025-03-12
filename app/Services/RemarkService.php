<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Shared\Data\RemarkData;
use App\Models\Remark;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class RemarkService implements RemarkServiceInterface
{
    public function syncWithDatabase(string $recordLocator, string $gds): void
    {
        $dbRemarks = RemarkData::collect(
            Remark::query()
                ->where('gds', $gds)
                ->where('record_locator', $recordLocator)
                ->get()
        );

        $gdsRemarks = $this->getGdsRemarks($recordLocator, $gds);

        // Remarks to add to Database (in Sabre but not in DB)
        $remarksToAdd = $gdsRemarks->filter(function ($gdsRemark) use ($dbRemarks) {
            return !$dbRemarks->contains(function ($dbRemark) use ($gdsRemark) {
                return $gdsRemark->text === $dbRemark->text;
            });
        });

        // Remarks to remove from Database (in DB but not in Sabre)
        $remarksToRemove = $dbRemarks->filter(function ($dbRemark) use ($gdsRemarks) {
            return !$gdsRemarks->contains(function ($gdsRemark) use ($dbRemark) {
                return $dbRemark->text === $gdsRemark->text;
            });
        });

        $remarksToAdd->each(function ($remark) {
            Remark::create($remark->toArray());
        });

        Remark::query()
            ->where('gds', $gds)
            ->where('record_locator', $recordLocator)
            ->whereIn('text', $remarksToRemove->pluck('text'))
            ->delete();

        Log::channel('tripQuotes')->info("Synced database remarks with GDS {$gds} remarks", [
            'recordLocator' => $recordLocator,
            'remarksToAdd' => $remarksToAdd->toArray(),
            'remarksToRemove' => $remarksToRemove->toArray()
        ]);
    }

    public function syncWithGds(string $recordLocator, string $gds): void
    {
        $dbRemarks = RemarkData::collect(
            Remark::query()
                ->where('gds', $gds)
                ->where('record_locator', $recordLocator)
                ->get()
        );

        $gdsRemarks = $this->getGdsRemarks($recordLocator, $gds);

        // Remarks to add to Sabre (in DB but not in Sabre)
        $remarksToAdd = $dbRemarks->filter(function ($dbRemark) use ($gdsRemarks) {
            return !$gdsRemarks->contains(function ($gdsRemark) use ($dbRemark) {
                return $gdsRemark->text === $dbRemark->text;
            });
        });

        // Remarks to remove from Sabre (in Sabre but not in DB)
        $remarksToRemove = $gdsRemarks->filter(function ($gdsRemark) use ($dbRemarks) {
            return !$dbRemarks->contains(function ($dbRemark) use ($gdsRemark) {
                return $dbRemark->text === $gdsRemark->text;
            });
        });

        // Send actual API call to Sabre to add remarks

        // Send actual API call to Sabre to delete remarks

        Log::channel('tripQuotes')->info("Synced GDS {$gds} remarks with database remarks", [
            'recordLocator' => $recordLocator,
            'remarksToAdd' => $remarksToAdd->toArray(),
            'remarksToRemove' => $remarksToRemove->toArray()
        ]);
    }

    public function upsertRemark(RemarkData $remark, string $pattern, ?string $deletePattern = null): void
    {
        $existingRemark = Remark::query()
            ->where('record_locator', $remark->recordLocator)
            ->where('gds', $remark->gds)
            ->whereRaw("text LIKE ?", [str_replace('*', '%', $pattern)])
            ->first();

        if ($existingRemark && $existingRemark->text !== $remark->text) {
            $existingRemark->update($remark->toArray());
        }

        if (! $existingRemark) {
            Remark::create($remark->toArray());
        }

        if ($deletePattern) {
            $existingRemarks = Remark::query()
                ->where('record_locator', $remark->recordLocator)
                ->where('gds', $remark->gds)
                ->whereRaw("text LIKE ?", [str_replace('*', '%', $deletePattern)])
                ->get();

            foreach ($existingRemarks as $existingRemark) {
                $existingRemark->delete();
            }
        }
    }

    public function upsertRemarksWithGroupPattern(Collection $newRemarks, string $groupPattern, Collection $remarkPatterns): void
    {
        $existingRemarks = Remark::query()
            ->where('record_locator', $newRemarks->first()->recordLocator)
            ->where('gds', $newRemarks->first()->gds)
            ->whereRaw("text LIKE ?", [str_replace('*', '%', $groupPattern)])
            ->get();

        $textsToKeep = $newRemarks->pluck('text')->toArray();
        $remarksToDelete = $existingRemarks->filter(fn($remark) => !in_array($remark->text, $textsToKeep));

        if ($remarksToDelete->isNotEmpty()) {
            Remark::query()
                ->whereIn('id', $remarksToDelete->pluck('id'))
                ->delete();
        }

        foreach ($newRemarks as $index => $newRemark) {
            $pattern = $remarkPatterns->get($index);
            $this->upsertRemark($newRemark, $pattern);
        }

        Log::channel('tripQuotes')->info("Synced remarks by pattern", [
            'recordLocator' => $newRemarks->first()->recordLocator,
            'gds' => $newRemarks->first()->gds,
            'pattern' => $groupPattern,
            'added' => $newRemarks->toArray(),
            'deleted' => $remarksToDelete->toArray()
        ]);
    }

    private function getGdsRemarks(string $recordLocator, string $gds): Collection
    {
        $fixturePath = database_path("fixtures/{$gds}/remarks-{$recordLocator}.json");

        if (file_exists($fixturePath) && app()->environment(['local', 'testing'])) {
            $jsonData = json_decode(file_get_contents($fixturePath), true);

            return collect(array_map(function ($item) {
                return RemarkData::fromSabre($item);
            }, $jsonData));
        }

        // Call actual API in production
        return collect();
    }
}
