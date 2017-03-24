<?php
use App\Config\Roles;use App\Config\SiteConstants;use App\Services\UserRolesServices;
?>
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('/public/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/public/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/public/css/site.css') }}" rel="stylesheet">
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
    <div id="app" class="navbar navbar-inverse navbar-fixed-top">
        <nav >
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;<li><a href="{{ url('admin') }}">Admin</a></li>
                    </ul>


                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    <?php

                                    $role='';
                                    $o365userId=null;
                                    if(Auth::user())
                                        $o365userId=Auth::user()->o365UserId;

                                    if(isset($_SESSION[SiteConstants::Session_O365_User_ID])){
                                        $o365userId = $_SESSION[SiteConstants::Session_O365_User_ID];
                                    }

                                    if($o365userId)
                                        $role = (new UserRolesServices)->GetUserRole($o365userId);
                                    if($role)
                                        {
                                            if($role ===Roles::Faculty)
                                                $role='Teacher';
                                            $msg='Logged in as: '.$role .'. ';
                                            echo $msg;
                                        }
                                    if(Auth::user()){
                                        $displayName = Auth::user()->email;
                                        if(Auth::user()->firstName !='')
                                            $displayName =Auth::user()->firstName .' '. Auth::user()->lastName;
                                        echo 'Hello ' . $displayName;
                                    }
                                    else{
                                        if(isset($_SESSION[SiteConstants::Session_O365_User_First_name] ) && isset( $_SESSION[SiteConstants::Session_O365_User_Last_name]))
                                            echo  'Hello '.  $_SESSION[SiteConstants::Session_O365_User_First_name] .' '. $_SESSION[SiteConstants::Session_O365_User_Last_name] ;
                                    }

                                    ?>

                                   <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="/auth/aboutme">About Me</a></li>
                                    <li><a href="/link">Link</a></li>
                                    <li>
                                        <a href="{{ url('/userlogout') }}">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>

                    </ul>

                </div>
            </div>
        </nav>


    </div>

        <div class="containerbg">
            <div class="container body-content">
                @yield('content')
            </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('/public/js/app.js') }}"></script>

    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.message-container').fadeOut(5000);
        });
    </script>
</body>
</html>
