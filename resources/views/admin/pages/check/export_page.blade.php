@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'  => 'checkExportForm',
        'url' => route('admin.check.export')
    ]) }}

    {{ Form::label('id', '姓名') }}
    {{ Form::select('id', $options['name']) }}
    {{ Form::label('date-range', '時間範圍') }}
    {{ Form::text('date-range') }}
    {{ Form::checkbox("has[work_time]", 1) }}
    {{ Form::label('work_time', '工作時間') }}
    {{ Form::select("op[work_time]", $options['operators'], 2) }}
    {{ Form::number('value[work_time]', 9)}}

    {{ Form::submit('送出') }}
    {{ Form::close() }}
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var check_export_form = $('#checkExportForm');
            check_export_form.find('input[name="date-range"]').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }); 
        });
    </script>
@endsection
