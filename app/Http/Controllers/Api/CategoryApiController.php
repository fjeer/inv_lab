<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\EquipmentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = EquipmentCategory::query();
        $this->applyTrashedFilter($query, $request);

        $search = $this->getSearch($request);

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $this->getPerPage($request);
        $page = $this->getPageFromRequest($request);

        $cats = $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);

        return $this->sendPaginated($cats, 'Data kategori berhasil dimuat');
    }

    public function show(EquipmentCategory $category): JsonResponse
    {
        return $this->sendSuccess(
            CategoryResource::make($category),
            'Detail ditemukan',
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $cat = EquipmentCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return $this->sendSuccess(
            CategoryResource::make($cat),
            'Kategori dibuat',
            201,
        );
    }

    public function update(UpdateCategoryRequest $request, EquipmentCategory $category): JsonResponse
    {
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return $this->sendSuccess(
            CategoryResource::make($category),
            'Kategori diperbarui',
        );
    }

    public function destroy(EquipmentCategory $category): JsonResponse
    {
        if ($category->equipment()->exists()) {
            return $this->sendError('Kategori tidak dapat dihapus karena masih memiliki alat terdaftar');
        }

        $category->delete();

        return $this->sendSuccess(null, 'Kategori dihapus');
    }
}
