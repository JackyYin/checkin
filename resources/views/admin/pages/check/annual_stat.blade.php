@extends('admin.layouts.master')

@section('content')
    {{ Form::open([
        'id'  => 'AnnualStatForm',
        'method' => 'get',
        'url' => route('admin.check.annual_stat')
    ]) }}

    <div class="form-group">
        {{ Form::label('id[]', '姓名') }}
        {{ Form::select('id[]', $options['name'], null, ['class' => 'form-control','multiple', 'id' => 'nameSelect']) }}
    </div>

    <button class="btn btn-primary" name="action_type" value="lookup" type="submit">查看</button>
    {{ Form::close() }}

    <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">姓名</th>
              <th scope="col">可用特休時數</th>
              <th scope="col">已用特休時數</th>
              <th scope="col">剩下特休時數</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rows as $row)
            <tr>
              <td>{{$row['name']}}</td>
              <td>{{$row['usable']}}</td>
              <td>{{$row['used']}}</td>
              <td>{{$row['remained']}}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready( function () {
            var AnnualStatForm = $('#AnnualStatForm');

            var validator = AnnualStatForm.validate({
                errorClass: "alert alert-danger",
                rules: {
                    "id[]":   { required: true},
                },
                messages: {
                    "id[]":   {required: "請選擇姓名"},
                }
            });
            validator.showErrors();

            AnnualStatForm.find('#nameSelect').multiselect({
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
