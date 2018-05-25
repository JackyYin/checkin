@extends('admin.layouts.master')

@section('content')
    <div class="table-responsive" id="staffIndex">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">姓名</th>
              <th scope="col">email</th>
              <th scope="col">員工編號</th>
              <th scope="col">權限</th>
              <th scope="col">啟用狀態</th>
              <th scope="col">訂閱狀態</th>
              <th scope="col">動作</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($staffs as $staff)
            <tr data-id="{{$staff->id}}">
              <td>{{$staff->name}}</td>
              <td>{{$staff->email}}</td>
              <td>{{$staff->staff_code}}</td>
              <td class="authority">
                @if ($staff->admin)
                    Admin
                @elseif ($staff->manager)
                    管理者
                @else
                    一般
                @endif
              </td>
              <td>
                {!! strtr($staff->active, [
                   '1' => '<span class="badge badge-success">已啟用</span>',
                   '0' => '<span class="badge badge-warning">未啟用</span>']) !!}
              </td>
              <td>
                {!! strtr($staff->subscribed, [
                   '1' => '<span class="badge badge-success">已訂閱</span>',
                   '0' => '<span class="badge badge-warning">未訂閱</span>']) !!}
              </td>
              <td>
                <a class="btn btn-secondary" href="{{ route('admin.staff.edit', ['staff' => $staff->id]) }}">編輯</a>
                @if ($admin && !$staff->admin && !$staff->manager)
                  <a class="btn btn-secondary btn-assign-manager"  href="#">指派管理員</a>
                @endif
                <a class="btn btn-secondary" href="#">訂閱</a>
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
            var staff_index = $('#staffIndex');

            var btn_assign_manager = staff_index.find('.btn-assign-manager');
            btn_assign_manager.click(function () {
                var assign_btn = $(this);
                var staff_id = $(this).closest('tr').data('id');
                $.ajax({
                    type: 'post',
                    url: '{{ route('admin.manager.assign') }}',
                    data: {
                        staff_id: staff_id,
                    }, 
                })
                .done(function (result) {
                    console.log(result);
                    //alert message
                    $('.container').prepend('<div id="alert-message" class="alert alert-success">' + result + '</div>');
                    setTimeout(function() { $('#alert-message').remove();}, 3000);
                    //change html content
                    assign_btn.parent().siblings('.authority').html('管理者');
                })
                .fail(function (result) {
                    console.log(result.responseText);
                    $('.container').prepend('<div id="alert-message" class="alert alert-danger">' + result.responseText + '</div>');
                    setTimeout(function() { $('#alert-message').remove();}, 3000);
                });
            }); 
        });
    </script>
@endsection
