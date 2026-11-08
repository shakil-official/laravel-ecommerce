@extends('layouts.admin')
@section('title', 'New product')
@section('subtitle', 'Add a product under a category and subcategory')
@section('content')
    <div class="card" style="max-width: 900px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @include('admin.products._form', ['submit' => 'Create product'])
        </form>
    </div></div>
@endsection
