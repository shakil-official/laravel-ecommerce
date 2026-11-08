<div class="mb-3">
    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
           class="form-control @error('name') is-invalid @enderror" maxlength="100" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="4" maxlength="1000"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> {{ $submit }}</button>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-ink">Cancel</a>
</div>
