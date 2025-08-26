<?php

namespace App\Exports;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SystemLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Validated filter payload from request.
     *
     * @var array<string, mixed>
     */
    protected array $filters;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Build the query with the same filter semantics as the listing endpoint.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = SystemLog::query();

        $f = $this->filters;

        if (!empty($f['entity'])) {
            $query->where('entity', $f['entity']);
        }
        if (!empty($f['action'])) {
            $query->where('action', $f['action']);
        }
        if (isset($f['user_id']) && $f['user_id'] !== '') {
            $query->where('user_id', (int) $f['user_id']);
        }
        if (isset($f['entity_id']) && $f['entity_id'] !== '') {
            $query->where('entity_id', (int) $f['entity_id']);
        }
        if (!empty($f['method'])) {
            $query->where('method', $f['method']);
        }
        if (!empty($f['path'])) {
            $query->where('path', $f['path']);
        }

        if (isset($f['q']) && trim((string) $f['q']) !== '') {
            $needle = '%' . str_replace(['%', '_'], ['\%', '\_'], trim((string) $f['q'])) . '%';
            $query->where(function ($w) use ($needle) {
                $w->where('entity', 'like', $needle)
                  ->orWhere('action', 'like', $needle)
                  ->orWhere('path', 'like', $needle)
                  ->orWhere('method', 'like', $needle)
                  ->orWhere('user_agent', 'like', $needle);
            });
        }

        if (!empty($f['date_from'])) {
            $query->where('created_at', '>=', $f['date_from'] . ' 00:00:00');
        }
        if (!empty($f['date_to'])) {
            $query->where('created_at', '<=', $f['date_to'] . ' 23:59:59');
        }

        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Column headers for the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'created_at',
            'user_id',
            'entity',
            'action',
            'method',
            'path',
        ];
    }

    /**
     * Map a SystemLog row to a flat array in column order.
     *
     * @param \App\Models\SystemLog $row
     * @return array<int, scalar|null>
     */
    public function map($row): array
    {
        $created = $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '';

        return [
            $created,
            $row->user_id,
            $row->entity,
            $row->action,
            $row->method,
            $row->path,
        ];
    }
}
