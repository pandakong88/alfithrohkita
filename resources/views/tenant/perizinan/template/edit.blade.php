@extends('layouts.superadmin')

@section('title', 'Edit Template Perizinan')

@section('content')

<div class="container">

    <h2>Edit Template Perizinan</h2>

    <form method="POST" action="{{ route('tenant.template-perizinan.update',$template->id) }}">

        @csrf
        @method('PUT')

        @include('tenant.perizinan.template._form')

        <br>

        <button type="submit">
            Update
        </button>

    </form>

</div>

@endsection