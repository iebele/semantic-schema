<?php namespace Iebele\SemanticSchema\Commands;

use Illuminate\Console\Command;


/**
 *
 *
 * @copyright  Copyright (C) 2017 Iebele Abel
 * @license    Licensed under the MIT License; see LICENSE
 *
 * Some of the methods used in this class are inspired on 4Schema, Copyright (C) 2013 - 2015 Alex Prut
 *
 */

define('COMMAND_NAME', 'Semantic Schema');
define('CURL_TIMEOUT', 600);


class SchemaUpdate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'schema:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tables with Schema.org data.';

    /**
     *
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {

        $time_start = microtime(true);

        $this->info('Updating. Connecting with Schema.org ');

        /* todo iterate over all main types */
        $typeName="CreativeWork";

        $schemaDocument = $this->getDocument("http://schema.org/".$typeName);

        //$type = parseType($type['file'], $typeName);

        if (!$schemaDocument){
            $this->error('Error. Got empty document from Schema.org.');
        }

        $size = mb_strlen(serialize((array)$schemaDocument['file']), '8bit');

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        $this->info("Received " . $size . " bytes of information for type " . $typeName . " in " . $execution_time . " seconds.");
        // var_dump($schemaDocument['file']);
    }


    private function getDocument($url)
    {
        $this->info($url);

        $curl = curl_init();

        // Setup the target
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        // Return in string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Timeout
        curl_setopt($curl, CURLOPT_TIMEOUT, CURL_TIMEOUT);

        // Webbot name
        curl_setopt($curl, CURLOPT_USERAGENT, COMMAND_NAME);

        // Minimize logs
        curl_setopt($curl, CURLOPT_VERBOSE, false);

        // Limit redirections to 4
        curl_setopt($curl, CURLOPT_MAXREDIRS, 4);

        // Follow redirects
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // Create return array
        $response['file']	= curl_exec($curl);
        $response['status']	= curl_getinfo($curl);
        $response['error']	= curl_error($curl);

        // Execute the request
        curl_exec($curl);

        // Close the handler
        curl_close($curl);

        // Check for errors
        if ( $response['error'] ) {
            $this->error( $response['error']);
            die();
        }
        if ($response['file'] == ''){
            $this->error('Error while making the HTTP request: no HTML retrieved.');
            die();
        }
        if ( $response['status'] ) {
            $status = $response['status']['http_code'];
            $this->info( 'Status: ' . $status);
        }

        return $response;
    }
}