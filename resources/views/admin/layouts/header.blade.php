<nav class="navbar navbar-expand-lg bg-dark sticky-top navbar-dark">
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            {{--
            <li class="nav-item">
                <a class="nav-link" href=#>excel匯入資料</a>
            </li>
            --}}
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.staff.index') }}>員工總覽</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.staff.create') }}>手動輸入資料</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.check.export_statistic') }}>輸出統計資料</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.check.export_check') }}>輸出打卡時間</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.check.count_late') }}>查看晚到次數</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.logout') }}>登出</a>
            </li>
        </ul>
    </div>
</nav>
