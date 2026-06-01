<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class RoleManagement extends Component
{
    public $roles;
    public $permissions;

    // Remove $permissionGroups from public property to avoid serialization issues
    // We will compute it in render or as a computed property

    // Form state
    public $editingRoleId = null;
    public $roleName = '';
    public $roleDisplayName = '';
    public $roleDescription = '';
    public $selectedPermissions = [];

    // Create modal
    public $showCreateModal = false;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->roles = Role::with('permissions')->orderBy('id')->get();
        $this->permissions = Permission::orderBy('group')->orderBy('display_name')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function createRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:50|unique:roles,name',
            'roleDisplayName' => 'required|string|max:100',
        ]);

        $role = Role::create([
            'name' => $this->roleName,
            'display_name' => $this->roleDisplayName,
            'description' => $this->roleDescription,
        ]);

        if (! empty($this->selectedPermissions)) {
            $role->permissions()->sync($this->selectedPermissions);
        }

        $this->showCreateModal = false;
        $this->resetForm();
        $this->loadData();

        $this->dispatch('swal', title: 'Berhasil!', text: 'Role berhasil dibuat.', icon: 'success');
    }

    public function editRole(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->editingRoleId = $roleId;
        $this->roleName = $role->name;
        $this->roleDisplayName = $role->display_name;
        $this->roleDescription = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    public function updateRole(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:50|unique:roles,name,' . $this->editingRoleId,
            'roleDisplayName' => 'required|string|max:100',
        ]);

        $role = Role::findOrFail($this->editingRoleId);
        $role->update([
            'name' => $this->roleName,
            'display_name' => $this->roleDisplayName,
            'description' => $this->roleDescription,
        ]);

        $role->permissions()->sync($this->selectedPermissions);

        $this->editingRoleId = null;
        $this->resetForm();
        $this->loadData();

        $this->dispatch('swal', title: 'Berhasil!', text: 'Role berhasil diperbarui.', icon: 'success');
    }

    public function cancelEdit(): void
    {
        $this->editingRoleId = null;
        $this->resetForm();
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->users()->count() > 0) {
            $this->dispatch('swal', title: 'Gagal', text: 'Role tidak bisa dihapus karena masih digunakan oleh pengguna.', icon: 'error');
            return;
        }

        $role->delete();
        $this->loadData();

        $this->dispatch('swal', title: 'Berhasil!', text: 'Role berhasil dihapus.', icon: 'success');
    }

    public function togglePermission(int $roleId, int $permissionId): void
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->toggle($permissionId);
        $this->loadData();
    }

    private function resetForm(): void
    {
        $this->roleName = '';
        $this->roleDisplayName = '';
        $this->roleDescription = '';
        $this->selectedPermissions = [];
    }

    public function render()
    {
        return view('livewire.admin.role-management', [
            'permissionGroups' => $this->permissions->groupBy('group')
        ])->layout('layouts.app');
    }
}
