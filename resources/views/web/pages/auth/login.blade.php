@extends('web.layouts.master')

@section('content')  
    <form id="form" action="{{ route('web.authenticate') }}" method="post">
        <div class="modal show">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="text-center">登入</h1>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div>
                                <p>Email</p>
                            </div>
                            <div>
                                <input type="email" name="email">
                            </div>
                        </div>
                        <div>
                            <div>
                                <p>密碼</p>
                            </div>
                            <div>
                                <input type="password" name="password">
                            </div>
                        </div>
                        <div>
                            <div>
                                <p>記住我</p>
                            </div>
                            <div>
                                <input type="checkbox" name="remember" value=1>
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                    <div class="modal-footer">
                        <div class="form-group">
                            <button class="btn">登入</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script>
        var validator = $("#form").validate({
          rules: {
            email:{ required: true, email: true },
            password:{ required: true, minlength: 6 }
          },
          messages: {
            email: {required: "請輸入email", email: "請填入有效的email"},
            password: {required: "請輸入密碼", minlength: "密碼請填入至少6個字元"}
          }
        });
        validator.showErrors();
    </script>
@endsection
