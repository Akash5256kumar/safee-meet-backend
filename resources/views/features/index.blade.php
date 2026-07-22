@extends('layouts.app')

@section('title', 'Plan Features')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div class="md:p-6"
     x-data="{
        showCreate: {{ $errors->any() ? 'true' : 'false' }},
        showEdit: false,
        editing: { id: null, name: '', type: 'boolean', group: '', description: '', is_active: 1 },
        openEdit(f) { this.editing = f; this.showEdit = true; }
     }">

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Plan Features</h1>
            <p class="mt-1 text-sm text-gray-400">{{ $features->count() }} features · {{ $plans->count() }} active plans</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="self-start rounded-lg bg-[#DC131C] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#b50f16] sm:self-auto">
            + Add Feature
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Feature master list ─────────────────────────────────────────── --}}
    <div class="mb-8 overflow-x-auto rounded-xl border border-[#2a2d3e] bg-black">
        <table class="w-full min-w-[700px] border-collapse text-[13px]">
            <thead>
                <tr class="border-b border-[#2a2d3e] text-left text-xs uppercase tracking-wide text-red-500">
                    <th class="px-5 py-4">Feature</th>
                    <th class="px-5 py-4">Group</th>
                    <th class="px-5 py-4">Type</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($features as $feature)
                    <tr class="border-b border-[#2a2d3e] last:border-b-0">
                        <td class="px-5 py-3 font-semibold text-white">{{ $feature->name }}
                            <span class="block text-[11px] font-normal text-gray-600">{{ $feature->slug }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-300">{{ $feature->group }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full bg-[#1b2230] px-3 py-1 text-xs text-gray-300">{{ $feature->type === 'limit' ? 'Limit (value)' : 'Yes / No' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-3 py-1 text-xs {{ $feature->is_active ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400' }}">
                                {{ $feature->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    @click="openEdit({ id: {{ $feature->id }}, name: @js($feature->name), type: @js($feature->type), group: @js($feature->group), description: @js($feature->description), is_active: {{ $feature->is_active ? 1 : 0 }} })"
                                    class="rounded-lg border border-blue-400 px-2.5 py-1 text-xs text-blue-400 transition hover:bg-blue-400 hover:text-white">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <form method="POST" action="{{ route('features.destroy', $feature) }}"
                                      onsubmit="return confirm('Delete this feature? It will be removed from all plans.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-500 px-2.5 py-1 text-xs text-red-400 transition hover:bg-red-500 hover:text-white">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No features yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Plan × Feature matrix ───────────────────────────────────────── --}}
    <div class="rounded-xl border border-[#2a2d3e] bg-black">
        <div class="flex items-center justify-between border-b border-[#2a2d3e] px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-white">Plan Feature Matrix</h2>
                <p class="text-xs text-gray-500">Tick a box for Yes/No features; type a value (e.g. "3", "Unlimited") for limit features.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('features.matrix') }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-[#2a2d3e] text-left text-xs uppercase tracking-wide text-red-500">
                            <th class="sticky left-0 z-10 bg-black px-5 py-4">Feature</th>
                            @foreach($plans as $plan)
                                <th class="px-4 py-4 text-center">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $groupName => $groupFeatures)
                            <tr class="bg-[#0d0f13]">
                                <td colspan="{{ $plans->count() + 1 }}" class="px-5 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $groupName }}</td>
                            </tr>
                            @foreach($groupFeatures as $feature)
                                <tr class="border-b border-[#2a2d3e]">
                                    <td class="sticky left-0 z-10 bg-black px-5 py-3 text-white">
                                        {{ $feature->name }}
                                        @if($feature->type === 'limit')
                                            <span class="ml-1 text-[10px] text-gray-600">(value)</span>
                                        @endif
                                    </td>
                                    @foreach($plans as $plan)
                                        @php $cell = $matrix[$plan->id][$feature->id] ?? null; @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($feature->type === 'limit')
                                                <input type="text"
                                                    name="matrix[{{ $plan->id }}][{{ $feature->id }}][value]"
                                                    value="{{ $cell['value'] ?? '' }}"
                                                    placeholder="—"
                                                    class="w-24 rounded-md border border-[#2a2d3e] bg-[#1a1a1a] px-2 py-1 text-center text-xs text-white outline-none focus:border-[#DC131C]">
                                            @else
                                                <input type="checkbox"
                                                    name="matrix[{{ $plan->id }}][{{ $feature->id }}][included]"
                                                    value="1"
                                                    @checked($cell['included'] ?? false)
                                                    class="h-4 w-4 cursor-pointer accent-[#DC131C]">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="{{ $plans->count() + 1 }}" class="px-5 py-8 text-center text-gray-500">Add features above to build the matrix.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($features->isNotEmpty())
                <div class="flex justify-end border-t border-[#2a2d3e] px-5 py-4">
                    <button type="submit" class="rounded-lg bg-[#DC131C] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#b50f16]">
                        Save Matrix
                    </button>
                </div>
            @endif
        </form>
    </div>

    {{-- ── Create Feature Modal ────────────────────────────────────────── --}}
    <div x-show="showCreate" x-cloak @click.self="showCreate = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-[#212529] bg-[#000] p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Add a feature</h3>
                <button type="button" @click="showCreate = false" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('features.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm text-gray-400">Name</label>
                    <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]" placeholder="Verified Badge Display">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Type</label>
                    <select name="type" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                        <option value="boolean" @selected(old('type', 'boolean') === 'boolean')>Yes / No</option>
                        <option value="limit" @selected(old('type') === 'limit')>Limit (value)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Group</label>
                    <input name="group" value="{{ old('group') }}" required list="feature-groups" class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]" placeholder="Safety">
                    <datalist id="feature-groups">
                        @foreach($groups->keys() as $g)<option value="{{ $g }}">@endforeach
                    </datalist>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm text-gray-400">Description <span class="text-gray-600">(optional)</span></label>
                    <input name="description" value="{{ old('description') }}" class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" @click="showCreate = false" class="rounded-lg border border-[#2a2d3e] px-4 py-2 text-sm font-semibold text-gray-300 transition hover:bg-[#2a2d3e]">Cancel</button>
                    <button type="submit" class="rounded-lg bg-[#DC131C] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#b50f16]">Add Feature</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Edit Feature Modal ──────────────────────────────────────────── --}}
    <div x-show="showEdit" x-cloak @click.self="showEdit = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-[#212529] bg-[#000] p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Edit feature</h3>
                <button type="button" @click="showEdit = false" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" :action="`{{ url('features') }}/${editing.id}`" class="grid gap-4 md:grid-cols-2">
                @csrf @method('PUT')
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm text-gray-400">Name</label>
                    <input name="name" x-model="editing.name" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Type</label>
                    <select name="type" x-model="editing.type" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                        <option value="boolean">Yes / No</option>
                        <option value="limit">Limit (value)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Group</label>
                    <input name="group" x-model="editing.group" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Status</label>
                    <select name="is_active" x-model="editing.is_active" required class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-gray-400">Description</label>
                    <input name="description" x-model="editing.description" class="w-full rounded-lg border border-[#2a2d3e] bg-[#1a1a1a] px-3 py-2 text-sm text-white outline-none focus:border-[#DC131C]">
                </div>
                <div class="md:col-span-2 flex justify-end gap-3">
                    <button type="button" @click="showEdit = false" class="rounded-lg border border-[#2a2d3e] px-4 py-2 text-sm font-semibold text-gray-300 transition hover:bg-[#2a2d3e]">Cancel</button>
                    <button type="submit" class="rounded-lg bg-[#DC131C] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#b50f16]">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
