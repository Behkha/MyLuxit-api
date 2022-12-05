<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;

class ParamList
{
    private $filterList = null;
    private $includeList = null;
    private $formatList = null;
    private $page = null;

    public function __construct(Request $request)
    {
        $this->filterList = new FilterList($request->input('filters'));
        $this->includeList = new IncludeList($request->input('include'));
        $this->formatList = new FormatList($request->input('formats'));
        $this->page = ($request->input('page') && is_numeric($request->input('page'))) ? $request->input('page') : 1;


    }

    public function filters()
    {
        return $this->filterList;
    }

    public function includes()
    {
        return $this->includeList;
    }

    public function formats()
    {
        return $this->formatList;
    }

    public function getPage()
    {
        return $this->page;
    }
}