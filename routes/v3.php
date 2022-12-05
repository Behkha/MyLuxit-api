<?php

//Info
$router->get('/', function () use ($router) {
    return response()->json('MyLuxit Private Protected Api, ©' . date('Y') . 'MYLUXIT.IR ALL RIGHTS RESERVED', 403);
});

//Session
$router->group(['prefix' => '/sessions'], function () use ($router) {
    $router->patch('/activation', ['as' => 'UserSendActivationSMS', 'uses' => 'AuthController@sendActivationCode']);
    $router->post('/', ['as' => 'UserLogin', 'uses' => 'AuthController@login']);
    $router->delete('/', ['as' => 'UserLogout', 'uses' => 'AuthController@logout']);
    $router->post('/admins', ['as' => 'AdminLogin', 'uses' => 'AuthController@LoginAdmin']);
    $router->delete('/admins', ['as' => 'AdminLogout', 'uses' => 'AuthController@LogoutAdmin', 'middleware' => ['auth:admin']]);
});

//Users
$router->group(['prefix' => '/users'], function () use ($router) {
    $router->get('/', ['as' => 'IndexUsers', 'uses' => 'UserController@index', 'middleware' => 'auth:admin']);
    $router->get('/{id}', ['as' => 'ShowUser', 'uses' => 'UserController@show', 'middleware' => 'auth:admin']);
    $router->patch('/{id}/avatar', ['as' => 'AcceptAvatar', 'uses' => 'UserController@acceptAvatar', 'middleware' => 'auth:admin']);
    $router->post('/', ['as' => 'RegisterUser', 'uses' => 'UserController@register']);
    $router->patch('/activation', ['as' => 'ConfirmTell', 'uses' => 'UserController@confirm']);
});

//User
$router->group(['prefix' => '/user'], function () use ($router) {
//    $router->post('/resets', ['as' => 'ForgetPassword', 'uses' => 'ResetPasswordController@create']);
//    $router->get('/resets/validation', ['as' => 'ValidateResetToken', 'uses' => 'ResetPasswordController@tokenValidation']);
//    $router->patch('/password', ['as' => 'ResetPassword', 'uses' => 'ResetPasswordController@resetPassword']);
    $router->group(['middleware' => 'auth:user'], function () use ($router) {
        $router->get('/self', ['as' => 'ShowUser', 'uses' => 'UserController@showSelf', 'middleware' => 'user.active']);
        $router->put('/self', ['as' => 'update', 'uses' => 'UserController@update']);
        $router->post('/avatar', ['as' => 'UploadUserProfilePictere', 'uses' => 'FileController@uploadUserAvatar']);
        $router->delete('/avatar', ['as' => 'RemoveUserProfilePicture', 'uses' => 'UserController@removeProfilePicture']);
    });
});

//Admin
$router->group(['prefix' => 'admins', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'IndexAdmins', 'uses' => 'AdminController@index']);
    $router->get('/self', ['as' => 'ShowAdmin', 'uses' => 'AdminController@self']);
    $router->post('/', ['as' => 'CreateAdmin', 'uses' => 'AdminController@create']);
    $router->put('/{id}', ['as' => 'UpdateAdmin', 'uses' => 'AdminController@update']);
    $router->delete('/{id}', ['as' => 'UpdateAdmin', 'uses' => 'AdminController@delete']);
    $router->get('/{id}', ['as' => 'ShowAdmin', 'uses' => 'AdminController@show']);
});


//Tag
$router->group(['prefix' => '/tags', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'IndexTags', 'uses' => 'TagController@index']);
    $router->get('/{id}', ['as' => 'ShowTag', 'uses' => 'TagController@show']);
    $router->put('/{id}', ['as' => 'UpdateTag', 'uses' => 'TagController@update']);
    $router->post('/', ['as' => 'CreateTag', 'uses' => 'TagController@create']);
    $router->delete('/{id}', ['as' => 'DeleteTag', 'uses' => 'TagController@delete']);
});

//Author
$router->group(['prefix' => '/authors', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'IndexAuthors', 'uses' => 'AuthorController@index']);
    $router->get('/{id}', ['as' => 'ShowAuthor', 'uses' => 'AuthorController@show']);
    $router->put('/{id}', ['as' => 'UpdateAuthor', 'uses' => 'AuthorController@update']);
    $router->post('/', ['as' => 'CreateAuthor', 'uses' => 'AuthorController@create']);
    $router->delete('/{id}', ['as' => 'DeleteAuthor', 'uses' => 'AuthorController@delete']);
});

//Photographer
$router->group(['prefix' => '/photographers', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'IndexPhotographers', 'uses' => 'PhotographerController@index']);
    $router->get('/{id}', ['as' => 'ShowPhotographer', 'uses' => 'PhotographerController@show']);
    $router->post('/', ['as' => 'CreatePhotographer', 'uses' => 'PhotographerController@create']);
    $router->put('/{id}', ['as' => 'UpdatePhotographers', 'uses' => 'PhotographerController@update']);
    $router->delete('/{id}', ['as' => 'DeletePhotographer', 'uses' => 'PhotographerController@delete']);
});

//Post
$router->group(['prefix' => '/posts'], function () use ($router) {
    $router->get('/', ['as' => 'IndexPosts', 'uses' => 'PostController@index']);
    $router->get('/nearests', ['as' => 'NearestPosts', 'uses' => 'PostController@indexNearests']);
    $router->get('/{id:[0-9]+}', ['as' => 'Post', 'uses' => 'PostController@show']);
    $router->post('/', ['as' => 'CreatePost', 'uses' => 'PostController@create', 'middleware' => ['auth:admin']]);
    $router->delete('/{id}', ['as' => 'DeletePost', 'uses' => 'PostController@delete', 'middleware' => 'auth:admin']);
    $router->put('/{id}', ['as' => 'UpdatePost', 'uses' => 'PostController@update', 'middleware' => 'auth:admin']);
});

//Event
$router->group(['prefix' => '/events'], function () use ($router) {
    $router->get('/types/', ['as' => 'EventTypes', 'uses' => 'EventController@indexTypes']);
    $router->get('/types/{id}', ['as' => 'EventType', 'uses' => 'EventController@showType']);
    $router->get('/', ['as' => 'Events', 'uses' => 'EventController@index']);
    $router->get('/{id}', ['as' => 'Event', 'uses' => 'EventController@show']);

    $router->get('/{id}/comments', ['as' => 'IndexEventComments', 'uses' => 'CommentController@indexCommentableComments', 'type' => 'event']);

    $router->group(['middleware' => 'auth:admin'], function () use ($router) {
        $router->post('/types/', ['as' => 'CreateEventType', 'uses' => 'EventController@createType']);
        $router->delete('/types/{id}', ['as' => 'DeleteEventType', 'uses' => 'EventController@deleteType']);
        $router->put('/types/{id}', ['as' => 'UpdateEventType', 'uses' => 'EventController@updateType']);

        $router->post('/', ['as' => 'CreateEvent', 'uses' => 'EventController@create']);
        $router->delete('/{id}', ['as' => 'DeleteEvent', 'uses' => 'EventController@delete']);
        $router->put('/{id}', ['as' => 'UpdateEvent', 'uses' => 'EventController@update']);

        $router->post('/{id}/admin_comments', ['as' => 'CreateEventAdminComment', 'uses' => 'CommentController@createAdminComment', 'type' => 'event']);
    });


    $router->post('/{id}/comments', ['as' => 'CreateEventComment', 'uses' => 'CommentController@create', 'middleware' => 'auth:user', 'type' => 'event']);
    $router->post('/{id}/ratings', ['as' => 'CreateEventRating', 'uses' => 'RatingController@create', 'middleware' => 'auth:user', 'type' => 'event']);
    $router->get('/{id}/gallery/images/confirmed', ['as' => 'IndexEventImages', 'uses' => 'EventController@indexConfirmedImages']);
    $router->get('/{id}/gallery/images', ['as' => 'IndexEventImages', 'uses' => 'EventController@indexImages', 'middleware' => 'auth:admin']);
    $router->patch('/{id}/gallery/images/{imageId}', ['as' => 'ChangeImageStatus', 'uses' => 'EventController@changeImageStatus', 'middleware' => 'auth:admin']);
});

//Place
$router->group(['prefix' => '/places'], function () use ($router) {
    $router->get('/types/', ['as' => 'PlaceTypes', 'uses' => 'PlaceController@indexTypes']);
    $router->get('/types/{id}', ['as' => 'PlaceType', 'uses' => 'PlaceController@showType']);


    $router->group(['middleware' => 'auth:admin'], function () use ($router) {
        $router->post('/types/', ['as' => 'CreatePlaceType', 'uses' => 'PlaceController@createType']);
        $router->put('/types/{id}/', ['as' => 'UpdatePlaceType', 'uses' => 'PlaceController@updateType']);
        $router->delete('/types/{id}', ['as' => 'DeletePlaceType', 'uses' => 'PlaceController@deleteType']);

        $router->post('/', ['as' => 'CreatePlace', 'uses' => 'PlaceController@create']);
        $router->put('/{id}/', ['as' => 'UpdatePlace', 'uses' => 'PlaceController@update']);
        $router->delete('/{id}/', ['as' => 'DeletePlace', 'uses' => 'PlaceController@delete']);

        $router->post('/{id}/admin_comments', ['as' => 'CreatePlaceAdminComment', 'uses' => 'CommentController@createAdminComment', 'type' => 'place']);
    });

    $router->get('/', ['as' => 'Places', 'uses' => 'PlaceController@index']);
    $router->get('/{id}', ['as' => 'Place', 'uses' => 'PlaceController@show']);

    $router->get('/{id}/gallery/images/confirmed', ['as' => 'IndexPlaceImages', 'uses' => 'PlaceController@indexConfirmedImages']);
    $router->get('/{id}/gallery/images', ['as' => 'IndexPlaceImages', 'uses' => 'PlaceController@indexImages', 'middleware' => 'auth:admin']);
    $router->patch('/{id}/gallery/images/{imageId}', ['as' => 'ChangeImageStatus', 'uses' => 'PlaceController@changeImageStatus', 'middleware' => 'auth:admin']);
    $router->get('/{id}/events', ['as' => 'PlaceEvents', 'uses' => 'PlaceController@showEvents']);
    $router->get('/{id}/comments', ['as' => 'IndexPlaceComments', 'uses' => 'CommentController@indexCommentableComments', 'type' => 'place']);
    $router->post('/{id}/comments', ['as' => 'CreatePlaceComment', 'uses' => 'CommentController@create', 'middleware' => 'auth:user', 'type' => 'place']);
    $router->post('/{id}/ratings', ['as' => 'CreatePlaceRating', 'uses' => 'RatingController@create', 'middleware' => 'auth:user', 'type' => 'place']);
});

//Category
$router->group(['prefix' => '/categories'], function () use ($router) {
    $router->get('/', ['as' => 'Categories', 'uses' => 'CategoryController@index']);
    $router->get('/{id}', ['as' => 'Category', 'uses' => 'CategoryController@show']);
    $router->get('/{id}/subcategories', ['as' => 'IndexSubCategories', 'uses' => 'CategoryController@indexSubcategories']);
    $router->get('/{id}/places/nearests', ['as' => 'IndexCategoryNearestPlaces', 'uses' => 'CategoryController@indexNearests']);
    $router->group(['middleware' => 'auth:admin'], function () use ($router) {
        $router->post('/', ['as' => 'CreateCategory', 'uses' => 'CategoryController@create']);
        $router->put('/{id}', ['as' => 'UpdateCategory', 'uses' => 'CategoryController@update']);
        $router->delete('/{id}/', ['as' => 'DeleteCategory', 'uses' => 'CategoryController@delete']);
        $router->post('/{id}/image', ['as' => 'UpdateCategoryImage', 'uses' => 'CategoryController@updateImage']);
    });
});

//Bookmarks
$router->group(['prefix' => '/user/bookmarks', 'middleware' => ['auth:user', 'user.active']], function () use ($router) {
    $router->post('/', ['as' => 'CreateBookmark', 'uses' => 'BookmarkController@create']);
    $router->get('/', ['as' => 'IndexBookmarks', 'uses' => 'BookmarkController@index']);
    $router->delete('/events/{id}', ['as' => 'DeleteEventBookmark', 'uses' => 'BookmarkController@deleteEventBookmark']);
    $router->delete('/places/{id}', ['as' => 'DeletePlaceBookmark', 'uses' => 'BookmarkController@deletePlaceBookmark']);
});


//Search
$router->group(['prefix' => '/search'], function () use ($router) {
    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        $router->get('/tags', ['as' => 'SearchTags', 'uses' => 'TagController@search']);
        //$router->get('/places', ['as' => 'SearchPlaces', 'uses' => 'PlaceController@search']);
        $router->get('/events', ['as' => 'SearchEvents', 'uses' => 'EventController@search']);
    });
    $router->get('/places', ['as' => 'SearchPlaces', 'uses' => 'PlaceController@search']);
    $router->get('/posts', ['as' => 'SearchPosts', 'uses' => 'PostController@search']);
});

//File
$router->group(['prefix' => '/files'], function () use ($router) {
    $router->post('/events/images', ['as' => 'UploadEventImage', 'uses' => 'FileController@uploadEventImage']);
    $router->post('/places/images', ['as' => 'UploadPlaceImage', 'uses' => 'FileController@uploadPlaceImage']);
    $router->post('/places/{id}/gallery/images', ['as' => 'UploadPlacePublicImage', 'uses' => 'FileController@uploadPlacePublicImage', 'middleware' => 'auth:user']);
    $router->post('/events/{id}/gallery/images', ['as' => 'UploadEventPublicImage', 'uses' => 'FileController@uploadEventPublicImage', 'middleware' => 'auth:user']);
    $router->post('/celebrities/images', ['as' => 'UploadCelebrityImage', 'uses' => 'FileController@uploadCelebrityImage']);
    $router->post('/', ['as' => 'UploadFile', 'uses' => 'FileController@uploadFile', 'middleware' => ['auth:admin']]);
});

//Comment
$router->group(['prefix' => 'comments', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'Comments', 'uses' => 'CommentController@index']);
    $router->get('/{id:[0-9]+}', ['as' => 'ShowComment', 'uses' => 'CommentController@show']);
    $router->patch('/{id}', ['as' => 'UpdateCommentStatus', 'uses' => 'CommentController@updateStatus']);
    $router->delete('/{id}', ['as' => 'DeleteComment', 'uses' => 'CommentController@delete']);
    $router->get('/pending', ['as' => 'IndexPendingComments', 'uses' => 'CommentController@indexPending']);
});

// Cities
$router->group(['prefix' => 'cities'], function () use ($router) {
    $router->group(['middleware' => ['auth:admin']], function () use ($router) {
        $router->post('/', ['as' => 'CreateCity', 'uses' => 'CityController@create']);
        $router->put('/{id}', ['as' => 'UpdateCity', 'uses' => 'CityController@update']);
    });
    $router->get('/', ['as' => 'IndexCities', 'uses' => 'CityController@index']);
});


// Provinces
$router->group(['prefix' => '/provinces', 'middleware' => 'auth:admin'], function () use ($router) {
    $router->get('/', ['as' => 'IndexProvince', 'uses' => 'ProvinceController@index']);
    $router->get('/{id}', ['as' => 'ShowProvince', 'uses' => 'ProvinceController@show']);
    $router->put('/{id}', ['as' => 'UpdateProvince', 'uses' => 'ProvinceController@update']);
    $router->post('/', ['as' => 'CreateProvince', 'uses' => 'ProvinceController@create']);
    //$router->delete('/{id}', ['as' => 'DeleteProvince', 'uses' => 'ProvinceController@delete']);
});


$router->group(['prefix' => 'characters'], function () use ($router) {
    $router->get('/', ['as' => 'IndexCharacters', 'uses' => 'CharacterController@index', 'middleware' => ['auth:admin']]);

    $router->group(['prefix' => 'types'], function () use ($router) {
        $router->post('/', ['as' => 'CreateCharacterTypes', 'uses' => 'CharacterController@createType']);
        $router->get('/', ['as' => 'IndexCharacterTypes', 'uses' => 'CharacterController@indexTypes']);
        $router->get('/{id}', ['as' => 'ShowCharacterType', 'uses' => 'CharacterController@showType']);
        $router->put('/{id}', ['as' => 'UpdateCharacterType', 'uses' => 'CharacterController@updateType']);
        $router->delete('/{id}', ['as' => 'DeleteCharacterType', 'uses' => 'CharacterController@deleteType']);
    });
});


// Celebrities
$router->group(['prefix' => 'celebrities'], function () use ($router) {
    $router->post('/', ['as' => 'CreateCelebrity', 'uses' => 'CelebrityController@create']);
    $router->get('/{id}', ['as' => 'ShowCelebrity', 'uses' => 'CelebrityController@show']);
    $router->delete('/{id}', ['as' => 'DeleteCelebrity', 'uses' => 'CelebrityController@delete']);
    $router->put('/{id}', ['as' => 'UpdateCelebrity', 'uses' => 'CelebrityController@update']);
    $router->get('/{id}/properties', ['as' => 'IndexCelebrityProperties', 'uses' => 'CelebrityController@indexProperties']);
    $router->get('/{id}/comments', ['as' => 'IndexCelebrityComments', 'uses' => 'CommentController@indexCommentableComments', 'type' => 'celebrity']);
    $router->post('/{id}/comments', ['as' => 'CreateCelebrityComments', 'uses' => 'CommentController@create', 'type' => 'celebrity']);
});

$router->group(['prefix' => 'languages'], function () use ($router) {
    $router->get('/', ['uses' => 'IndexLanguages', 'uses' => 'LanguageController@index']);

});
