<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SSH BOT</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link href="/css/bootstrap-sketchy.min.css" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                /*font-family: 'Raleway', sans-serif;*/
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            #version {
                font-size: 20px;
                background-color: #3d4040;
                padding: 6px;
                color: cyan;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            @endif
            <div class="content">
                <div class="title m-b-md">
                    SSH Bot <sub id="version">Beta</sub>
                </div>
                <form action="/ns-setup" method="POST">
                    @csrf
                    <siv class="row">
                        <div class="form-group col-sm-10">
                            <input type="text" name="domain" class="form-control" placeholder="Domain">
                            <span class="text-danger">{{$errors->first('domain')}}</span>
                        </div>
                    </siv>
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="form-group">
                                <input type="text" name="host1" class="form-control" placeholder="IP Address">
                            <span class="text-danger">{{$errors->first('host1')}}</span>
                            </div>
                            <div class="form-group">
                                <input type="text" name="username1" class="form-control" placeholder="username">
                            <span class="text-danger">{{$errors->first('username1')}}</span>
                            </div>
                            <div class="form-group">
                                <input type="text" name="password1" class="form-control" placeholder="Password">
                            <span class="text-danger">{{$errors->first('password1')}}</span>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="form-group">
                                <input type="text" name="host2" class="form-control" placeholder="IP Address">
                            <span class="text-danger">{{$errors->first('host2')}}</span>
                            </div>
                            <div class="form-group">
                                <input type="text" name="username2" class="form-control" placeholder="username">
                            <span class="text-danger">{{$errors->first('username2')}}</span>
                            </div>
                            <div class="form-group">
                                <input type="text" name="password2" class="form-control" placeholder="Password">
                            <span class="text-danger">{{$errors->first('password2')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="form-group col-sm-6">
                            <input type="submit" value="Install NameServer" class="form-control" style="cursor: pointer;" placeholder="">
                        </div>
                    </div>                    
                </form>
            </div>
        </div>
        <div style="position: fixed;bottom: 10px;right: 10px;">Developed by <a href="https://www.devopinion.com" style="font-size: 25px;">Amarjit Singh</a></div>
        <script src="/js/jquery-3.3.1.slim.min.js"></script>
        <script src="/js/popper.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
    </body>
</html>