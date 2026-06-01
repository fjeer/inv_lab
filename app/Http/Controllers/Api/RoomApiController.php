<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Room::with('building');
        $this->applyTrashedFilter($query, $request);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

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

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $limit = max($limit, 1); $page = (int)($start / $limit) + 1;
            $rooms = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($rooms, 'Data ruangan berhasil dimuat');
        }

        $rooms = $query->orderBy('name')->get();

        return $this->sendSuccess($rooms, 'Data ruangan berhasil dimuat');
    }

    public function show($id)
    {
        $room = Room::withTrashed()->with(['building', 'laboratories'])->find($id);

        if (!$room) {
            return $this->sendError('Ruangan tidak ditemukan');
        }

        return $this->sendSuccess($room, 'Detail ruangan berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:rooms,code',
            'floor' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $room = Room::create($request->all());

        return $this->sendSuccess($room->load('building'), 'Ruangan berhasil dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->sendError('Ruangan tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:rooms,code,' . $id,
            'floor' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $room->update($request->all());

        return $this->sendSuccess($room->load('building'), 'Ruangan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return $this->sendError('Ruangan tidak ditemukan');
        }

        if ($room->laboratories()->count() > 0) {
            return $this->sendError('Ruangan tidak dapat dihapus karena masih memiliki laboratorium');
        }

        $room->delete();

        return $this->sendSuccess(null, 'Ruangan berhasil dihapus');
    }
}
