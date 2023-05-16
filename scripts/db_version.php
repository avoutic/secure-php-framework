<?php

// Global configuration
//
if (!file_exists(__DIR__.'/../vendor/autoload.php'))
{
    exit('Composer not initialized');
}

require_once __DIR__.'/../vendor/autoload.php';

use WebFramework\Core\WF;

$framework = new WF();

header('content-type: text/plain');

try
{
    // Initialize WF
    //
    $framework->skip_app_db_version_check();
    $framework->skip_wf_db_version_check();
    $framework->init();

    $db_manager = $framework->get_database_manager();

    $current_version = $db_manager->get_current_version();

    // Verify database scheme hash
    //
    var_dump($db_manager->verify_hash());
}
catch (Throwable $e)
{
    echo('Unhandled exception');

    if ($framework->get_config('debug') == true)
    {
        echo($e->getMessage().PHP_EOL);
        print_r($e->getTrace());
    }

    if (!$e instanceof WebFramework\Core\VerifyException)
    {
        WF::report_error($e->getMessage(), $e->getTrace());
    }

    exit();
}
