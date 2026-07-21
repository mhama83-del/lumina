<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Continuum V2 routes — role-protected groups, no public numeric IDs in candidate URLs (03).
 * Legacy V1 routes are NOT carried forward. Demo routes exist only under DEMO_MODE.
 */
/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');
$routes->get('how-it-works', 'Home::architecture');

// --- Candidate workspace ---
$routes->group('candidate', ['filter' => 'workspace:candidate'], static function ($routes) {
    $routes->get('home', 'Candidate::home');
    $routes->get('evidence', 'Candidate::evidence');
    $routes->match(['get', 'post'], 'survey', 'Candidate::survey');
    $routes->post('evidence/add', 'Candidate::addEvidence');
    $routes->get('roles/(:segment)', 'Candidate::roleContext/$1');
    $routes->match(['get', 'post'], 'roles/(:segment)/apply', 'Candidate::apply/$1');
    $routes->get('applications/(:num)', 'Candidate::application/$1');
    $routes->post('applications/(:num)/respond', 'Candidate::respondClarification/$1');
});

// --- Employer workspace ---
$routes->group('employer', ['filter' => 'workspace:employer'], static function ($routes) {
    $routes->get('roles', 'Employer::roles');
    $routes->match(['get', 'post'], 'roles/compose', 'Employer::compose');
    $routes->post('roles/(:num)/publish', 'Employer::publish/$1');
    $routes->get('roles/(:num)/review', 'Employer::reviewQueue/$1');
    $routes->get('review/(:num)', 'Employer::candidateReview/$1');
    $routes->post('review/(:num)/status', 'Employer::changeStatus/$1');
    $routes->post('review/(:num)/clarify', 'Employer::requestClarification/$1');
    $routes->post('review/(:num)/feedback', 'Employer::releaseFeedback/$1');
});

// --- University workspace (aggregate only by default) ---
$routes->group('university', ['filter' => 'workspace:university'], static function ($routes) {
    $routes->get('cohorts/(:num)', 'University::cohort/$1');
    $routes->post('cohorts/(:num)/intervene', 'University::createIntervention/$1');
});

// --- Talentbank operator ---
$routes->group('operator', ['filter' => 'workspace:operator'], static function ($routes) {
    $routes->get('control-tower', 'Operator::controlTower');
    $routes->post('exceptions/(:num)/remind', 'Operator::remind/$1');
});

// --- Demo scenario switcher (DEMO_MODE only; NEVER a production auth mechanism) ---
$routes->group('demo', ['filter' => 'demoOnly'], static function ($routes) {
    $routes->get('scenarios', 'Demo::scenarios');
    $routes->get('scenarios/(:segment)', 'Demo::enter/$1');
    $routes->get('reset', 'Demo::reset');
});
