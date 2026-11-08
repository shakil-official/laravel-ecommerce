@extends('layouts.admin')
@section('title', 'Edit category')
@section('subtitle', $category->name)
@section('content')
    <div class="card" style="max-width: 760px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" novalidate>
            @csrf @method('PUT')
            @include('admin.categories._form', ['submit' => 'Save changes'])
        </form>
    </div></div>
@endsection
