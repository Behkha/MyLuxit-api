<?php
namespace App\Http\Controllers\Helpers;

class IncludeList{
    private $includes = [] ;

    public function __construct($includes)
    {
        $this->initialIncludes($includes);
    }
    private function initialIncludes($includes){
        if (!$includes){
            return ;
        }
        $this->includes = explode(',', $includes);
    }

    public function getAll(){
        return $this->includes;
    }
    public function has($include){
        return in_array($include,$this->includes);
    }
    public function isEmpty(){
        return empty($this->includes);
    }

}