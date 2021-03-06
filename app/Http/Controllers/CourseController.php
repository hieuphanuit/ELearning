<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateCourseRequest;
use App\Http\Services\CourseService;

class CourseController extends Controller
{
    //
    protected $service;

    public function __construct(CourseService $courseService)
    {
        $this->service = $courseService;
    }

    public function create(CreateCourseRequest $request)
    {
        return $this->service->create($request);
    }
}
