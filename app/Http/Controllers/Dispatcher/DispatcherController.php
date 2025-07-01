<?php

namespace App\Http\Controllers\Dispatcher;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DispatcherController extends Controller
{
    public function index()
    {
        return view('dispatcher.dashboard');
    }
}
