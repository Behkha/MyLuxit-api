<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    private const DefaultLanguageAbbr = 'fa';
    protected $guarded = [];

    public static function getDefaultLanguage()
    {
        return self::getByAbbr(self::getDefaultLanguageAbbr());
    }

    public static function getByAbbr($abbreviation)
    {
        return Cache::remember('Languages:' . $abbreviation, 30,
            function () use ($abbreviation) {
                return Language::where('abbreviation', $abbreviation)->firstOrFail();
            }
        );
    }

    public static function getDefaultLanguageAbbr()
    {
        return self::DefaultLanguageAbbr;
    }
}
