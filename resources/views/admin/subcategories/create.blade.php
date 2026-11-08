@extends('layouts.admin')
@section('title', 'New subcategory')
@section('subtitle', 'Create a subcategory under an existing category')
@section('content')
    <div class="card" style="max-width: 760px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.subcategories.store') }}" novalidate>
            @csrf
            @include('admin.subcategories._form', ['submit' => 'Create subcategory'])
        </form>
    </div></div>
@endsection
