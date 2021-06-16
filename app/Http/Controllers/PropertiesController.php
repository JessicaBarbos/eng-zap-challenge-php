<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PropertiesService;

class PropertiesController extends Controller
{
    private $service;

    public function __construct(PropertiesService $service)
    {
        $this->service = $service;
    }

    public function findByZap(Request $request)
    {
        return $this->service->findByZap(
            $request->get('pageSize', 20)
        );

    }

    public function findByViva(Request $request)
    {
        return $this->service->findByViva(
            $request->get('pageSize', 20)
        );

    }

}