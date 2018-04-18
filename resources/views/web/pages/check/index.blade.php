@extends('web.layouts.master')

@section('content')
    <div>
    @if ($checked_in && !$checked_out)
        <a href={{ route('web.check.off') }} class="btn">下班</a>
    @elseif (!$checked_in)
        <a href={{ route('web.check.on') }} class="btn">上班</a>
    @elseif ($checked_out)
        <h1>還沒到上班時間</h1>
    @endif
    </div>
@endsection
