@extends('layouts.superadmin')

@section('content')
   <h1>Dashboard Super Admin</h1>
   <p>Selamat datang, {{ auth()->user()->name }}</p>
   <p>Role: {{ auth()->user()->role }}</p>
@endsection