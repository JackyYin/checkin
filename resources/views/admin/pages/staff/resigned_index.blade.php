@extends('admin.layouts.master')

@section('content')
    <div class="table-responsive" id="resignedStaffIndex">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <form class="form-inline my-2 my-lg-0" action="{{ route('admin.staff.index') }}" method="get">
              <input class="form-control mr-sm-2" type="search" placeholder="姓名、email..." aria-label="Search" name="keyword">
              <button class="btn btn-outline-success my-2 my-sm-0" type="submit">搜尋</button>
            </form>
            <a class="btn btn-outline-secondary" href="{{ route('admin.staff.resignedIndex') }}" style="margin-left: 10px">返回</a>
        </nav>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">姓名</th>
              <th scope="col">email</th>
              <th scope="col">員工編號</th>
              <th scope="col">動作</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($staffs as $staff)
            <tr data-id="{{$staff->id}}">
              <td>{{$staff->name}}</td>
              <td>{{$staff->email}}</td>
              <td>{{$staff->staff_code}}</td>
              <td>
                  待更新
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
        });
    </script>
@endsection
