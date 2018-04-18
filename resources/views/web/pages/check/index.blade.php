@extends('web.layouts.master')

@section('content')
    <div>
    @if ($checked)
        <a class="btn">下班</a>
    @else
        <a class="btn">上班</a>
    @endif
    </div>
@endsection
