@extends('layout')
@section('content')
    <input type="hidden" id="csrf_token" value="{{ csrf_token() }}" />
    <Trello></Trello>
@stop