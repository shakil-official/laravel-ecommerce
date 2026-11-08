@extends('layouts.app')
@section('title', 'Page not found')
@section('content')
    <div class="text-center py-5">
        <div class="display-1 fw-800 text-brand">404</div>
        <h1 class="h4 mb-2">We couldn't find that page</h1>
        <p class="text-muted mb-4">The product or page you are looking for may have been moved or deleted.</p>
        <a href="{{ route('shop.index') }}" class="btn btn-brand"><i class="bi bi-arrow-left"></i> Back to shop</a>
    </div>
@endsection
