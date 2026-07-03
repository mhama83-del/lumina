<?php
namespace App\Controllers;
class Employer extends BaseController
{
    public function index()
    {
        if (session('role') !== 'employer') session()->set(['role' => 'employer']);
        return view('employer/index', ['title' => 'Lumina · Employer']);
    }
}
