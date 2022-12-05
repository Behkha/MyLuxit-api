<?php

namespace App\Models;

use App\Jobs\SendSMS;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Contracts\JWTSubject as JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;
    use SoftDeletes;

    const STATUSES = [
        'active' => 1,
        'incomplete' => 2,
        'banned' => 3,
    ];

    const GENDERS = [
        'male' => 1,
        'female' => 2,
    ];

    protected $casts = [
        'profile_picture' => 'json'
    ];

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        self::created(function ($user) {
            $user->updateActivationCode();
        });
    }

    public function referral()
    {
        return $this->hasOne('App\Models\UserReferral');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City');
    }

    public function bookmarks()
    {
        return $this->hasMany('App\Models\Bookmark');
    }

    public function bookmarkedEvents()
    {
        return $this->hasMany('App\Models\Bookmark')->where('bookmarkable_type', '=', 'event');
    }

    public function bookmarkedPlaces()
    {
        return $this->hasMany('App\Models\Bookmark')->where('bookmarkable_type', '=', 'place');
    }

    public function bookmarkCollections()
    {
        return $this->hasMany('App\Models\BookmarkCollection');
    }

    public function ratings()
    {
        return $this->hasMany('App\Models\Rating');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function updateActivationCode()
    {

        $random_code = rand(10000, 99999);

        $this->update([
            'activation_code' => $random_code
        ]);

        return $this->activation_code;
    }

    public function sendActivationCodeSMS()
    {
//        dispatch(new SendSMS([
//            'ApplicationName' => 'چارپایه',
//            'VerificationCode' => $this->activation_code
//        ],
//            SendSMS::TEMPLATES['verification'],
//            $this->tell
//       ));
	$url = 'http://www.afe.ir/Url/SendSMS';
            $query_array = array(
                'Username' => env('SMS_USERNAME'),
                'Password' => env('SMS_PASSWORD'),
                'Number' => env('SMS_PHONE'),
                'Mobile' => $this->tell,
                'SMS' => 'به%20چارپایه%20خوش%20آمدید%0Aکد%20فعاسازی:' . $this->activation_code,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?' . 'Username=' . $query_array['Username'] . '&Password=' . $query_array['Password'] . '&Number=' .$query_array['Number'] . '&Mobile=' . $query_array['Mobile'] . '&SMS=' . $query_array['SMS']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            curl_close($ch);
        return;
    }
}
