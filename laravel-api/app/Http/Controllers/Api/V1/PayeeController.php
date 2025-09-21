<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PayeeStoreRequest;
use App\Http\Requests\Api\V1\PayeeUpdateRequest;
use App\Http\Resources\PayeeResource;
use App\Models\Payee;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PayeeController extends Controller
{
    /**
     * GET /api/v1/payees
     * Query:
     *  - q?: string (search id_number, firstname, lastname, email)
     *  - page?: int
     *  - perPage?: int (<= 100)
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('perPage', 25)));

        $qb = Payee::query();

        if ($q !== '') {
            $qb->where(function ($w) use ($q) {
                $w->where('id_number', 'like', '%' . $q . '%')
                    ->orWhere('firstname', 'like', '%' . $q . '%')
                    ->orWhere('lastname', 'like', '%' . $q . '%')
                    ->orWhere('middlename', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        $total = (clone $qb)->count();
        $rows = $qb->orderBy('id', 'desc')
            ->forPage($page, $perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => PayeeResource::collection($rows),
            'meta'    => [
                'page'    => $page,
                'perPage' => $perPage,
                'total'   => $total,
            ],
        ]);
    }

    /**
     * GET /api/v1/payees/{id}
     */
    public function show($id)
    {
        $row = Payee::find((int) $id);
        if (!$row) {
            abort(404);
        }
        return response()->json([
            'success' => true,
            'data'    => new PayeeResource($row),
        ]);
    }

    /**
     * POST /api/v1/payees
     */
    public function store(PayeeStoreRequest $request)
    {
        $payload = $request->validated();

        // Enforce unique id_number at DB level too (race-safe)
        $exists = Payee::query()->where('id_number', $payload['id_number'])->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'id_number' => ['ID number already exists']
            ]);
        }

        $row = null;
        DB::transaction(function () use (&$row, $payload) {
            $row = Payee::create($payload);
        });

        // System log
        try {
            SystemLogService::log('create', 'Payee', (int) $row->id, null, $row->toArray(), $request);
        } catch (\Throwable $e) {
            // ignore log failures
        }

        return response()->json([
            'success' => true,
            'data'    => ['id' => (int) $row->id],
        ], 201);
    }

    /**
     * PATCH /api/v1/payees/{id}
     */
    public function update($id, PayeeUpdateRequest $request)
    {
        $row = Payee::find((int) $id);
        if (!$row) {
            abort(404);
        }

        $payload = $request->validated();

        // Protect id_number uniqueness in race conditions
        $dup = Payee::query()
            ->where('id_number', $payload['id_number'])
            ->where('id', '<>', (int) $row->id)
            ->exists();
        if ($dup) {
            throw ValidationException::withMessages([
                'id_number' => ['ID number already exists']
            ]);
        }

        $old = $row->toArray();

        DB::transaction(function () use ($row, $payload) {
            $row->fill($payload);
            $row->save();
        });

        // System log
        try {
            SystemLogService::log('update', 'Payee', (int) $row->id, $old, $row->toArray(), $request);
        } catch (\Throwable $e) {
            // ignore log failures
        }

        return response()->json([
            'success' => true,
            'data'    => ['id' => (int) $row->id],
        ]);
    }

    /**
     * DELETE /api/v1/payees/{id}
     * Policy: Set payment_details.payee_id to NULL for related rows, then delete payee.
     */
    public function destroy($id, Request $request)
    {
        $row = Payee::find((int) $id);
        if (!$row) {
            abort(404);
        }

        $old = $row->toArray();

        DB::transaction(function () use ($row) {
            if (Schema::hasTable('payment_details') && Schema::hasColumn('payment_details', 'payee_id')) {
                DB::table('payment_details')->where('payee_id', (int) $row->id)->update(['payee_id' => null]);
            }
            $row->delete();
        });

        // System log
        try {
            SystemLogService::log('delete', 'Payee', (int) $row->id, $old, null, $request);
        } catch (\Throwable $e) {
            // ignore log failures
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
