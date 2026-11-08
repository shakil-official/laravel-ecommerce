@if (session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i> <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i> <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-octagon-fill fs-5"></i> <div><strong>Please fix the highlighted fields.</strong></div>
    </div>
@endif
