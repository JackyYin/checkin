@extends('web.layouts.master')

@section('content')
    <div>
    @if ($checked)
        <a href={{ route('web.check.off') }} class="btn">下班</a>
    @else
        <a href={{ route('web.check.on') }} class="btn">上班</a>
    @endif
    </div>
@endsection
