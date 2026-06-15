<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaborRelationController extends Controller
{
    public function index()
    {
        return view('labor-relation-cases');
    }
}