@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'  => 'CheckExportForm',
        'method' => 'get',
        'url' => route('admin.check.export_check_page')
    ]) }}

    <div class="form-group">
        {{ Form::label('id[]', '姓名') }}
        {{ Form::select('id[]', $options['name'], null, ['class' => 'form-control','multiple', 'id' => 'nameSelect']) }}
    </div>
    <div class="form-group form-inline">
        {{ Form::label('date-range', '時間範圍') }}
        {{ Form::text('date-range', null, ['class' => 'form-control']) }}
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
              <th scope="col">上班時間</th>
              <th scope="col">下班時間</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{$row['date']}}</td>
              <td>{{$row['name']}}</td>
              <td>{{$row['checkin_at']}}</td>
              <td>{{$row['checkout_at']}}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var check_export_form = $('#CheckExportForm');
            check_export_form.find('input[name="date-range"]').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            var validator = check_export_form.validate({
                errorClass: "alert alert-danger",
                rules: {
                    "id[]":   { required: true},
                },
                messages: {
                    "id[]":   {required: "請選擇姓名"},
                }
            });
            validator.showErrors();

            check_export_form.find('#nameSelect').multiselect({
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

        });
    </script>
@endsection
