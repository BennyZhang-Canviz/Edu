<?php

//use Illuminate\Routing\Route;
//
//$route = Route::current();
//$actionName = $route->getActionName();
//App\Http\Controllers\SchoolsController@users

$json_string = file_get_contents('public/demo-pages.json');

// 把JSON字符串转成PHP数组
$data123 = json_decode($json_string);

// 显示出来看看
dd($data123);
?>

<div class="demo-helper-control collapsed">
    <div class="header">DEMO HELPER</div>
    <div class="header-right-shadow-mask"></div>
    <div class="body">
        <p class="desc">Code sample links for this page:</p>

        <ul>

            <li>
                <p class="title">@link.Title</p>
                <p><a href="@link.Url" target="_blank">@link.Url</a></p>
            </li>

        </ul>

        <p class="empty-result">Links not available.</p>

    </div>
</div>