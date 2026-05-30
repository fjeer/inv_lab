@section('title', 'Manajemen Role & Hak Akses')

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Manajemen Role & Hak Akses</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola role pengguna dan atur hak akses masing-masing role.</p>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Role
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session()->has('message'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('message') }}
    </div>
    @endif

    @if(session()->has('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Roles Grid --}}
    <div class="grid gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden" wire:key="role-{{ $role->id }}">
            <div class="p-5 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        @if($editingRoleId === $role->id)
                            <div class="flex flex-wrap gap-3">
                                <input type="text" wire:model="roleName" placeholder="Nama slug" class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                <input type="text" wire:model="roleDisplayName" placeholder="Label tampilan" class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                                <input type="text" wire:model="roleDescription" placeholder="Deskripsi (opsional)" class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        @else
                            <h3 class="text-lg font-bold text-slate-800">{{ $role->display_name }}</h3>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $role->name }}</p>
                            @if($role->description)
                            <p class="text-sm text-slate-500 mt-1">{{ $role->description }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($editingRoleId === $role->id)
                            <button wire:click="updateRole" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-semibold rounded-lg hover:bg-emerald-600 transition-colors">Simpan</button>
                            <button wire:click="cancelEdit" class="px-3 py-1.5 bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-300 transition-colors">Batal</button>
                        @else
                            <span class="text-xs text-slate-400">{{ $role->users->count() ?? 0 }} pengguna</span>
                            <button wire:click="editRole({{ $role->id }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Yakin ingin menghapus role ini?" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Permission checkboxes --}}
            <div class="p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Hak Akses (Permissions)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permissionGroups as $group => $perms)
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2 capitalize">{{ $group }}</p>
                        <div class="space-y-1.5">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2 cursor-pointer group" wire:key="perm-{{ $role->id }}-{{ $perm->id }}">
                                <input type="checkbox"
                                    wire:click="togglePermission({{ $role->id }}, {{ $perm->id }})"
                                    @checked($role->permissions->contains('id', $perm->id))
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition-colors" />
                                <span class="text-sm text-slate-600 group-hover:text-slate-800 transition-colors">{{ $perm->display_name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Create Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" wire:click.self="$set('showCreateModal', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Buat Role Baru</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Slug</label>
                    <input type="text" wire:model="roleName" placeholder="contoh: kepala_lab" class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @error('roleName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Label Tampilan</label>
                    <input type="text" wire:model="roleDisplayName" placeholder="contoh: Kepala Lab" class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @error('roleDisplayName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi (Opsional)</label>
                    <textarea wire:model="roleDescription" rows="2" placeholder="Deskripsi role..." class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700 mb-2">Hak Akses Awal</p>
                    <div class="max-h-48 overflow-y-auto space-y-1.5 border border-slate-200 rounded-xl p-3">
                        @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 cursor-pointer" wire:key="create-perm-{{ $perm->id }}">
                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->id }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <span class="text-sm text-slate-600">{{ $perm->display_name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 flex justify-end gap-3">
                <button wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</button>
                <button wire:click="createRole" class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Buat Role</button>
            </div>
        </div>
    </div>
    @endif
</div>
