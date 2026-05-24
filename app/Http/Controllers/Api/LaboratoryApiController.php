<?php

namespace App\Http\Controllers\Api;

use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaboratoryApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Laboratory::with(['room.building', 'responsiblePerson']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('location', 'like', '%' . $search . '%')
                  ->orWhereHas('room', function ($sq) use ($search) {
                      $sq->where('name', 'like', '%' . $search . '%')
                         ->orWhereHas('building', function ($ssq) use ($search) {
                             $ssq->where('name', 'like', '%' . $search . '%');
                         });
                  });
            });
        }

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $page = ($start / $limit) + 1;
            $labs = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($labs, 'Data laboratorium berhasil dimuat');
        }

        $labs = $query->orderBy('name')->get();

        return $this->sendSuccess($labs, 'Data laboratorium berhasil dimuat');
    }

    public function show($id)
    {
        $lab = Laboratory::with(['room.building', 'equipment.category', 'schedules.user', 'responsiblePerson'])->find($id);

        if (!$lab) {
            return $this->sendError('Laboratorium tidak ditemukan');
        }

        return $this->sendSuccess($lab, 'Detail laboratorium berhasil dimuat');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories,code',
            'room_id' => 'required|exists:rooms,id|unique:laboratories,room_id',
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'responsible_person_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $lab = Laboratory::create($request->all());

        return $this->sendSuccess($lab, 'Laboratorium berhasil dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $lab = Laboratory::find($id);

        if (!$lab) {
            return $this->sendError('Laboratorium tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:laboratories,code,' . $id,
            'room_id' => 'required|exists:rooms,id|unique:laboratories,room_id,' . $id,
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'responsible_person_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $lab->update($request->all());

        return $this->sendSuccess($lab, 'Laboratorium berhasil diperbarui');
    }

    public function destroy($id)
    {
        $lab = Laboratory::find($id);

        if (!$lab) {
            return $this->sendError('Laboratorium tidak ditemukan');
        }

        // Check if laboratory has equipment
        if ($lab->equipment()->count() > 0) {
            return $this->sendError('Laboratorium tidak dapat dihapus karena masih memiliki alat');
        }

        $lab->delete();

        return $this->sendSuccess(null, 'Laboratorium berhasil dihapus');
    }
}
