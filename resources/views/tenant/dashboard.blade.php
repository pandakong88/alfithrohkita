@extends('layouts.tenant')

@section('content')
   <h1>Dashboard Tenant</h1>
   <p>Selamat datang, {{ auth()->user()->name }}</p>
   <p>Role: {{ auth()->user()->role }}</p>

   @if(auth()->user()->pondok)
       <p>Pondok: {{ auth()->user()->pondok->name }}</p>
   @endif
@endsection