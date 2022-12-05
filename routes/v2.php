<?php

$router->group(['prefix'=>'/posts'],function () use ($router){
    $router->get('/',['as'=>'Posts', 'uses'=>'PostController@index']);
});