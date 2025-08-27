<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GradingSystemStoreRequest;
use App\Http\Requests\Api\V1\GradingSystemUpdateRequest;
use App\Http\Requests\Api\V1\GradingItemStoreRequest;
use App\Http\Requests\Api\V1\GradingItemsBulkStoreRequest;
use App\Models\GradingSystem;
use App\Models\GradingItem;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingSystemController extends Controller
{
    /**
     * GET /api/v1/grading-systems
     * Returns all grading systems with item counts.
     */
    public function index(Request $request): JsonResponse
    {
        $systems = GradingSystem::query()
            ->withCount('items')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $systems,
        ]);
    }

    /**
     * GET /api/v1/grading-systems/{id}
     * Returns a grading system with items (ordered by value ASC).
     */
    public function show(int $id): JsonResponse
    {
        $system = GradingSystem::find($id);
        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system not found',
            ], 404);
        }

        $items = $system->items()->orderBy('value', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'system' => $system,
                'items' => $items,
            ],
        ]);
    }

    /**
     * POST /api/v1/grading-systems
     * Body: { name }
     */
    public function store(GradingSystemStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $system = GradingSystem::create([
            'name' => $data['name'],
        ]);

        SystemLogService::log('create', 'GradingSystem', $system->getKey(), null, $system->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $system,
        ], 201);
    }

    /**
     * PUT /api/v1/grading-systems/{id}
     * Body: { name }
     */
    public function update(GradingSystemUpdateRequest $request, int $id): JsonResponse
    {
        $system = GradingSystem::find($id);
        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system not found',
            ], 404);
        }

        $old = $system->toArray();
        $system->update($request->validated());
        $new = $system->fresh();

        SystemLogService::log('update', 'GradingSystem', $system->getKey(), $old, $new->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $new,
        ]);
    }

    /**
     * DELETE /api/v1/grading-systems/{id}
     * Prevent deletion if used by any subject in tb_mas_subjects.grading_system_id or grading_system_id_midterm
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $system = GradingSystem::find($id);
        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system not found',
            ], 404);
        }

        $inUse = DB::table('tb_mas_subjects')
            ->where('grading_system_id', $id)
            ->orWhere('grading_system_id_midterm', $id)
            ->exists();

        if ($inUse) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system is in use and cannot be deleted',
            ], 409);
        }

        $old = $system->toArray();

        DB::transaction(function () use ($system) {
            // delete items then the system
            GradingItem::where('grading_id', $system->getKey())->delete();
            $system->delete();
        });

        SystemLogService::log('delete', 'GradingSystem', $id, $old, null, $request);

        return response()->json([
            'success' => true,
            'message' => 'Grading system deleted',
        ]);
    }

    /**
     * POST /api/v1/grading-systems/{id}/items/bulk
     * Body: { items: [ { value, remarks }, ... ] }
     */
    public function addItemsBulk(GradingItemsBulkStoreRequest $request, int $id): JsonResponse
    {
        $system = GradingSystem::find($id);
        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system not found',
            ], 404);
        }

        $payload = $request->validated();
        $items = $payload['items'];

        // detect duplicates within request by value
        $values = array_map(function ($it) {
            return (string)$it['value'];
        }, $items);
        $dupeValues = array_diff_key($values, array_unique($values));
        if (!empty($dupeValues)) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate values found in request payload',
            ], 422);
        }

        $created = [];
        DB::transaction(function () use ($items, $system, &$created) {
            foreach ($items as $it) {
                // enforce per-grading uniqueness on (grading_id, value)
                $exists = GradingItem::where('grading_id', $system->getKey())
                    ->where('value', $it['value'])
                    ->exists();
                if ($exists) {
                    // skip existing duplicates (idempotent bulk add)
                    continue;
                }
                $created[] = GradingItem::create([
                    'grading_id' => $system->getKey(),
                    'value' => $it['value'],
                    'remarks' => $it['remarks'],
                ]);
            }
        });

        SystemLogService::log('create', 'GradingItemsBulk', $system->getKey(), null, [
            'count' => count($created),
            'items' => array_map(function ($m) {
                return $m->toArray();
            }, $created),
        ], $request);

        return response()->json([
            'success' => true,
            'data' => [
                'created' => array_map(function ($m) {
                    return $m->toArray();
                }, $created),
            ],
        ], 201);
    }

    /**
     * POST /api/v1/grading-systems/{id}/items
     * Body: { value, remarks }
     */
    public function addItem(GradingItemStoreRequest $request, int $id): JsonResponse
    {
        $system = GradingSystem::find($id);
        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Grading system not found',
            ], 404);
        }

        $data = $request->validated();

        $exists = GradingItem::where('grading_id', $system->getKey())
            ->where('value', $data['value'])
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A grading item with the same value already exists',
            ], 409);
        }

        $item = GradingItem::create([
            'grading_id' => $system->getKey(),
            'value' => $data['value'],
            'remarks' => $data['remarks'],
        ]);

        SystemLogService::log('create', 'GradingItem', $item->getKey(), null, $item->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $item,
        ], 201);
    }

    /**
     * DELETE /api/v1/grading-systems/items/{itemId}
     */
    public function deleteItem(Request $request, int $itemId): JsonResponse
    {
        $item = GradingItem::find($itemId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Grading item not found',
            ], 404);
        }

        $old = $item->toArray();
        $item->delete();

        SystemLogService::log('delete', 'GradingItem', $itemId, $old, null, $request);

        return response()->json([
            'success' => true,
            'message' => 'Grading item deleted',
        ]);
    }
}
