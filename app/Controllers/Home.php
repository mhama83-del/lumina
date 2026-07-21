<?php
namespace App\Controllers;

class Home extends ContinuumController
{
    public function index()   { return $this->shell('home', [], 'public'); }
    public function architecture() { return $this->shell('architecture', [], 'public'); }
}
