@extends('layouts.admin')
@section('title', 'New category')
@section('subtitle', 'Create a top-level category')
@section('content')
    <div class="card" style="max-width: 760px"><div class="card-body p-4">
        <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
            @csrf
            @include('admin.categories._form', ['submit' => 'Create category'])
        </form>
    </div></div>
@endsection
