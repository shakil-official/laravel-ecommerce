@extends('layouts.admin')
@section('title', 'Edit subcategory')
@section('subtitle', $subcategory->name)
@section('content')
    <div class="card" style="max-width: 760px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.subcategories.update', $subcategory) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.subcategories._form', ['submit' => 'Save changes'])
        </form>
    </div></div>
@endsection
