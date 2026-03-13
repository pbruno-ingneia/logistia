<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use MongoDB\Driver\Exception\ExecutionTimeoutException;
use PHPMailer\PHPMailer\PHPMailer;
use Socialite;


class HomeController extends Controller{

    public function index(){
        return Redirect::to('admin/login');
    }

}
