<?php
namespace App\Http\Controllers\Helpers;

class FormatList{
    private $formats = [] ;

    public function __construct($formats)
    {
        $this->initialFormats($formats);
    }
    private function initialFormats($formats){
        if (!$formats){
            return ;
        }
        $this->formats= explode(',', $formats);
    }

    public function getAll(){
        return $this->formats;
    }
    public function has($formats){
        return in_array($formats,$this->formats);
    }
    public function isEmpty(){
        return empty($this->formats);
    }

}