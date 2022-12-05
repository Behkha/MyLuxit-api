<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Imagable;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileController extends Controller
{
    const  STORAGE_URL = 'file.myluxit.ir/';

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public static function removeFileByPath($path)
    {
        $fileName = str_replace(self::getStorageUrl(), '', $path);
        Storage::delete($fileName);
    }

    public static function getStorageUrl()
    {
//        if ($prefix = env('FILE_URL_PREFIX')) {
//            return $prefix . ".myluxit.ir/";
//        } else if ($fileUrl = env('FILE_URL')) {
//            return $fileUrl;
            // TODO: Fix this
//        } else if (env('APP_ENV') === 'local')
//            return "file-test.myluxit.ir/";
//        else {
            return self::STORAGE_URL;
//        }
    }

    public static function uploadImageFile($file, $fileHashedName, $filePath, $extension = '')
    {
        $fileName = str_random(8) . $fileHashedName;
        if ($extension)
            $fileName .= '.' . $extension;

        $path = self::getStorageUrl() . $filePath . $fileName;

        Storage::put(
            $filePath . $fileName, $file
        );

        return $path;
    }


    public function uploadPlaceImage(Request $request)
    {
        $file = $request->file('file');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, null, null);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'places/default/');

        $previewFile = self::manipulateImageFile($file, null, null, 20, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'places/preview/');
        //TODO : add more sizes .

        return response()->json(['path' => $defaultFilePath, 'preview_path' => $previewFilePath]);
    }

    public static function generateImageFileName($file, $extension)
    {
        $fileName = self::generateImageFileHashName($file);
        $fileName = str_random(8) . $fileName;
        if ($extension)
            $fileName .= '.' . $extension;
        return $fileName;
    }

    public static function generateImageFileHashName($file, $removeExtension = true)
    {
        $fileName = $file->hashName();

        if ($removeExtension)
            $fileName = str_replace('.', '', $fileName);

        return $fileName;
    }

    public static function manipulateImageFile($file, $width, $height, $quality = "75", $blur = null, $encode = "jpg")
    {
        $image = Image::make($file);

        if ($blur) {
            $image = $image->blur($blur);
        }


        if ($width || $height) {
            $image = $image->resize($width, $height);
        }

        $image = $image->encode($encode, $quality)->getEncoded();
        return $image;
    }

    public static function uploadNamedImageFile($file, $fileName, $filePath)
    {
        $path = self::getStorageUrl() . $filePath . $fileName;

        Storage::put(
            $filePath . $fileName, $file
        );

        return $path;
    }

    public function uploadPlacePublicImage(Request $request, $id = null)
    {
        $this->validate($request, [
            'file' => 'required|image|max:5000'
        ]);
        $place = Place::getById($id);

        $file = $request->file('file');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, null, null);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, "places/public/default/");

        $previewFile = self::manipulateImageFile($file, null, null, 50, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, "places/public/preview/");

        $data = ['path' => $defaultFilePath, 'preview_path' => $previewFilePath];
        $media = array_merge([
            'type' => 'image',
        ], $data
        );

        $place->images()->create([
            'user_id' => Auth::id(),
            'status_id' => Imagable::Statuses['pending'],
            'media' => $media
        ]);

        //TODO : add more sizes .

        return response()->json($data);
    }

    public function uploadEventPublicImage(Request $request, $id = null)
    {
        $this->validate($request, [
            'file' => 'required|image|max:5000'
        ]);
        $event = Event::getById($id);

        $file = $request->file('file');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, 900, 600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, "events/public/default/");

        $previewFile = self::manipulateImageFile($file, 900, 600, 50, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, "events/public/preview/");

        $data = ['path' => $defaultFilePath, 'preview_path' => $previewFilePath];
        $media = array_merge([
            'type' => 'image',
        ], $data
        );

        $event->images()->create([
            'user_id' => Auth::id(),
            'status_id' => Imagable::Statuses['pending'],
            'media' => $media
        ]);

        //TODO : add more sizes .

        return response()->json($data);
    }

    public function uploadEventImage(Request $request)
    {
        $file = $request->file('file');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, 900, 600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'events/default/');

        $previewFile = self::manipulateImageFile($file, 900, 600, 50, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'events/preview/');

        //TODO : add more sizes .

        return response()->json(['path' => $defaultFilePath, 'preview_path' => $previewFilePath]);
    }

    public function uploadCelebrityImage(Request $request)
    {
        $file = $request->file('file');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, 900, 600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'celebrities/default/');

        $previewFile = self::manipulateImageFile($file, 900, 600, 50, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'celebrities/preview/');

        //TODO : add more sizes .

        return response()->json(['path' => $defaultFilePath, 'preview_path' => $previewFilePath]);
    }

    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $filePath = $request->input('filePath');
        $fileName = $request->input('name');
        $temp = Storage::putFileAs(
            $filePath, $file, $fileName
        );
        return response()->json($temp);
    }

    public function uploadUserAvatar(Request $request)
    {
        $this->validateUploadAvatarRequest($request);

        $file = $request->file('avatar');
        $fileName = self::generateImageFileName($file, 'jpg');

        $defaultFile = self::manipulateImageFile($file, 600, 600);
        $defaultFilePath = self::uploadNamedImageFile($defaultFile, $fileName, 'users/avatars/');

        $previewFile = self::manipulateImageFile($file, 600, 600, 50, 15);
        $previewFilePath = self::uploadNamedImageFile($previewFile, $fileName, 'users/avatars/preview/');

        //TODO : add more sizes .

        $user = Auth::guard('user')->user();

        $user->update([
            'profile_picture' => [
                'path' => $defaultFilePath,
                'preview_path' => $previewFilePath
            ],
            'is_profile_picture_accepted' => true
        ]);

        return response()->json(['path' => $defaultFilePath, 'preview_path' => $previewFilePath]);

    }

    private function validateUploadAvatarRequest(Request $request)
    {
        $this->validate($request, [
            'avatar' => 'required|image|max:10000'
        ]);
    }

}
