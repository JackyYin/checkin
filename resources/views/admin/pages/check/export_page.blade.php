@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'  => 'checkExportForm',
        'url' => route('admin.check.export')
    ]) }}

    <div class="form-group">
        {{ Form::label('id', '姓名') }}
        {{ Form::select('id', $options['name'], null, ['class' => 'form-control']) }}
    </div>
    <div class="form-group">
        {{ Form::label('date-range', '時間範圍') }}
        {{ Form::text('date-range', null, ['class' => 'form-control']) }}
    </div>
    <div class="form-inline">
        <div class="form-check">
            {{ Form::checkbox("has[work_time]", 1, false, ['class' => 'form-check-input']) }}
            {{ Form::label('has[work_time]', '工作時間', ['class' => 'form-check-label']) }}
        </div>
        <div class="form-group">
            {{ Form::select("op[work_time]", $options['operators'], 2, ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::number('value[work_time]', 9, ['class' => 'form-control'])}}
        </div>
    </div>

    {{ Form::submit('送出', ['class' => 'btn btn-primary']) }}
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
