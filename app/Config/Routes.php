<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 * Lumina routes — Fasa 0-3 live; Fasa 4+ stubbed (no 404s).
 */

$routes->get('/', 'Home::index');
$routes->get('styleguide', 'Home::styleguide');   // Fasa 0 check
$routes->get('selftest', 'Home::selftest');       // Fasa 1 check

// One-click login / role + stage switch
$routes->get('demo/(:segment)', 'Demo::enter/$1');

// Role landings
$routes->get('candidate', 'Candidate::home');
$routes->get('employer', 'Employer::index');
$routes->get('university', 'University::dashboard');

// ---- Fasa 3: candidate cold-start ----
$routes->get('start',           'Candidate::start');
$routes->get('start/sample',    'Candidate::sample');
$routes->match(['get', 'post'], 'onboard/animal', 'Candidate::animal');
$routes->match(['get', 'post'], 'onboard/input',  'Candidate::input');
$routes->get('passport',        'Candidate::passport');

// ---- Stubs for later phases ----
$routes->get('compass', 'Candidate::compass');        // Fasa 4
$routes->post('whatif', 'Candidate::whatif');         // Fasa 4 AJAX
$routes->get('match',   'Candidate::smatch');    // Fasa 5  (method renamed: 'match' is a PHP keyword)
$routes->get('placed',  'Candidate::placed');    // Fasa 6
