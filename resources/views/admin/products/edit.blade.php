@extends('layouts.admin')
@section('title', 'Edit product')
@section('subtitle', $product->name)
@section('content')
    <div class="card" style="max-width: 900px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" novalidate>
            @csrf @method('PUT')
            @include('admin.products._form', ['submit' => 'Save changes'])
        </form>
    </div></div>
@endsection
