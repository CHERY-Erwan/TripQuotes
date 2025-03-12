<?php

namespace App\Http\Controllers;

use App\Domains\Shared\Data\RemarkData;
use App\Services\RemarkServiceInterface;

class TestController extends Controller
{
    public function __construct(private readonly RemarkServiceInterface $remarkService) {}

    public function index()
    {
        $this->remarkService->syncWithDatabase('LFPEXD', 'SABRE');

        $this->remarkService->upsertRemark(
            remark: RemarkData::from([
                'recordLocator' => 'LFPEXD',
                'gds' => 'SABRE',
                'reference' => '1',
                'text' => 'TRIPQUOTE-ID-1',
            ]),
            pattern: 'TRIPQUOTE-ID-*',
        );

        $this->remarkService->upsertRemark(
            remark: RemarkData::from([
                'recordLocator' => 'LFPEXD',
                'gds' => 'SABRE',
                'reference' => '1',
                'text' => 'NO-MU-TEST',
            ]),
            pattern: 'NO-MU-*',
        );

        $this->remarkService->upsertRemark(
            remark: RemarkData::from([
                'recordLocator' => 'LFPEXD',
                'gds' => 'SABRE',
                'reference' => '1',
                'text' => 'MU-TEST',
            ]),
            pattern: 'MU-*',
            deletePattern: 'NO-MU-*',
        );

        $newRemarks = collect([
            RemarkData::from([
                'recordLocator' => 'LFPEXD',
                'gds' => 'SABRE',
                'reference' => '1',
                'text' => 'PRIXA-111.1EUR/PQ1/FDOS-35.00EUR',
            ]),
            RemarkData::from([
                'recordLocator' => 'LFPEXD',
                'gds' => 'SABRE',
                'reference' => '1',
                'text' => 'PRIXA-111.1EUR/PQ2/FDOS-35.00EUR',
            ]),
        ]);

        $this->remarkService->upsertRemarksWithGroupPattern(
            newRemarks: $newRemarks,
            groupPattern: 'PRIXA-*EUR/PQ*/FDOS-*EUR',
            remarkPatterns: collect(['PRIXA-*EUR/PQ1/FDOS-*EUR', 'PRIXA-*EUR/PQ2/FDOS-*EUR'])
        );

        $this->remarkService->syncWithGds('LFPEXD', 'SABRE');
    }
}
