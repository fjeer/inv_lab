<?php

namespace App\Http\Controllers\Api;

use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryApiController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = EquipmentCategory::query();
        $this->applyTrashedFilter($query, $request);

        // DataTables search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('length')) {
            $limit = $request->input('length', 10);
            $start = $request->input('start', 0);
            $limit = max($limit, 1); $page = (int)($start / $limit) + 1;
            $cats = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);
            return $this->sendPaginated($cats, 'Data dikumpulkan');
        }

        return $this->sendSuccess($query->orderBy('name')->get(), 'Data dikumpulkan');
    }

    public function show($id)
    {
        $cat = EquipmentCategory::withTrashed()->find($id);
        return $cat ? $this->sendSuccess($cat, 'Detail ditemukan') : $this->sendError('Not found');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:equipment_categories,name',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $cat = EquipmentCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description
        ]);
        return $this->sendSuccess($cat, 'Kategori dibuat', 201);
    }

    public function update(Request $request, $id)
    {
        $cat = EquipmentCategory::find($id);
        if (!$cat) {
            return $this->sendError('Kategori tidak ditemukan');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:equipment_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        $cat->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description
        ]);
        return $this->sendSuccess($cat, 'Kategori diperbarui');
    }

    public function destroy($id)
    {
        $cat = EquipmentCategory::find($id);
        if (!$cat) {
            return $this->sendError('Kategori tidak ditemukan');
        }

        if ($cat->equipment()->exists()) {
            return $this->sendError('Kategori tidak dapat dihapus karena masih memiliki alat terdaftar');
        }

        $cat->delete();
        return $this->sendSuccess(null, 'Kategori dihapus');
    }
}
