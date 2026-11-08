<div class="mb-3">
    <label for="category_id" class="form-label">Parent category <span class="text-danger">*</span></label>
    <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="">— Select category —</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $subcategory->category_id) === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name" value="{{ old('name', $subcategory->name) }}"
           class="form-control @error('name') is-invalid @enderror" maxlength="100" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="4" maxlength="1000"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $subcategory->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> {{ $submit }}</button>
    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-ink">Cancel</a>
</div>
