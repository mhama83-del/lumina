<?php
namespace App\Controllers;
class University extends BaseController
{
    public function dashboard()
    {
        if (session('role') !== 'university') session()->set(['role' => 'university']);
        return view('university/dashboard', ['title' => 'Lumina · University']);
    }
}
