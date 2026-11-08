@php
    $selectedCategory = old('category_id', $product->category_id);
    $selectedSub = old('subcategory_id', $product->subcategory_id);
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Select category —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="subcategory_id" class="form-label">Subcategory <span class="text-danger">*</span></label>
        <select id="subcategory_id" name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" required
                data-selected="{{ $selectedSub }}">
            <option value="">— Select subcategory —</option>
        </select>
        @error('subcategory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="name" class="form-label">Product name <span class="text-danger">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="150"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea id="description" name="description" rows="5" maxlength="5000"
                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="old_price" class="form-label">Old price <span class="text-danger">*</span></label>
        <div class="input-group has-validation">
            <span class="input-group-text">{{ config('shop.currency') }}</span>
            <input type="number" step="0.01" min="0" id="old_price" name="old_price" value="{{ old('old_price', $product->old_price) }}"
                   class="form-control @error('old_price') is-invalid @enderror" required>
            @error('old_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <label for="new_price" class="form-label">New price <span class="text-danger">*</span></label>
        <div class="input-group has-validation">
            <span class="input-group-text">{{ config('shop.currency') }}</span>
            <input type="number" step="0.01" min="0" id="new_price" name="new_price" value="{{ old('new_price', $product->new_price) }}"
                   class="form-control @error('new_price') is-invalid @enderror" required>
            @error('new_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-text">Must be less than or equal to the old price.</div>
    </div>
    <div class="col-12">
        <label for="image" class="form-label">Product image @unless ($product->exists)<span class="text-danger">*</span>@endunless</label>
        <input type="file" id="image" name="image" accept="image/*"
               class="form-control @error('image') is-invalid @enderror" @unless ($product->exists) required @endunless>
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">{{ strtoupper(implode(', ', config('shop.image.mimes'))) }} · max {{ config('shop.image.max_kb') / 1024 }} MB
            @if ($product->exists) · leave empty to keep the current image @endif</div>
        <div class="mt-2">
            <img id="image-preview" src="{{ $product->exists ? $product->image_url : '' }}" alt="Preview"
                 class="rounded border {{ $product->exists ? '' : 'd-none' }}" style="max-height:160px">
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> {{ $submit }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-ink">Cancel</a>
</div>

@push('scripts')
<script>
    (function () {
        // Dependent dropdown: only show subcategories of the chosen category.
        var subcategories = @json($subcategories);
        var category = document.getElementById('category_id');
        var sub = document.getElementById('subcategory_id');

        function fill(selected) {
            sub.innerHTML = '<option value="">— Select subcategory —</option>';
            subcategories
                .filter(function (s) { return String(s.category_id) === category.value; })
                .forEach(function (s) {
                    var o = document.createElement('option');
                    o.value = s.id; o.textContent = s.name;
                    if (String(s.id) === String(selected)) { o.selected = true; }
                    sub.appendChild(o);
                });
        }
        category.addEventListener('change', function () { fill(''); });
        fill(sub.dataset.selected);

        // Live image preview.
        var file = document.getElementById('image');
        var preview = document.getElementById('image-preview');
        file.addEventListener('change', function () {
            if (file.files && file.files[0]) {
                preview.src = URL.createObjectURL(file.files[0]);
                preview.classList.remove('d-none');
            }
        });
    })();
</script>
@endpush
