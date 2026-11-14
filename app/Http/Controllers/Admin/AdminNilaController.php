<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\Nila\NilaSpreadsheetParser;
use App\Integrations\Nila\NilaImportPreviewService;
use App\Jobs\RunNilaSpreadsheetImport;
use App\Models\IntegrationMapping;
use App\Models\NilaImportBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $previewPath = 'nila/previews/' . $batch->id . '.json';

        try {
            $previewJson = json_encode(
                $parsed,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if (! Storage::disk('local')->put($previewPath, $previewJson)) {
                throw new RuntimeException('ذخیره پیش‌نمایش فایل انجام نشد.');
            }

            $metadata = $batch->metadata ?? [];
            $metadata['preview_path'] = $previewPath;
            $batch->update(['metadata' => $metadata]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete([$path, $previewPath]);
            $batch->delete();
            report($exception);

            return back()->with('error', 'ذخیره نسخه امن پیش‌نمایش فایل انجام نشد.');
        }

        return redirect()
            ->route('admin.nila.imports.show', $batch)
            ->with('success', 'فایل با موفقیت بررسی اولیه شد؛ هنوز هیچ محصولی وارد دیتابیس نشده است.');
    }

    public function importNext(
        Request $request,
        NilaImportBatch $batch
    ): RedirectResponse {
        $validated = $request->validate([
            'batch_size' => ['nullable', 'integer', 'min:1', 'max:50'],
            'confirm_stock_snapshot' => ['required', 'accepted'],
        ]);

        $limit = (int) ($validated['batch_size'] ?? 25);
        $token = (string) Str::uuid();

        try {
            DB::transaction(function () use ($batch, $token): void {
                $locked = NilaImportBatch::query()
                    ->lockForUpdate()
                    ->findOrFail($batch->id);

                if ($locked->status === 'completed') {
                    throw new RuntimeException('این فایل قبلاً به پایان رسیده است.');
                }

                if (
                    in_array($locked->status, ['queued', 'processing'], true)
                    && $locked->updated_at?->greaterThan(now()->subMinutes(15))
                ) {
                    throw new RuntimeException('این فایل در حال اجرا یا انتظار صف است؛ اجرای تکراری مجاز نیست.');
                }

                $metadata = $locked->metadata ?? [];
                $metadata['queue_token'] = $token;

                $locked->update([
                    'status' => 'queued',
                    'metadata' => $metadata,
                ]);
            });

            RunNilaSpreadsheetImport::dispatch($batch->id, $limit, $token);
        } catch (Throwable $exception) {
            DB::transaction(function () use ($batch, $token): void {
                $locked = NilaImportBatch::query()
                    ->lockForUpdate()
                    ->find($batch->id);

                if (! $locked) {
                    return;
                }

                $metadata = $locked->metadata ?? [];
                if (($metadata['queue_token'] ?? null) !== $token) {
                    return;
                }

                unset($metadata['queue_token']);

                $locked->update([
                    'status' => 'ready',
                    'metadata' => $metadata,
                ]);
            });

            if (! $exception instanceof RuntimeException) {
                report($exception);
            }

            return back()->with(
                'error',
                $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'قرار دادن مرحله Import در صف انجام نشد؛ فایل برای تلاش دوباره آماده شد.'
            );
        }

        return back()->with(
            'success',
            'مرحله Import در صف اجرا قرار گرفت. نتیجه در همین گزارش قابل پیگیری است.'
        );
    }

    public function showImport(
        Request $request,
        NilaImportBatch $batch,
        NilaSpreadsheetParser $parser,
        NilaImportPreviewService $comparisonService
    ): View {
        abort_unless(
            Storage::disk($batch->disk)->exists($batch->file_path),
            404
        );

        $metadata = $batch->metadata ?? [];
        $previewPath = $metadata['preview_path'] ?? null;
        $parsed = null;

        if (
            is_string($previewPath)
            && $previewPath !== ''
            && Storage::disk('local')->exists($previewPath)
        ) {
            $parsed = json_decode(
                Storage::disk('local')->get($previewPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } else {
            $parsed = $parser->parse(
                Storage::disk($batch->disk)->path($batch->file_path)
            );

            $previewPath = 'nila/previews/' . $batch->id . '.json';
            $previewJson = json_encode(
                $parsed,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if (Storage::disk('local')->put($previewPath, $previewJson)) {
                $metadata['preview_path'] = $previewPath;
                $batch->update(['metadata' => $metadata]);
            }
        }

        abort_unless(
            is_array($parsed)
            && isset($parsed['groups'], $parsed['rows'])
            && is_array($parsed['groups'])
            && is_array($parsed['rows']),
            500,
            'نسخه ذخیره‌شده پیش‌نمایش معتبر نیست.'
        );

        return view('admin.nila.preview', [
            'batch' => $batch,
            'groups' => $this->paginateArray($parsed['groups'], 50, 'groups_page', $request),
            'rows' => $this->paginateArray($parsed['rows'], 100, 'rows_page', $request),
            'failedProducts' => $batch->metadata['failed_products'] ?? [],
            'comparison' => $comparisonService->summarize($parsed),
        ]);
    }

    private function paginateArray(
        array $items,
        int $perPage,
        string $pageName,
        Request $request
    ): LengthAwarePaginator {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $page = max(1, $page);

        return new LengthAwarePaginator(
            array_values(array_slice($items, ($page - 1) * $perPage, $perPage)),
            count($items),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except($pageName),
                'pageName' => $pageName,
            ]
        );
    }

}
