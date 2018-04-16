    <form action="{{ route('admin.authenticate') }}" method="post">
        <div class="modal show">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="text-center">登入</h1>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div>
                                <p>帳號</p>
                            </div>
                            <div>
                                <input type="email" name="email" required>
                            </div>
                        </div>
                        <div>
                            <div>
                                <p>密碼</p>
                            </div>
                            <div>
                                <input type="password" name="password" required>
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
