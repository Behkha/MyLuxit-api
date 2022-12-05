<?php
namespace App\Http\Controllers\Helpers;
class FilterList{
    private $filters = [] ;

    public function __construct($filters)
    {
        $this->initialFilters($filters);
    }
    private function initialFilters($filters){
        if (!$filters){
            return ;
        }
        $parts = explode(',', $filters);
        foreach ($parts as $part){
            $filterParts = explode(':', $part);
            if (count($filterParts)==2){
                $filterName = $filterParts[0];
                $filterValueParts = explode('|',$filterParts[1]);
                $this->add($filterName,$filterValueParts);
            }
        }
    }

    public function getAll(){
        return $this->filters;
    }
    public function get($filterName){
        if ($this->has($filterName))
            return $this->filters[$filterName];
        else
            return null;
    }
    public function has($filterName){
        return isset($this->filters[$filterName]);
    }
    public function isEmpty(){
        return empty($this->filters);
    }
    public function filterHasValue($filterName,$value){
        if ($this->has($filterName))
            return in_array($value,$this->getAll()[$filterName]);
        return null;
    }
    public function add($filterName, $filterValue){
        $this->filters[$filterName] = $filterValue;
    }
}