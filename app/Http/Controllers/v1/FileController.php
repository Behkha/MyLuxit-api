<?php

namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    const STORAGE_URL = 'file.chaarpaye.ir/';
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function uploadPlaceImage(Request $request){
        $file = $request->file('file');
        $fileName = self::generateImageFileName($file,'jpg');

        $defaultFile = self::manipulateImageFile($file,900,600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'places/default/');

        $previewFile = self::manipulateImageFile($file, 900, 600, 50, 50);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'places/preview/');
        //TODO : add more sizes .

        return response()->json(['path'=>$defaultFilePath,'preview_path'=>$previewFilePath]);
    }
    public function uploadEventImage(Request $request){
        $file = $request->file('file');
        $fileName = self::generateImageFileName($file,'jpg');

        $defaultFile = self::manipulateImageFile($file,900,600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'events/default/');

        $previewFile = self::manipulateImageFile($file, 900, 600, 50, 50);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'events/preview/');

        //TODO : add more sizes .

        return response()->json(['path'=>$defaultFilePath,'preview_path'=>$previewFilePath]);
    }

    public static function removeFileByPath($path) {
        $fileName = str_replace(FileController::STORAGE_URL,'', $path);
        Storage::delete($fileName);
    }
    public static function manipulateImageFile($file, $width, $height, $quality="75", $blur=null, $encode="jpg"){
        $image = Image::make($file)->resize($width,$height);
        if ($blur){
            $image = $image->blur($blur);
        }
        $image = $image->encode($encode, $quality)->getEncoded();
        return $image ;
    }
    public static function uploadImageFile($file, $fileHashedName, $filePath, $extension=''){
        $fileName = str_random(8).$fileHashedName;
        if ($extension)
            $fileName .= '.'.$extension ;

        $path = self::STORAGE_URL.$filePath.$fileName;

        Storage::put(
            $filePath.$fileName, $file
        );

        Redis::lpush(config('cache.prefix').'uploadedImages',$path);

        return $path ;
    }
    public static function uploadNamedImageFile($file, $fileName, $filePath){
        $path = self::STORAGE_URL.$filePath.$fileName;

        Storage::put(
            $filePath.$fileName, $file
        );

        Redis::lpush(config('cache.prefix').'uploadedImages',$path);

        return $path ;
    }
    public static function generateImageFileHashName($file, $removeExtension=true){
        $fileName = $file->hashName();

        if ($removeExtension)
            $fileName = str_replace('.','',$fileName);

        return $fileName;
    }
    public static function generateImageFileName($file, $extension){
        $fileName = self::generateImageFileHashName($file);
        $fileName = str_random(8).$fileName;
        if ($extension)
            $fileName .= '.'.$extension ;
        return $fileName;
    }

    public function uploadFile(Request $request){
        $file = $request->file('file');
        $filePath = $request->filePath;
        $fileName = $request['name'];
        $temp = Storage::putFileAs(
            $filePath, $file, $fileName
        );
        return response()->json($temp);
    }
}