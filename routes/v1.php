<?php

//Info
$router->get('/', function () use ($router) {
    return response()->json('Chaar Paye Private Protected Api, © ' . date('Y') . '  CHAARPAYE.IR ALL RIGHTS RESERVED', 403);
});

//Session
$router->group(['prefix' => '/sessions'], function () use ($router) {
    $router->post('/', ['as' => 'Login', 'uses' => 'AuthController@login']);
    $router->delete('/', ['as' => 'Logout', 'uses' => 'AuthController@logout']);
    $router->post('/admins', ['as' => 'AdminLogin', 'uses' => 'AuthController@LoginAdmin']);
    $router->delete('/admins', ['as' => 'AdminLogout', 'uses' => 'AuthController@LogoutAdmin', 'middleware' => ['auth:admin']]);
});

//User
$router->group(['prefix' => '/users'], function () use ($router) {
    $router->post('/', ['as' => 'RegisterUser', 'uses' => 'UserController@register']);
});

//Admin
$router->group(['prefix' => '/admins'], function () use ($router) {
    $router->post('/', ['as' => 'CreateAdmin', 'uses' => 'AdminController@create', 'middleware' => ['auth:admin']]);
    $router->get('/self', ['as' => 'self', 'uses' => 'AdminController@self', 'middleware' => ['auth:admin']]);
});

//Tag
$router->group(['prefix' => '/tags'], function () use ($router) {
    $router->get('/', ['as' => 'Tags', 'uses' => 'TagController@index', 'middleware' => ['auth:admin']]);
    $router->get('/{id}', ['as' => 'Tag', 'uses' => 'TagController@show', 'middleware' => ['auth:admin']]);
    $router->post('/', ['as' => 'CreateTag', 'uses' => 'TagController@create', 'middleware' => ['auth:admin']]);
});

//Author
$router->group(['prefix' => '/authors'], function () use ($router) {
    $router->get('/', ['as' => 'Authors', 'uses' => 'AuthorController@index', 'middleware' => ['auth:admin']]);
    $router->get('/{id}', ['as' => 'Author', 'uses' => 'AuthorController@show', 'middleware' => ['auth:admin']]);
    $router->post('/', ['as' => 'CreateAuthor', 'uses' => 'AuthorController@create', 'middleware' => ['auth:admin']]);
});

//Photographer
$router->group(['prefix' => '/photographers'], function () use ($router) {
    $router->get('/', ['as' => 'Photographers', 'uses' => 'PhotographerController@index', 'middleware' => ['auth:admin']]);
    $router->get('/{id}', ['as' => 'Photographer', 'uses' => 'PhotographerController@show', 'middleware' => ['auth:admin']]);
    $router->post('/', ['as' => 'CreatePhotographer', 'uses' => 'PhotographerController@create', 'middleware' => ['auth:admin']]);
});

//Post
$router->group(['prefix' => '/posts'], function () use ($router) {
    $router->get('/', ['as' => 'Posts', 'uses' => 'PostController@index']);
    $router->get('/{id}', ['as' => 'Post', 'uses' => 'PostController@show']);
    $router->post('/', ['as' => 'CreatePost', 'uses' => 'PostController@create', 'middleware' => ['auth:admin']]);
});

//Event
$router->group(['prefix' => '/events'], function () use ($router) {
    $router->get('/types/', ['as' => 'EventTypes', 'uses' => 'EventController@indexTypes']);
    $router->get('/types/{id}', ['as' => 'EventType', 'uses' => 'EventController@showType']);
    $router->post('/types/', ['as' => 'CreateEventType', 'uses' => 'EventController@createType', 'middleware' => ['auth:admin']]);

    $router->get('/', ['as' => 'Events', 'uses' => 'EventController@index']);
    $router->get('/{id}', ['as' => 'Event', 'uses' => 'EventController@show']);
    $router->post('/', ['as' => 'CreateEvent', 'uses' => 'EventController@create', 'middleware' => ['auth:admin']]);
});

//Place
$router->group(['prefix' => '/places'], function () use ($router) {
    $router->get('/types/', ['as' => 'PlaceTypes', 'uses' => 'PlaceController@indexTypes']);
    $router->get('/types/{id}', ['as' => 'PlaceType', 'uses' => 'PlaceController@showType']);
    $router->post('/types/', ['as' => 'CreatePlaceType', 'uses' => 'PlaceController@createType', 'middleware' => ['auth:admin']]);

    $router->get('/', ['as' => 'Places', 'uses' => 'PlaceController@index']);
    $router->get('/{id}', ['as' => 'Place', 'uses' => 'PlaceController@show']);
    $router->post('/', ['as' => 'CreatePlace', 'uses' => 'PlaceController@create', 'middleware' => ['auth:admin']]);
});

//Category
$router->group(['prefix' => '/categories'], function () use ($router) {
    $router->get('/', ['as' => 'Categories', 'uses' => 'CategoryController@index']);
    $router->get('/{id}', ['as' => 'Category', 'uses' => 'CategoryController@show']);
    $router->post('/', ['as' => 'CreateCategory', 'uses' => 'CategoryController@create', 'middleware' => ['auth:admin']]);
});

//Bookmarks
$router->group(['prefix' => '/user/collections', 'middleware' => ['auth:user']], function () use ($router) {
    $router->get('/', ['as' => 'Collections', 'uses' => 'BookmarkCollectionController@index']);
    $router->post('/', ['as' => 'CreateCollection', 'uses' => 'BookmarkCollectionController@create']);
    $router->get('/{id}', ['as' => 'GetCollection', 'uses' => 'BookmarkCollectionController@show']);
    $router->put('/{id}', ['as' => 'UpdateCollection', 'uses' => 'BookmarkCollectionController@update']);
    $router->delete('/{id}', ['as' => 'DeleteCollection', 'uses' => 'BookmarkCollectionController@delete']);

    $router->group(['prefix' => '/{collectionId}/bookmarks'], function () use ($router) {
        $router->post('/', ['as' => 'CreateBookmark', 'uses' => 'BookmarkCollectionController@createBookmark']);
        $router->delete('/events/{id}', ['as' => 'DeleteBookmarkedEvent', 'uses' => 'BookmarkCollectionController@deleteEventBookmark']);
        $router->delete('/places/{id}', ['as' => 'DeleteBookmarkedPlace', 'uses' => 'BookmarkCollectionController@deletePlaceBookmark']);
    });
});
$router->get('/user/bookmarks', ['as' => 'GetBookmarks', 'uses' => 'BookmarkCollectionController@list', 'middleware' => ['auth:user']]);

//Search
$router->group(['prefix' => '/search', 'middleware' => ['auth:admin']], function () use ($router) {
    $router->post('/tags', ['as' => 'SearchTags', 'uses' => 'TagController@search']);
    $router->post('/places', ['as' => 'SearchPlaces', 'uses' => 'PlaceController@search']);
    $router->post('/events', ['as' => 'SearchEvents', 'uses' => 'EventController@search']);
});

//File
$router->group(['prefix' => '/files'], function () use ($router) {
    $router->post('/events/images', ['as' => 'UploadEventImage', 'uses' => 'FileController@uploadEventImage']);
    $router->post('/places/images', ['as' => 'UploadPlaceImage', 'uses' => 'FileController@uploadPlaceImage']);
    $router->post('/', ['as' => 'UploadFile', 'uses' => 'FileController@uploadFile', 'middleware' => ['auth:admin']]);
});