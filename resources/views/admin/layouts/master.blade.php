<html>
    <head>
            {{ Html::style('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') }}
    </head>
    <body>
        <div class="container">
            @if ($admin)
                @include('admin.layouts.header')
            @endif

            @include('admin.layouts.messages')
            @yield('content')
        </div>

        {{ Html::script('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js') }}
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js') }}
        {{ Html::script('https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/additional-methods.min.js') }}
        @yield('scripts')
    </body>
</html>
