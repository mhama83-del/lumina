<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 * Lumina routes — Fasa 0-6 live.
 */

$routes->get('/', 'Home::index');
$routes->get('styleguide', 'Home::styleguide');
$routes->get('selftest', 'Home::selftest');
$routes->get('how-it-works', 'Home::architecture');
$routes->get('graph', 'Home::graph');

$routes->get('demo/(:segment)', 'Demo::enter/$1');

$routes->get('candidate', 'Candidate::home');
$routes->get('employer', 'Employer::index');
$routes->get('university', 'University::dashboard');
$routes->get('university/interventions', 'University::interventions');
$routes->get('university/students', 'University::students');
$routes->get('university/student/(:num)', 'University::student/$1');

// ---- Fasa 4B: employer DB browser + role detail + compare + shortlist ----
$routes->get('employer/role/(:num)', 'Employer::role/$1');
$routes->get('employer/candidate/(:num)', 'Employer::candidate/$1');
$routes->get('employer/compare',     'Employer::compare');
$routes->get('employer/shortlist',   'Employer::shortlist');

// ---- Fasa 3: candidate cold-start ----
$routes->get('start',           'Candidate::start');
$routes->get('start/sample',    'Candidate::sample');
$routes->match(['get', 'post'], 'onboard/animal', 'Candidate::animal');
$routes->match(['get', 'post'], 'onboard/input',  'Candidate::input');
$routes->get('passport',        'Candidate::passport');
$routes->get('resume',          'Candidate::resume');
$routes->post('resume/analyze', 'Candidate::resumeAnalyze');
$routes->post('resume/preview', 'Candidate::resumePreview');

// ---- Fasa 4: candidate career compass ----
$routes->get('compass', 'Candidate::compass');
$routes->post('whatif', 'Candidate::whatif');
$routes->post('compass/explore', 'Candidate::exploreCareer'); // Strategic B5: Career Explorer

// ---- Fasa 6.2: headless JSON API ----
$routes->match(['get', 'post'], 'api/analyze-resume', 'Api::analyzeResume');
$routes->match(['get', 'post'], 'api/build-profile',  'Api::buildProfile');
$routes->get('api/match-candidates',   'Api::matchCandidates');
$routes->get('api/compare-candidates', 'Api::compareCandidates');
$routes->get('api/cohort-insight',     'Api::cohortInsight');

// ---- Later ----
$routes->get('match',   'Candidate::smatch');
$routes->get('placed',  'Candidate::placed');
