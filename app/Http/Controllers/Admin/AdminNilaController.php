<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\Nila\NilaSpreadsheetParser;
use App\Models\IntegrationMapping;
use App\Models\NilaImportBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminNilaController extends Controller
{
    public function index(): View
    {
        $base = IntegrationMapping::query()->where('integration', 'nila');

        $products = (clone $base)->where('entity_type', Product::class)->count();
        $variants = (clone $base)->where('entity_type', ProductVariant::class)->count();

        $mappings = (clone $base)
            ->latest('updated_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $latest = $mappings->first();

        return view('admin.nila.index', [
            'products' => $products,
            'variants' => $variants,
            'mappings' => $mappings,
            'latestSyncAt' => $latest?->updated_at,
            'batches' => NilaImportBatch::query()
                ->latest('id')
                ->paginate(10, ['*'], 'batches_page'),
        ]);
    }

    public function upload(Request $request, NilaSpreadsheetParser $parser): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,csv,txt'],
        ], [], [
            'file' => 'فایل اکسل یا CSV',
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'csv', 'txt'], true)) {
            return back()->withErrors([
                'file' => 'فقط فایل XLSX یا CSV مجاز است.',
            ]);
        }

        $path = $file->store('nila/imports', 'local');

        if (! $path) {
            return back()->with('error', 'ذخیره امن فایل انجام نشد.');
        }

        try {
            $parsed = $parser->parse(Storage::disk('local')->path($path));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => $exception instanceof RuntimeException
                        ? $exception->getMessage()
                        : 'خواندن فایل انجام نشد. ساختار فایل را بررسی کنید.',
                ]);
        }

        $rows = $parsed['rows'];
        $validRows = count(array_filter($rows, fn (array $row) => $row['is_valid']));

        $batch = NilaImportBatch::query()->create([
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'disk' => 'local',
            'file_path' => $path,
            'status' => 'preview_ready',
            'total_rows' => count($rows),
            'valid_rows' => $validRows,
            'invalid_rows' => count($rows) - $validRows,
            'product_groups' => count($parsed['groups']),
            'created_by' => $request->user()?->id,
            'metadata' => [
                'extension' => $extension,
                'preview_only' => true,
            ],
        ]);

        return redirect()
            ->route('admin.nila.imports.show', $batch)
            ->with('success', 'فایل با موفقیت بررسی اولیه شد؛ هنوز هیچ محصولی وارد دیتابیس نشده است.');
    }

    public function showImport(
        NilaImportBatch $batch,
        NilaSpreadsheetParser $parser
    ): View {
        abort_unless(
            Storage::disk($batch->disk)->exists($batch->file_path),
            404
        );

        $parsed = $parser->parse(
            Storage::disk($batch->disk)->path($batch->file_path)
        );

        return view('admin.nila.preview', [
            'batch' => $batch,
            'groups' => $parsed['groups'],
            'rows' => array_slice($parsed['rows'], 0, 100),
            'hasMoreRows' => count($parsed['rows']) > 100,
        ]);
    }
}
