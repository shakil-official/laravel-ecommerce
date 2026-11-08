{{-- Pagination footer for admin tables: "Showing 1–10 of 54", per-page selector, page links --}}
@if ($paginator->total() > 0)
    <div class="table-footer">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span>Showing <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong></span>
            <form method="GET" class="d-flex align-items-center gap-2">
                @foreach (request()->except(['per_page', 'page']) as $key => $value)
                    @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                @endforeach
                <label for="per_page" class="mb-0">Rows</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    @foreach (\App\Http\Controllers\Controller::PER_PAGE_OPTIONS as $option)
                        <option value="{{ $option }}" @selected($paginator->perPage() === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @if ($paginator->hasPages())
            {{ $paginator->links() }}
        @endif
    </div>
@endif
