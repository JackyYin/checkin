@extends('admin.layouts.master')

@section('content')
    <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">姓名</th>
              <th scope="col">email</th>
              <th scope="col">員工編號</th>
              <th scope="col">啟用狀態</th>
              <th scope="col">訂閱狀態</th>
              <th scope="col">動作</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($staffs as $staff)
            <tr>
              <td>{{$staff->name}}</td>
              <td>{{$staff->email}}</td>
              <td>{{$staff->staff_code}}</td>
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
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
    </div>
@endsection
