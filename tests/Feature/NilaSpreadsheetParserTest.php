<?php

namespace Tests\Feature;

use App\Integrations\Nila\NilaSpreadsheetParser;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class NilaSpreadsheetParserTest extends TestCase
{
    public function test_xlsx_shared_strings_inline_strings_and_persian_numbers_are_parsed(): void
    {
        if (! class_exists(ZipArchive::class) || ! function_exists('simplexml_load_string')) {
            $this->markTestSkipped('The PHP zip and simplexml extensions are required.');
        }

        $shared = [
            'HolooCode',
            'HolooName',
            'HolooRegularPrice',
            'HolooStockQuantity',
            'HolooCustomerCode',
            'P-001*V-01',
            '۱۲۵٬۰۰۰',
            '۸',
            'C-001',
        ];

        $sharedXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="9" uniqueCount="9">'
            . implode('', array_map(
                fn (string $value) => '<si><t>' . htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>',
                $shared
            ))
            . '</sst>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            . '<row r="1">'
            . '<c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c>'
            . '<c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c>'
            . '<c r="E1" t="s"><v>4</v></c>'
            . '</row>'
            . '<row r="2">'
            . '<c r="A2" t="s"><v>5</v></c>'
            . '<c r="B2" t="inlineStr"><is><t>کفش ورزشی - مشکی</t></is></c>'
            . '<c r="C2" t="s"><v>6</v></c><c r="D2" t="s"><v>7</v></c>'
            . '<c r="E2" t="s"><v>8</v></c>'
            . '</row>'
            . '</sheetData></worksheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $relationsXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $temporaryPath = tempnam(sys_get_temp_dir(), 'nila-xlsx-');
        $this->assertNotFalse($temporaryPath);
        @unlink($temporaryPath);
        $path = $temporaryPath . '.xlsx';
        $zip = new ZipArchive();

        try {
            $this->assertSame(true, $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
            $zip->addFromString('xl/workbook.xml', $workbookXml);
            $zip->addFromString('xl/_rels/workbook.xml.rels', $relationsXml);
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
            $zip->addFromString('xl/sharedStrings.xml', $sharedXml);
            $zip->close();

            $parsed = app(NilaSpreadsheetParser::class)->parse($path);

            $this->assertCount(1, $parsed['rows']);
            $this->assertCount(1, $parsed['groups']);
            $this->assertSame('P-001', $parsed['rows'][0]['product_code']);
            $this->assertSame('V-01', $parsed['rows'][0]['variant_code']);
            $this->assertSame('کفش ورزشی - مشکی', $parsed['rows'][0]['holoo_name']);
            $this->assertSame('مشکی', $parsed['rows'][0]['color_candidate']);
            $this->assertEquals(125000, $parsed['rows'][0]['price']);
            $this->assertSame(8, $parsed['rows'][0]['stock']);
            $this->assertTrue($parsed['rows'][0]['is_valid']);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function test_invalid_xlsx_is_rejected_without_fallback_to_an_empty_catalog(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is required.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'nila-invalid-');
        $this->assertNotFalse($temporaryPath);
        @unlink($temporaryPath);
        $path = $temporaryPath . '.xlsx';
        $zip = new ZipArchive();

        try {
            $this->assertSame(true, $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
            $zip->addFromString('xl/workbook.xml', 'not-xml');
            $zip->close();

            $this->expectException(RuntimeException::class);
            app(NilaSpreadsheetParser::class)->parse($path);
        } finally {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
