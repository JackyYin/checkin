@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'     => 'STExportForm',
        'method' => 'get',
        'url'    => route('admin.check.export_statistic')
    ]) }}

    <div class="form-group">
        {{ Form::label('id[]', '姓名') }}
        {{ Form::select('id[]', $options['name'], null, ['class' => 'form-control','multiple', 'id' => 'nameSelect']) }}
    </div>
    <div class="form-group">
        {{ Form::label('type[]', '類別') }}
        {{ Form::select('type[]', $options['type'], null, ['class' => 'form-control','multiple', 'id' => 'typeSelect']) }}
    </div>
    <div class="form-group form-inline">
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

    <button class="btn btn-primary" name="action_type" value="lookup" type="submit">查看</button>
    <button class="btn btn-primary" name="action_type" value="export" type="submit">匯出</button>
    {{ Form::close() }}

    <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">日期</th>
              <th scope="col">姓名</th>
              <th scope="col">事假時數</th>
              <th scope="col">特休時數</th>
              <th scope="col">公假時數</th>
              <th scope="col">病假時數</th>
              <th scope="col">Online時數</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{$row['date']}}</td>
              <td>{{$row['name']}}</td>
              <td>{{$row['personal_leave_time']}}</td>
              <td>{{$row['annual_leave_time']}}</td>
              <td>{{$row['official_leave_time']}}</td>
              <td>{{$row['sick_leave_time']}}</td>
              <td>{{$row['online_time']}}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var statistic_export_form = $('#STExportForm');
            statistic_export_form.find('input[name="date-range"]').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            var validator = statistic_export_form.validate({
                errorClass: "alert alert-danger",
                rules: {
                    "id[]":   { required: true},
                    "type[]": { required: true}
                },
                messages: {
                    "id[]":   {required: "請選擇姓名"},
                    "type[]": {required: "請選擇類型"},
                }
            });
            validator.showErrors();

            statistic_export_form.find('#nameSelect').multiselect({
                buttonClass: 'btn btn-outline-secondary',
                buttonText: function(options, select) {
                    if (options.length === 0) {
                        return '請選擇姓名';
                    }
                    else if (options.length > 5) {
                        return '已選擇超過五名';
                    }
                    else {
                        var labels = [];
                        options.each(function() {
                            if ($(this).attr('label') !== undefined) {
                                labels.push($(this).attr('label'));
                            }
                            else {
                                labels.push($(this).html());
                            }
                        });
                        return labels.join(', ') + '';
                    }
                },
                includeSelectAllOption: true,
                selectAllText: '全選',
                maxHeight: 400,
            });

            statistic_export_form.find('#typeSelect').multiselect({
                buttonClass: 'btn btn-outline-secondary',
                buttonText: function(options, select) {
                    if (options.length === 0) {
                       return '請選擇類別';
                    }
                    else if (options.length > 5) {
                        return '已選擇超過五類';
                    }
                    else {
                        var labels = [];
                        options.each(function() {
                            if ($(this).attr('label') !== undefined) {
                                labels.push($(this).attr('label'));
                            }
                            else {
                                labels.push($(this).html());
                            }
                        });
                        return labels.join(', ') + '';
                    }
                },
                includeSelectAllOption: true,
                selectAllText: '全選',
                maxHeight: 400,
            });
        });
    </script>
@endsection
