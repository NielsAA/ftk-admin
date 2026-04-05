<?php

namespace App\Http\Controllers;

use App\Filament\Imports\MemberImporter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberImportTemplateController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $filename = 'members-import-skabelon.csv';
        $headers = MemberImporter::templateColumns();

        return response()->streamDownload(function () use ($headers): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, ';');

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
