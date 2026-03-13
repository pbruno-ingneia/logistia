<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TariffeImport;


class AjaxController extends Controller {

    public function is_loggato(){
        if(!session()->has('utente')) return Redirect::to('admin/login')->send();
    }

}
