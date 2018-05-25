@extends('admin.layouts.master')

@section('content')
    <div class="table-responsive" id="staffIndex">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <form class="form-inline my-2 my-lg-0" action="{{ route('admin.staff.index') }}" method="get">
              <input class="form-control mr-sm-2" type="search" placeholder="姓名、email..." aria-label="Search" name="keyword">
              <button class="btn btn-outline-success my-2 my-sm-0" type="submit">搜尋</button>
            </form>
            <a class="btn btn-outline-secondary" href="{{ route('admin.staff.index') }}" style="margin-left: 10px">返回</a>
          </div>
        </nav>
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
              <td class="subscription">
                {!! strtr($staff->subscribed, [
                   '1' => '<span class="badge badge-success">已訂閱</span>',
                   '0' => '<span class="badge badge-warning">未訂閱</span>']) !!}
              </td>
              <td>
                <a class="btn btn-secondary" href="{{ route('admin.staff.edit', ['staff' => $staff->id]) }}">編輯</a>
                @if ($admin && !$staff->admin && !$staff->manager)
                  <a class="btn btn-secondary btn-assign-manager"  href="#">指派管理員</a>
                @endif
                @if (!$staff->subscribed)
                    <a class="btn btn-secondary btn-assign-subscription" href="#">訂閱</a>
                @endif
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

            //指派管理員功能
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
                    assign_btn.remove();
                })
                .fail(function (result) {
                    console.log(result.responseText);
                    $('.container').prepend('<div id="alert-message" class="alert alert-danger">' + result.responseText + '</div>');
                    setTimeout(function() { $('#alert-message').remove();}, 3000);
                });
            }); 
            //指派訂閱功能
            var btn_assign_subscription = staff_index.find('.btn-assign-subscription');
            btn_assign_subscription.click(function () {
                var assign_btn = $(this);
                var staff_id = $(this).closest('tr').data('id');
                $.ajax({
                    type: 'post',
                    url: '{{ route('admin.staff.assignSubscription') }}',
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
                    assign_btn.parent().siblings('.subscription').html('<span class="badge badge-success">已訂閱</span>');
                    assign_btn.remove();
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
