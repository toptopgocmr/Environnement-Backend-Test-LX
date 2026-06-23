<div>
    <label class="block text-slate-300 text-sm font-medium mb-2">Nom de la catégorie *</label>
    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
        class="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        style="background:#162035;border:1px solid #1E3A6A" placeholder="Ex: Littérature africaine">
</div>
<div>
    <label class="block text-slate-300 text-sm font-medium mb-2">Description</label>
    <textarea name="description" rows="3"
        class="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        style="background:#162035;border:1px solid #1E3A6A" placeholder="Description de la catégorie...">{{ old('description', $category->description ?? '') }}</textarea>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-slate-300 text-sm font-medium mb-2">Emoji / Icône</label>
        <input type="text" name="icon" value="{{ old('icon', $category->icon ?? '') }}"
            class="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            style="background:#162035;border:1px solid #1E3A6A" placeholder="📚">
    </div>
    <div>
        <label class="block text-slate-300 text-sm font-medium mb-2">Couleur</label>
        <input type="color" name="color" value="{{ old('color', $category->color ?? '#2563EB') }}"
            class="w-full h-12 rounded-xl cursor-pointer" style="background:#162035;border:1px solid #1E3A6A;padding:4px">
    </div>
</div>
<div class="flex items-center gap-3">
    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
        class="w-4 h-4 rounded">
    <label for="is_active" class="text-slate-300 text-sm">Catégorie active</label>
</div>
