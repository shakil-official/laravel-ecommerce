{{-- All frontend assets are served locally (no CDN needed) --}}
<link rel="preload" href="{{ asset('vendor/fonts/inter/inter-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
<link href="{{ asset('vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}?v=3" rel="stylesheet">
<script>
    // If an uploaded image file is missing, show the placeholder instead of a broken icon.
    document.addEventListener('error', function (e) {
        var img = e.target;
        if (img && img.tagName === 'IMG' && ! img.dataset.fallback) {
            img.dataset.fallback = '1';
            img.src = '{{ asset('images/placeholder.svg') }}';
        }
    }, true);
</script>
