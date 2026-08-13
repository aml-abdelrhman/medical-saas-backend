<?php

use App\Providers\AppServiceProvider;

return [
    \Illuminate\View\ViewServiceProvider::class,
    \Illuminate\Session\SessionServiceProvider::class,
    \Illuminate\Cookie\CookieServiceProvider::class,
    AppServiceProvider::class,
];