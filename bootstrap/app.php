<?php

/**
 *  Slim Application setting
 *  and bootstrapping
 */

session_start();

// Unset all of the session variables.
if (isset($_COOKIE['PHPSESSID'])) {
  setcookie('PHPSESSID', '', time() - 3600, '/');
}

if (isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'declined') {
  setcookie('_ga', '', time() - 3600, '/');
  setcookie('_gid', '', time() - 3600, '/');
}

// Require composer autoloader
require __DIR__ . '/../vendor/autoload.php';


// Application settings
$conf = require __DIR__ . '/../app/config.php';
$stngs = require __DIR__ . '/../app/settings.php';

// Merge settings in a single object
$settings = array_merge($conf, $stngs);
// Add settings vars to globals
$settings['globals']['absoluteURL'] = $settings['settings']['absoluteURL'];
$settings['globals']['absoluteURLMail'] = $settings['settings']['absoluteURLMail'];
$settings['globals']['containerURL'] = $settings['settings']['containerURL'];
$settings['globals']['baseURL'] = $settings['settings']['baseURL'];
$settings['globals']['diskPath'] = $settings['settings']['diskPath'];
$settings['globals']['wfDataPath'] = $settings['settings']['wfDataPath'];
$settings['globals']['scriptsGlobals'] = $settings['settings']['scriptsGlobals'];
$settings['globals']['scriptsLocal'] = $settings['settings']['scriptsLocal'];
$settings['globals']['mailTemplates'] = $settings['settings']['mailTemplates'];
$settings['globals']['scriptsPath'] = $settings['settings']['scriptsPath'];
$settings['globals']['analysisPath'] = $settings['settings']['analysisPath'];
$settings['globals']['affinity'] = $settings['settings']['affinity'];
$settings['globals']['ga4'] = $settings['settings']['ga4'];
$settings['globals']['sge'] = $settings['settings']['sge'];
$settings['globals']['docker'] = $settings['settings']['docker'];

// New Slim app instance
$app = new Slim\App( $settings );


// Add our dependencies to the container
require __DIR__ . '/../app/dependencies.php';


// Require our route
require __DIR__ . '/../app/routes.php';
