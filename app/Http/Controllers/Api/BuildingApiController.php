<?php

namespace App\Http\Controllers\Api;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildingApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Building::query();

        // DataTables search
        $search = null;
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
        } elseif ($request->filled('search') && !is_array($request->input('search'))) {
            $search = $request->input('search');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $query->with(['rooms.laboratories' => function ($q) {
            $q->select('id', 'room_id', 'name');
        }]);

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $page = ($start / $limit) + 1;
            $buildings = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($buildings, 'Data gedung berhasil dimuat');
        }

        $buildings = $query->orderBy('name')->get();
        return $this->sendSuccess($buildings, 'Data gedung berhasil dimuat');
    }

    public function show($id)
    {
        $building = Building::with('rooms')->find($id);

        if (!$building) {
            return $this->sendError('Gedung tidak ditemukan');
        }

        return $this->sendSuccess($building, 'Detail gedung berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:buildings,code',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $building = Building::create($request->all());

        return $this->sendSuccess($building, 'Gedung berhasil dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $building = Building::find($id);

        if (!$building) {
            return $this->sendError('Gedung tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:buildings,code,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $building->update($request->all());

        return $this->sendSuccess($building, 'Gedung berhasil diperbarui');
    }

    public function destroy($id)
    {
        $building = Building::find($id);

        if (!$building) {
            return $this->sendError('Gedung tidak ditemukan');
        }

        if ($building->rooms()->count() > 0) {
            return $this->sendError('Gedung tidak dapat dihapus karena masih memiliki ruangan');
        }

        $building->delete();

        return $this->sendSuccess(null, 'Gedung berhasil dihapus');
    }
}
