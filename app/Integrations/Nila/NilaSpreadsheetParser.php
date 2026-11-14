<?php

namespace App\Integrations\Nila;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;
use ZipArchive;

final class NilaSpreadsheetParser
{
    private const REQUIRED_HEADERS = [
        'holoocode',
        'holooname',
        'holooregularprice',
        'holoostockquantity',
        'holoocustomercode',
    ];

    /**
     * Parse the first worksheet (or a CSV export) into normalized rows.
     *
     * This class deliberately does not write to catalog tables. Importing is
     * a separate, explicitly confirmed operation.
     *
     * @return array{rows: array<int, array<string, mixed>>, groups: array<string, array<string, mixed>>}
     */
    public function parse(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('فایل واردسازی در دسترس نیست.');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $rawRows = match ($extension) {
            'csv', 'txt' => $this->readCsv($path),
            'xlsx' => $this->readXlsx($path),
            default => throw new RuntimeException('فرمت فایل پشتیبانی نمی‌شود.'),
        };

        return $this->normalizeRows($rawRows);
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('خواندن فایل CSV ممکن نیست.');
        }

        try {
            $sample = (string) fgets($handle);
            rewind($handle);

            $delimiter = ',';
            $counts = [
                ',' => substr_count($sample, ','),
                ';' => substr_count($sample, ';'),
                "\t" => substr_count($sample, "\t"),
            ];
            arsort($counts);
            $candidate = array_key_first($counts);

            if ($candidate !== null && $counts[$candidate] > 0) {
                $delimiter = $candidate;
            }

            $rows = [];

            while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
                if ($row === [null] || $row === []) {
                    continue;
                }

                $rows[] = $row;
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('برای خواندن XLSX، افزونه PHP zip باید روی سرور فعال باشد.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('فایل XLSX معتبر یا قابل خواندن نیست.');
        }

        try {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relationsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if ($workbookXml === false || $relationsXml === false) {
                throw new RuntimeException('ساختار داخلی فایل XLSX ناقص است.');
            }

            $workbook = $this->xml($workbookXml);
            $relations = $this->xml($relationsXml);

            $officeNs = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $documentRelNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
            $packageRelNs = 'http://schemas.openxmlformats.org/package/2006/relationships';

            $sheets = $workbook->children($officeNs)->sheets->children($officeNs);
            $firstSheet = $sheets->sheet[0] ?? null;

            if (! $firstSheet) {
                throw new RuntimeException('هیچ Sheet قابل خواندنی در فایل پیدا نشد.');
            }

            $relationshipId = (string) $firstSheet->attributes($documentRelNs)['id'];
            $target = null;

            foreach ($relations->children($packageRelNs)->Relationship as $relationship) {
                if ((string) $relationship['Id'] === $relationshipId) {
                    $target = (string) $relationship['Target'];
                    break;
                }
            }

            if (! $target) {
                throw new RuntimeException('مسیر Worksheet در فایل پیدا نشد.');
            }

            $sheetPath = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/' . ltrim($target, '/');

            $sheetXml = $zip->getFromName($sheetPath);

            if ($sheetXml === false) {
                throw new RuntimeException('محتوای Worksheet قابل خواندن نیست.');
            }

            $sharedStrings = $this->readSharedStrings($zip, $officeNs);
            $sheet = $this->xml($sheetXml);
            $sheetData = $sheet->children($officeNs)->sheetData;
            $rows = [];

            foreach ($sheetData->children($officeNs)->row as $row) {
                $cells = [];

                foreach ($row->children($officeNs)->c as $cell) {
                    $reference = (string) $cell['r'];
                    $column = $this->columnIndex($reference);
                    $type = (string) $cell['t'];
                    $value = '';

                    if ($type === 'inlineStr') {
                        $inline = $cell->children($officeNs)->is;
                        $value = $this->collectText($inline, $officeNs);
                    } else {
                        $rawValue = (string) ($cell->children($officeNs)->v ?? '');

                        if ($type === 's') {
                            $value = $sharedStrings[(int) $rawValue] ?? '';
                        } else {
                            $value = $rawValue;
                        }
                    }

                    $cells[$column] = $value;
                }

                if ($cells !== []) {
                    $max = max(array_keys($cells));
                    $values = [];

                    for ($index = 0; $index <= $max; $index++) {
                        $values[] = $cells[$index] ?? '';
                    }

                    $rows[] = $values;
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function readSharedStrings(ZipArchive $zip, string $namespace): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $root = $this->xml($xml);
        $strings = [];

        foreach ($root->children($namespace)->si as $item) {
            $strings[] = $this->collectText($item, $namespace);
        }

        return $strings;
    }

    private function collectText(SimpleXMLElement $element, string $namespace): string
    {
        $text = '';

        foreach ($element->children($namespace) as $child) {
            if ($child->getName() === 't') {
                $text .= (string) $child;
            } else {
                $text .= $this->collectText($child, $namespace);
            }
        }

        return $text;
    }

    private function xml(string $contents): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET);

            if (! $xml) {
                throw new RuntimeException('ساختار XML فایل اکسل معتبر نیست.');
            }

            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/i', $cellReference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function normalizeRows(array $rawRows): array
    {
        if (count($rawRows) > 20001) {
            throw new RuntimeException('فایل بیش از ۲۰٬۰۰۰ ردیف دارد؛ آن را به چند فایل کوچک‌تر تقسیم کنید.');
        }

        $headerIndex = null;
        $headerMap = [];

        foreach ($rawRows as $index => $row) {
            $candidate = array_map(
                fn ($value) => $this->normalizeHeader((string) $value),
                $row
            );

            if (count(array_intersect(self::REQUIRED_HEADERS, $candidate)) === count(self::REQUIRED_HEADERS)) {
                $headerIndex = $index;

                foreach ($candidate as $column => $header) {
                    $headerMap[$header] = $column;
                }

                break;
            }
        }

        if ($headerIndex === null) {
            throw new RuntimeException('ستون‌های موردنیاز هولو در فایل پیدا نشدند.');
        }

        $rows = [];
        $groups = [];
        $seenCodes = [];

        foreach (array_slice($rawRows, $headerIndex + 1, null, true) as $rowNumber => $raw) {
            $value = function (string $header) use ($raw, $headerMap): string {
                $column = $headerMap[$header] ?? null;

                return $column === null
                    ? ''
                    : trim((string) ($raw[$column] ?? ''));
            };

            $externalCode = $this->cleanText($value('holoocode'));
            $name = $this->cleanText($value('holooname'));
            $priceRaw = $value('holooregularprice');
            $stockRaw = $value('holoostockquantity');
            $customerCode = $this->cleanText($value('holoocustomercode'));

            if ($externalCode === '' && $name === '' && $priceRaw === '' && $stockRaw === '') {
                continue;
            }

            $errors = [];
            $parts = explode('*', $externalCode);
            $productCode = count($parts) === 2 ? trim($parts[0]) : '';
            $variantCode = count($parts) === 2 ? trim($parts[1]) : '';

            if ($productCode === '' || $variantCode === '') {
                $errors[] = 'کد هولو باید به شکل کد محصول*کد تنوع باشد.';
            }

            if ($name === '') {
                $errors[] = 'نام محصول خالی است.';
            }

            if ($externalCode !== '' && isset($seenCodes[$externalCode])) {
                $errors[] = 'کد تنوع در فایل تکراری است.';
            }
            $seenCodes[$externalCode] = true;

            $price = $this->parseNumber($priceRaw);
            if ($priceRaw === '' || $price === null) {
                $errors[] = 'قیمت خالی یا نامعتبر است؛ قیمت صفر به‌صورت خودکار جایگزین نمی‌شود.';
            } elseif ($price < 0) {
                $errors[] = 'قیمت نمی‌تواند منفی باشد.';
            }

            $stock = $this->parseInteger($stockRaw);
            if ($stock === null || $stock < 0) {
                $errors[] = 'موجودی باید عدد صحیح و بزرگ‌تر یا مساوی صفر باشد.';
            }

            $nameParts = preg_split('/\s*-\s*/u', $name) ?: [$name];
            $colorCandidate = count($nameParts) > 1 ? trim((string) array_pop($nameParts)) : null;
            $productName = trim(implode('-', $nameParts));

            $record = [
                'row_number' => $rowNumber + 1,
                'holoo_code' => $externalCode,
                'product_code' => $productCode,
                'variant_code' => $variantCode,
                'holoo_name' => $name,
                'product_name_candidate' => $productName !== '' ? $productName : $name,
                'color_candidate' => $colorCandidate !== '' ? $colorCandidate : null,
                'price' => $price,
                'stock' => $stock,
                'customer_code' => $customerCode,
                'errors' => $errors,
                'is_valid' => $errors === [],
            ];

            $rows[] = $record;

            if ($productCode !== '') {
                $groups[$productCode] ??= [
                    'product_code' => $productCode,
                    'product_name_candidate' => $record['product_name_candidate'],
                    'rows' => 0,
                    'valid_rows' => 0,
                    'invalid_rows' => 0,
                    'name_conflict' => false,
                ];

                $groups[$productCode]['rows']++;

                if ($groups[$productCode]['product_name_candidate'] !== $record['product_name_candidate']) {
                    $groups[$productCode]['name_conflict'] = true;
                }

                if ($record['is_valid']) {
                    $groups[$productCode]['valid_rows']++;
                } else {
                    $groups[$productCode]['invalid_rows']++;
                }
            }
        }

        $conflictingCodes = [];

        foreach ($groups as $code => $group) {
            if ($group['name_conflict']) {
                $conflictingCodes[$code] = true;
            }
        }

        foreach ($groups as &$group) {
            $group['valid_rows'] = 0;
            $group['invalid_rows'] = 0;
        }
        unset($group);

        foreach ($rows as &$record) {
            $code = $record['product_code'];

            if (isset($conflictingCodes[$code])) {
                $record['is_valid'] = false;
                $record['errors'][] = 'نام پایه در ردیف‌های این کد محصول یکسان نیست.';
            }

            if (isset($groups[$code])) {
                if ($record['is_valid']) {
                    $groups[$code]['valid_rows']++;
                } else {
                    $groups[$code]['invalid_rows']++;
                }
            }
        }
        unset($record);

        return [
            'rows' => $rows,
            'groups' => array_values($groups),
        ];
    }

    private function normalizeHeader(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', trim($value)) ?? trim($value);

        return strtolower(preg_replace('/[\s_\-]+/u', '', $value) ?? $value);
    }

    private function cleanText(string $value): string
    {
        return trim(preg_replace('/\x{00A0}/u', ' ', $value) ?? $value);
    }

    private function parseNumber(string $value): ?float
    {
        $value = $this->normalizeDigits(trim($value));

        if ($value === '') {
            return null;
        }

        $value = str_replace([',', '٬', '،', ' '], '', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value) ?? '';

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function parseInteger(string $value): ?int
    {
        $value = $this->normalizeDigits(trim($value));
        $value = str_replace([',', '٬', '،', ' '], '', $value);

        if ($value === '' || ! preg_match('/^-?\d+$/', $value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
