<?php

namespace App\Http\Controllers;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }
}