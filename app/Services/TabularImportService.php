<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class TabularImportService
{
    private const MAX_DATA_ROWS = 1000;

    private const MAX_XML_BYTES = 10_000_000;

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    public function read(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw new RuntimeException(__('Format file tidak didukung. Gunakan CSV atau XLSX.')),
        };

        return $this->formatRows($rows);
    }

    /** @return array<int, array<int, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException(__('File tidak dapat dibaca.'));
        }

        try {
            $sample = fgets($handle) ?: '';
            $delimiter = $this->detectDelimiter($sample);
            rewind($handle);

            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
                $rows[] = array_map(fn ($value) => $this->toUtf8((string) $value), $row);

                if (count($rows) > self::MAX_DATA_ROWS + 1) {
                    throw new RuntimeException(__('File melebihi batas :count baris data.', ['count' => self::MAX_DATA_ROWS]));
                }
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /** @return array<int, array<int, string>> */
    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('File XLSX tidak dapat dibuka atau rusak.'));
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetPath = $this->firstWorksheetPath($zip);
            $sheetXml = $this->readZipEntry($zip, $sheetPath);

            if ($sheetXml === null) {
                throw new RuntimeException(__('Worksheet pertama tidak ditemukan pada file XLSX.'));
            }

            $sheet = $this->loadXml($sheetXml);
            $namespace = $sheet->getNamespaces(true)[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $sheet->registerXPathNamespace('main', $namespace);
            $xmlRows = $sheet->xpath('//main:sheetData/main:row') ?: [];
            $rows = [];

            foreach ($xmlRows as $xmlRow) {
                if (count($rows) > self::MAX_DATA_ROWS) {
                    throw new RuntimeException(__('File melebihi batas :count baris data.', ['count' => self::MAX_DATA_ROWS]));
                }

                $row = [];
                $xmlRow->registerXPathNamespace('main', $namespace);
                foreach ($xmlRow->xpath('./main:c') ?: [] as $cell) {
                    $reference = (string) $cell['r'];
                    $columnIndex = $this->columnIndex($reference);
                    $type = (string) $cell['t'];
                    $value = '';

                    if ($type === 'inlineStr') {
                        $value = $this->inlineStringValue($cell, $namespace);
                    } else {
                        $rawValue = (string) ($cell->children($namespace)->v ?? '');
                        $value = $type === 's'
                            ? ($sharedStrings[(int) $rawValue] ?? '')
                            : $rawValue;
                    }

                    $row[$columnIndex] = $this->toUtf8($value);
                }

                if ($row !== []) {
                    $maxColumn = max(array_keys($row));
                    $rows[] = array_map(
                        fn ($index) => $row[$index] ?? '',
                        range(0, $maxColumn)
                    );
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    /** @return array<int, string> */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $this->readZipEntry($zip, 'xl/sharedStrings.xml');

        if ($xml === null) {
            return [];
        }

        $document = $this->loadXml($xml);
        $namespace = $document->getNamespaces(true)[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $document->registerXPathNamespace('main', $namespace);

        return array_map(function (SimpleXMLElement $item) use ($namespace): string {
            $item->registerXPathNamespace('main', $namespace);
            $textNodes = $item->xpath('.//main:t') ?: [];

            return implode('', array_map(fn (SimpleXMLElement $node) => (string) $node, $textNodes));
        }, $document->xpath('//main:si') ?: []);
    }

    private function firstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $this->readZipEntry($zip, 'xl/workbook.xml');
        $relationshipsXml = $this->readZipEntry($zip, 'xl/_rels/workbook.xml.rels');

        if ($workbookXml === null || $relationshipsXml === null) {
            return 'xl/worksheets/sheet1.xml';
        }

        $workbook = $this->loadXml($workbookXml);
        $mainNamespace = $workbook->getNamespaces(true)[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $relationshipNamespace = $workbook->getNamespaces(true)['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $sheets = $workbook->children($mainNamespace)->sheets;
        $firstSheet = $sheets?->children($mainNamespace)->sheet[0] ?? null;
        $relationshipId = $firstSheet ? (string) $firstSheet->attributes($relationshipNamespace)['id'] : '';

        if ($relationshipId === '') {
            return 'xl/worksheets/sheet1.xml';
        }

        $relationships = $this->loadXml($relationshipsXml);
        foreach ($relationships->children($relationships->getNamespaces(true)[''] ?? '')->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = str_replace('\\', '/', (string) $relationship['Target']);
                $target = ltrim($target, '/');

                return str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function readZipEntry(ZipArchive $zip, string $path): ?string
    {
        $index = $zip->locateName($path);
        if ($index === false) {
            return null;
        }

        $stat = $zip->statIndex($index);
        if (($stat['size'] ?? 0) > self::MAX_XML_BYTES) {
            throw new RuntimeException(__('Isi file XLSX terlalu besar untuk diproses.'));
        }

        $contents = $zip->getFromIndex($index);

        return $contents === false ? null : $contents;
    }

    private function loadXml(string $xml): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $document = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($document === false) {
            throw new RuntimeException(__('Struktur file spreadsheet tidak valid.'));
        }

        return $document;
    }

    private function inlineStringValue(SimpleXMLElement $cell, string $namespace): string
    {
        $cell->registerXPathNamespace('main', $namespace);
        $nodes = $cell->xpath('.//main:is//main:t') ?: [];

        return implode('', array_map(fn (SimpleXMLElement $node) => (string) $node, $nodes));
    }

    private function columnIndex(string $reference): int
    {
        preg_match('/^([A-Z]+)/i', $reference, $matches);
        $letters = strtoupper($matches[1] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    /**
     * @param  array<int, array<int, string>>  $rawRows
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    private function formatRows(array $rawRows): array
    {
        $rawRows = array_values(array_filter($rawRows, fn (array $row) => $this->hasValues($row)));

        if (count($rawRows) < 2) {
            throw new RuntimeException(__('File harus memiliki baris judul kolom dan minimal satu baris data.'));
        }

        $rawHeaders = array_shift($rawRows);
        $headers = [];

        foreach ($rawHeaders as $index => $header) {
            $base = trim($this->stripBom($header)) ?: __('Kolom :number', ['number' => $index + 1]);
            $candidate = $base;
            $suffix = 2;

            while (in_array($candidate, $headers, true)) {
                $candidate = $base.' ('.$suffix++.')';
            }

            $headers[] = $candidate;
        }

        $rows = [];
        foreach ($rawRows as $rawRow) {
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($rawRow[$index] ?? ''));
            }

            if ($this->hasValues($row)) {
                $rows[] = $row;
            }
        }

        if ($rows === []) {
            throw new RuntimeException(__('File tidak memiliki baris data yang dapat diimpor.'));
        }

        return compact('headers', 'rows');
    }

    /** @param array<int|string, string> $row */
    private function hasValues(array $row): bool
    {
        return collect($row)->contains(fn ($value) => trim((string) $value) !== '');
    }

    private function detectDelimiter(string $sample): string
    {
        $delimiters = [',', ';', "\t"];

        return collect($delimiters)
            ->sortByDesc(fn ($delimiter) => count(str_getcsv($sample, $delimiter, '"', '')))
            ->first();
    }

    private function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function toUtf8(string $value): string
    {
        $encoding = mb_detect_encoding($value, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);

        return $encoding && $encoding !== 'UTF-8'
            ? mb_convert_encoding($value, 'UTF-8', $encoding)
            : $value;
    }
}
