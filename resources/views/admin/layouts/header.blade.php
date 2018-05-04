<nav class="navbar navbar-expand-lg bg-dark sticky-top navbar-dark">
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.staff.index') }}>員工管理</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href=#>excel匯入資料</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.staff.create') }}>手動輸入資料</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href={{ route('admin.check.export_page') }}>輸出打卡資料</a>
            </li>
        </ul>
    </div>
</nav>
