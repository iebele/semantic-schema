<?php namespace Iebele\SemanticSchema\Commands;

use Illuminate\Console\Command;
use Iebele\SemanticSchema\SchemaOrg as SchemaOrg;
use DOMDocument;
use DOMXPath;


/**
 *
 *
 * @copyright  Copyright (C) 2017 Iebele Abel
 * @license    Licensed under the MIT License; see LICENSE
 *
 * Some of methods are modified methods from Spider4Schema:  https://github.com/alexprut/Spider4Schema (Copyright (C) 2013 - 2015 Alex Prut)
 *
 *
 */

define('COMMAND_NAME', 'Semantic Schema');
define('CURL_TIMEOUT', 600);


class SchemaUpdate extends Command {



    /*
     * The URL to obtain all types from schema.org
     */
    protected  $allTypesUrl = "https://schema.org/docs/full.html";

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
        // Prevent errors
        libxml_use_internal_errors(true);
        // Start timer
        $time_start = microtime(true);

        $this->info('Updating. Connecting with Schema.org ');
        $schemaAllTypesDocument = $this->getDocument($this->allTypesUrl);

        if (!$schemaAllTypesDocument){
            $this->error('Error. Got empty document from Schema.org.');
            die();
        }

        $size = mb_strlen(serialize((array)$schemaAllTypesDocument['file']), '8bit');
        // end timer
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $this->info("Read " . $size . " bytes from " . $this->allTypesUrl . " in " . $execution_time . " seconds.");

        // Get types from file
        // Test a type
        // PreOrderAction
        $test[0] = "+";
        $test[1] = "Float";
        $test[2] = "Thing";
        $test[3] = "Audiobook";
        $test = null;

        if (!$test){
            $types = $this->parseAllTypes($schemaAllTypesDocument['file']);
        }
        else {
            foreach ($test as $type)
            $types[$type] = $type;
        }


        $this->info( "Found  " . count($types) . " types." );
        $this->info( PHP_EOL );


        // iterate over all types and store types and properties in table
        // For each Type retrieve all available Properties and information
        $count = 1;
        $warnings = [];
        $validTypes = [];
        foreach ($types as $typeName)
        {
            $this->info( "Parsing type: " . $typeName . " (type " . $count . " of " . count($types) . ")" );

            // https://schema.org/docs/full.html contains a reference "PaymentCard +" under "FinancialProduct", which results in an error
            //
            if ( preg_match("/^[A-Za-z0-9]*$/", $typeName) ){
                // Retrieve the Type HTML
                $typeDoc = $this->getDocument('http://schema.org/' . $typeName);
                $type = $this->parseType($typeDoc['file'], $typeName);

                if ($type['hasExtensionUrl'] ){
                    // $typeDoc = $this->getDocument('http://schema.org/' . $typeName);
                    $typeDoc = $this->getDocument($type['hasExtensionUrl']);
                    $type = $this->parseType($typeDoc['file'], $typeName);
                }

                // Create our validTypes object
                $validTypes[$typeName]['comment'] = $type['comment'];
                if ($type['properties']){
                    foreach ( $type['properties'] as $property => $value) {
                        $validTypes[$typeName]['properties'][$property]['name'] = $property;
                        $propertyFrom = str_replace('Properties from ', '', $value['propertyFrom']);
                        $validTypes[$typeName]['properties'][$property]['propertyFrom'] = $propertyFrom;
                        $validTypes[$typeName]['properties'][$property]['description'] = $value['description'];
                        if ($value['expectedTypes'] ){
                            $expectedTypes = $value['expectedTypes'];
                            foreach ($expectedTypes as $expectedType){
                                $validTypes[$typeName]['properties'][$property]['expectedTypes'][] = $expectedType;
                            }
                        }
                    }
                }
                else {
                    $validTypes[$typeName]['properties'] = [];
                    $warnings[] = "Warning: possible invalid type " . $typeName . ". This type might not be part of core ('pending').";
                }

                // Show validTypes in console

                $this->comment("Type: " . $typeName);
                $this->comment("  - Comment: " . $validTypes[$typeName]['comment']);
                $this->comment("  - Properties: ");
                foreach ( $validTypes[$typeName]['properties'] as $property ){
                    $this->comment("   - Name: "       . $property['name'] );
                    $this->comment("     - Property from: " .  $property['propertyFrom']);
                    $this->comment("     - Description: "   . $property['description']);
                    $this->comment("     - Expected types: ");
                    foreach ( $property['expectedTypes'] as $expectedType ){
                        $this->comment("       -  ". $expectedType);
                    }
                }


                // Wait some time, to not DDOS the Schema.org website
                $wait = (( rand(1, 10)/10 )+ 0.4 ) *1000000;
                $this->line( "Waiting " . $wait/1000000 . " seconds before new request to schema.org (prevent DDOS)" );
                usleep($wait);
                $this->info( PHP_EOL );
                $count++;
            }
            else {
                $warnings[] = "This type is invalid: " . $typeName . "  . This is a known bug in FinancialProduct (PaymentCard).";
            }

        }
        if ($warnings){
            $this->info( PHP_EOL );
            $this->line( "There were warnings :" );
            foreach ($warnings as $warning){
                $this->error( $warning );
                $this->info( PHP_EOL );
            }
        }

        libxml_use_internal_errors(false);

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
            $this->error('Error while making the HTTP request ('. $url . '): no HTML retrieved.');
            die();
        }
        if ( $response['status']['http_code'] != 200) {
            $status = $response['status']['http_code'];
            $this->error( 'Status: ' . $status);
            die();
        }

        return $response;
    }


    /**
     * Retrieve all available Types
     *
     * @param   string  $html  The retrieved HTML from the Type page
     *
     * @return	array
     */
    private function parseAllTypes($html)
    {
        // Create a new DOMDocument
        $doc = new DOMDocument;
        $doc->loadHTML($html);

        // Create a new DOMXPath, to make XPath queries
        $xpath = new DOMXPath($doc);
        $nodeList = $xpath->query("//li[@class='tbranch']/a | //li[@class='tleaf']/a");

        $types = array();

        foreach ($nodeList as $node) {
            // Sanitize the Type
            $type = str_replace('*', '', $node->nodeValue);

            $types[$type] = $this->removeSpaces($type);
        }

        return $types;
    }

    private function parseType($html, $typeName)
    {
        $this->info ( "parseType: Parsing type " .  $typeName .".");

        $type['comment']	= [];
        $type['extends']	= [];
        $type['properties']	= [];
        $type['hasExtensionUrl']= [];

        // Create a new DOMDocument
        $doc = new DOMDocument;
        $doc->loadHTML($html);

        // Create a new DOMXPath, to make XPath queries
        $xpath = new DOMXPath($doc);

        // Check if type is extension
        $nodeList = $xpath->query("//div[@id='mainContent']/h1");
        $isExtension = false;
        $extensionUrl = null;
        foreach ($nodeList as $node) {
            if ($node->nodeValue == "Schema.org Extensions") {
                $isExtension = true;
                $this->info($node->nodeValue);
            }
        }


        if ( $isExtension == true ) {
            $nodeList = $xpath->query("//div[@id='mainContent']/ul/li/a/@href");
            foreach ($nodeList as $node) {
                if ($node->nodeValue ) {
                    $extensionUrl = $node->nodeValue;
                    $this->info($node->nodeValue);
                }
            }
            $this->info ( $typeName .  " is defined in the extension : " . $extensionUrl  );
            $type['hasExtensionUrl'] = $extensionUrl;
            return $type;
        }



        $type['comment']	= $this->parseTypeComment($xpath);
        $type['extends']	= $this->parseTypeExtends($xpath);
        $type['properties']	= $this->parseTypeProperties($xpath, $typeName);

        // Debug
        //if (DEBUG === 'verbose')
        //    var_dump($type);


        $this->info ( "parseType: Parsed type " .  $typeName .".");
        return $type;
    }



    /**
     * Retrieve the Type comment
     *
     * @param   DOMXPath  $xpath  The Document object where to search
     *
     * @return	string
     */
    private function parseTypeComment(DOMXPath $xpath)
    {

        $this->info ( "parseTypeComment");

        $nodeList = $xpath->query("//div[@property='rdfs:comment']");

        $comment = '';

        foreach ($nodeList as $node)
        {
            $comment = $node->nodeValue;
        }

        $this->info ( "parseTypeComment - done");
        return $this->removeSpaces($comment);
    }

    /**
     * Retrieve the Type inherence if available
     *
     * @param   DOMXPath  $xpath  The Document object where to search
     *
     * @return	string
     */
    private function parseTypeExtends(DOMXPath $xpath)
    {
        $this->info ( "parseTypeExtends");
        $nodeList = $xpath->query("//h1[@class='page-title']");

        $tmpExtends = null;
        foreach ($nodeList as $node)
        {
            $tmpExtends = $node->nodeValue;
        }

        // Search for the Extended Type if available
        if (!$tmpExtends) {
            return '';
        }
        $types = explode('>', $tmpExtends);

        if (count($types) > 1)
        {
            return $this->removeSpaces($types[count($types) - 2]);
        }

        $this->info ( "parseTypeExtends - done");
        return '';
    }

    /**
     * Retrieve all available Properties of a given Type
     *
     * @param   DOMXPath  $xpath     The Document object where to search
     * @param   string    $typeName  The Type name
     *
     * @return	array
     */
    private function parseTypeProperties(DOMXPath $xpath, $typeName)
    {

        $this->info ( "parseTypeProperties");

        $properties = array();
        $from = null;

        // Control if properties available
        $nodeList = $xpath->query("(//thead[@class='supertype'])//a");
        foreach ($nodeList as $node)
        {
            $values = array();
            $childNodes = $node->childNodes;

            // Retrieve all available information
            foreach ($childNodes as $node)
            {
                if ($value = $this->removeSpaces($node->nodeValue))
                    $values[] = $value;
            }

            // Here we skip
            if (count($values) > 1 ){
                $expectedTypes = explode(' or ', $values[1]);

                // Create the final $property
                $properties[$values[0]] = array(
                    'propertyFrom' => $from,
                    'expectedTypes' => $expectedTypes,
                    'description' => $values[2]
                );
            }
            else { $from = $values[0]; }
        }


        // Return an empty array if there isn't any available property
        //if (!$nodeList->length || ($nodeList->item(0)->nodeValue != $typeName))
        //    return array();

        // Retrieve all Type Properties
        $nodeList = $xpath->query("(//tbody[@class='supertype'])[1]/tr");

        foreach ($nodeList as $node)
        {
            $values = array();
            $childNodes = $node->childNodes;

            // Retrieve all available information
            foreach ($childNodes as $node)
            {
                if ($value = $this->removeSpaces($node->nodeValue))
                    $values[] = $value;
            }

            // Here we skip
            if (count($values) > 1 ){
                $expectedTypes = explode(' or ', $values[1]);

                // Create the final $property
                $properties[$values[0]] = array(
                    'propertyFrom' => $from,
                    'expectedTypes' => $expectedTypes,
                    'description' => $values[2]
                );
            }
            else { $from = $values[0]; }
        }

        // Retrieve all Type Properties
        $nodeList = $xpath->query("(//tbody[@class='supertype'])[2]/tr");

        foreach ($nodeList as $node)
        {
            $values = array();
            $childNodes = $node->childNodes;

            // Retrieve all available information
            foreach ($childNodes as $node)
            {
                if ($value = $this->removeSpaces($node->nodeValue))
                    $values[] = $value;
            }

            // Here we skip
            if (count($values) > 1 ){
                $expectedTypes = explode(' or ', $values[1]);

                // Create the final $property
                $properties[$values[0]] = array(
                    'propertyFrom' => $from,
                    'expectedTypes' => $expectedTypes,
                    'description' => $values[2]
                );
            }
            else { $from = $values[0]; }

        }

        $this->info ( "parseTypeProperties - done ");

        //if (empty($properties))
        //    return array();

        return $properties;
    }


    /**
     * Remove multiple occurrences of whitespace characters
     * in a string an convert them all into single spaces.
     * Also remove &nbsp;
     *
     * @param  $string
     *
     * @return string
     */
    private function removeSpaces($string)
    {

        $string = preg_replace(array('/' . chr(0xC2) . chr(0xA0) . '/', '/\s+/'), ' ', $string);

        return trim($string);
    }

}