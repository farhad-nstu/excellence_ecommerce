@extends('web.layout')
@section('content')
@php $r =   'web.carts.cart' . $final_theme['cart']; @endphp
@include($r)
@endsection
