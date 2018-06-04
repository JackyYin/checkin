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
        <table id="STexportTable" class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">日期</th>
              <th scope="col">姓名</th>
              <th class="type-1" scope="col">事假時數</th>
              <th class="type-2" scope="col">特休時數</th>
              <th class="type-3" scope="col">公假時數</th>
              <th class="type-4" scope="col">病假時數</th>
              <th class="type-5" scope="col">Online時數</th>
              <th class="type-7" scope="col">喪假時數</th>
              <th class="type-8" scope="col">產假時數</th>
              <th class="type-9" scope="col">陪產假時數</th>
              <th class="type-10" scope="col">婚假時數</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{$row['date']}}</td>
              <td>{{$row['name']}}</td>
              <td class="type-1">
              {{ isset($row['personal_leave_time']) ? $row['personal_leave_time'] : "" }}
              </td>
              <td class="type-2">
              {{ isset($row['annual_leave_time']) ? $row['annual_leave_time'] : "" }}
              </td>
              <td class="type-3">
              {{ isset($row['official_leave_time']) ? $row['official_leave_time'] : "" }}
              </td>
              <td class="type-4">
              {{ isset($row['sick_leave_time']) ? $row['sick_leave_time'] : "" }}
              </td>
              <td class="type-5">
              {{ isset($row['online_time']) ? $row['online_time'] : "" }}
              </td>
              <td class="type-7">
              {{ isset($row['mourning_leave_time']) ? $row['mourning_leave_time'] : "" }}
              </td>
              <td class="type-8">
              {{ isset($row['maternity_leave_time']) ? $row['maternity_leave_time'] : "" }}
              </td>
              <td class="type-9">
              {{ isset($row['paternity_leave_time']) ? $row['paternity_leave_time'] : "" }}
              </td>
              <td class="type-10">
              {{ isset($row['marriage_leave_time']) ? $row['marriage_leave_time'] : "" }}
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var form = $('#STExportForm');
            form.find('input[name="date-range"]').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            var validator = form.validate({
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

            form.find('#nameSelect').multiselect({
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

            form.find('#typeSelect').multiselect({
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

            //根據所選假別調整table
            var table = $('#STexportTable');
            var all_types = {!! json_encode(array_divide($options['type'])[0]) !!}
            var visible_types = {!! json_encode(request()->query('type')) !!}.map(function (item) { return parseInt(item,10); }) ;
            console.log('all_types',all_types);
            console.log('visible_types',visible_types);
            for (i = 0; i< all_types.length; i++) {
                if (visible_types.indexOf(all_types[i]) <= -1) {
                    var head = table.find('thead').find('.type-' + all_types[i]);
                    var body = table.find('tbody').find('.type-' + all_types[i]);
                    head.css('display','none');
                    body.css('display','none');
                }
            }
        });
    </script>
@endsection
