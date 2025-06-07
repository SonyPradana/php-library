<?php

use System\Router\Router;

Router::get('/test', [__CLASS__, __FUNCTION__])->name('test')->middleware(['test']);
Router::get('/test/(:id)', [__CLASS__, 'empty']);
Router::prefix('test/')->group(function () {
    Router::post('/test/post', [__CLASS__, 'post'])->name('post');
});
