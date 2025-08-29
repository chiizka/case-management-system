<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(){
        return view('frontend.index');
    }


    public function login(){
        return view('frontend.login');
    }

    public function users()
    {
        $users = \App\Models\User::all(); // fetch all users
        return view('frontend.users', compact('users'));
    }
    
}
