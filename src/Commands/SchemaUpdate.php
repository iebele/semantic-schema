<?php namespace Iebele\SemanticSchema\Commands;

use Illuminate\Console\Command;
use Iebele\SemanticSchema\SchemaOrg as SchemaOrg;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Console\Helper\ProgressBar;

use Iebele\SemanticSchema\Models\SchemaTypes as SchemaTypes;


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
define('CURL_TIMEOUT', 6000);


class SchemaUpdate extends Command {


    /*
     * Print detailed information
     */
    protected  $verbose;



    protected $progressbar;

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
    protected $description = 'Update local tables with types and properties from schema.org.';

    /**
     *
     */




    public function handle()
    {

        // Prevent CTRL-C
        /*
        pcntl_signal(SIGINT, function ($signo) {
            echo "CATCH!\n";
            die();
        });
        */

        $this->fire();
    }

    /*
    * Wait between request to prevent DDOS Schema.org
    *
    */
    private function wait($seconds = null ){

        if ( $seconds == null ){
            $wait = (( rand(1, 10)/10 )+ 0.4 ) *500000;
        }
        else {
            $wait = $seconds*1000000;
        }
        $message = "Waiting " . $wait/1000000 . " seconds before new request to schema.org (prevent DDOS)";
        if ($this->verbose) $this->line( $message );
        //$this->progressbar->setMessage($message);
        usleep($wait);
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

        $this->info('Updating schema. Connecting with Schema.org. Please wait.');
        $schemaAllTypesDocument = $this->getDocument($this->allTypesUrl);

        if (!$schemaAllTypesDocument){
            $this->error('Error. Got empty document from Schema.org.');
            die();
        }


        // If they array 'test' is not null, only the types in the array are fetched. This is for debugging purposes only.
        //$test[] = "+";
        //$test[] = "Float";
        $test[] = "Thing";
        //$test[] = "Audiobook";
        //$test[] = "OccupationalTherapy";
        //$test[] = "FinancialProduct";
        //$test[] = "ConfirmAction";
        //$test[] = "LoanOrCredit";
        //$test[]  = "RsvpAction";
        //$test[]  = "CreditCard";
        //$test[] = "PreOrderAction";
        $test[] = "Action";
        $test[] = "LocalBusiness";
        $test[] = "CreativeWork";
        $test[] = "BlogPosting";
        $test = null;

        $this->verbose = false;

        if (!$test){
            $types = $this->parseAllTypes($schemaAllTypesDocument['file']);
        }
        else {
            foreach ($test as $type)
            $types[$type] = $type;
        }


        $size = mb_strlen(serialize((array)$schemaAllTypesDocument['file']), '8bit');
        // end timer
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        // Progressbar
        $this->progressbar = $this->output->createProgressBar(count($types) );
        $this->progressbar->setFormat("<info>%message%\n %current%/%max% [%bar%] %percent:3s%% %elapsed%</info>");

        $message = "Found " . count($types) . " types in document " . $this->allTypesUrl . " (". $size. " bytes) in " . $execution_time . " seconds.";
        $this->progressbar->setMessage($message);
        $this->progressbar->setProgress(1);

        $this->wait(2);


        // iterate over all types and store types and properties in table
        // For each Type retrieve all available Properties and information
        $count = 1;
        $warnings = [];
        $validTypes = [];
        foreach ($types as $typeName)
        {

            if ($this->verbose)  $this->info("Fetching type: " . $typeName . " (type " . $count . " of " . count($types) . ")" );
            $message= "Fetching type: " . $typeName ;
            $this->progressbar->setMessage($message);

            // https://schema.org/docs/full.html contains a reference "PaymentCard +" under "FinancialProduct", which results in an error
            //
            $validTypes[$typeName]['extensionUrl'] = 'http://schema.org/' . $typeName;
            if ( preg_match("/^[A-Za-z0-9]*$/", $typeName) ){
                // Retrieve the Type HTML
                $typeDoc = $this->getDocument('http://schema.org/' . $typeName);
                $type = $this->parseType($typeDoc['file'], $typeName);

                $validTypes[$typeName]['extends'] = $type['extends'];

                $extensionUrl = $type['hasExtensionUrl'];
                if ( $extensionUrl ){
                    $typeDoc = $this->getDocument($extensionUrl);
                    $type = $this->parseType($typeDoc['file'], $typeName);

                    $validTypes[$typeName]['extensionUrl'] = $extensionUrl;
                }



                // Create our validTypes object
                $validTypes[$typeName]['description'] = $type['description'];
                $validTypes[$typeName]['parents'] = $type['parents'];
                if ($type['properties']){
                    foreach ( $type['properties'] as $property => $value) {
                        $validTypes[$typeName]['properties'][$property]['name'] = $property;
                        $propertyFrom = str_replace('Properties from ', '', $value['propertyFrom']);
                        $validTypes[$typeName]['properties'][$property]['propertyFrom'] = $propertyFrom;
                        $validTypes[$typeName]['properties'][$property]['description'] = $value['description'];
                        $validTypes[$typeName]['properties'][$property]['url'] = $value['url'];
                        if ($this->verbose) $this->line("A: " . gettype($value['expectedTypes']));
                        if ($value['expectedTypes'] ){
                            $expectedTypes = $value['expectedTypes'];
                            foreach ($expectedTypes as $expectedType){
                                $validTypes[$typeName]['properties'][$property]['expectedTypes'][] = $expectedType;
                            }
                        }
                        else {
                            $validTypes[$typeName]['properties'][$property]['expectedTypes'] = [];
                        }
                    }
                }
                else {
                    $validTypes[$typeName]['properties'] = [];
                    //$warnings[] = "Warning: possible invalid type " . $typeName . ". This type might not be part of core ('pending').";
                }

                // Show validTypes in console

                if ($this->verbose) $this->comment("Type: " . $typeName );
                if ($this->verbose) $this->line("C: " . gettype($validTypes[$typeName]['extends']));
                if ($this->verbose) $this->comment("Extends: " . $validTypes[$typeName]['extends']);
                if ($this->verbose) $this->comment("extensionUrl: " . $validTypes[$typeName]['extensionUrl']);
                if ($this->verbose) $this->comment("    - Description: " . $validTypes[$typeName]['description']);
                if ($this->verbose) $this->comment("    - Properties: ");
                foreach ( $validTypes[$typeName]['properties'] as $property ){

                    if ($this->verbose) $this->comment("     - Name: "       . $property['name'] );
                    if ($this->verbose) $this->comment("         - Property from: " .  $property['propertyFrom']);
                    if ($this->verbose) $this->comment("         - Description: "   . $property['description']);
                    if ($this->verbose) $this->comment("         - Url: "   . $property['url']);
                    if ($this->verbose) $this->comment("         - Expected types: ");
                    if ( $property['expectedTypes'] ){
                        if ($this->verbose) $this->line("B: " . gettype($property['expectedTypes'] ));
                        foreach ( $property['expectedTypes'] as $expectedType ){
                            if ($this->verbose)  $this->comment("             -  ". $expectedType);
                        }
                    }

                }
                $extensionMsg = null;
                if ($validTypes[$typeName]['extensionUrl'] ) {
                    $extensionMsg = " (" . $validTypes[$typeName]['extensionUrl']  . ")";
                }
                $this->progressbar->setMessage($message . $extensionMsg . ", " .count($validTypes[$typeName]['properties']) . " properties."  );


                if ($this->verbose) $this->info( PHP_EOL );
                $count++;
            }
            else {
                $warnings[] = "This type is invalid: " . $typeName . "  . This is a known bug in FinancialProduct (PaymentCard).";
            }

            
            // Store types and properties in database
            //
            //
            //
            $type = SchemaTypes::addType( $typeName, $validTypes[$typeName]['description'], $validTypes[$typeName]['extends'], $validTypes[$typeName]['extensionUrl'] , $validTypes[$typeName]['parents']);
            if ( $type ){
                // Add properties
                foreach ( $validTypes[$typeName]['properties'] as $property ){
                    $type->addPropertyToType( $typeName, $property['name'], $property['description'], $property['url'] , $property['expectedTypes'] );
                }
            }
            else {
                $this->progressbar->setMessage("Could not store " . $typeName . " in database"  );
                $this->progressbar->setProgress($count);
                $this->progressbar->finish();
                $this->error ("Error");
            }

            //if ($type == null){
            //    $this->progressbar->setMessage($typeName . " already exists in local database.");
            //}

            $this->progressbar->setProgress($count);
            // Wait some time, to not DDOS the Schema.org website
            $this->wait();

        }

        $this->progressbar->setMessage("Fetched all types and properties from schema.og");
        $this->progressbar->finish();
        $this->info(PHP_EOL);
        $this->info("Updating schema_parent_type table");
        if (SchemaTypes::updateParents()){
            $this->info("Ready.");
        }
        else {
            $this->comment("Warning: Error updating schema_parent_type table.");
            if ($test!=null){
                $this->comment("This warning probably occured because you were testing schema:update with only a few types.");
            }
        }


        if ($warnings){
            $this->comment( "There were some warnings:" );
            foreach ($warnings as $warning){
                $this->comment( $warning );
            }
        }

        libxml_use_internal_errors(false);

    }


    private function getDocument($url)
    {
        if ($this->verbose) $this->info($url);

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

        $previous  = "";
        foreach ($nodeList as $node) {
            // Sanitize the Type
            $type = str_replace('*', '', $node->nodeValue);


            // prevent bug in "PaymentCard" and "LocalBusiness"
            if ( $this->removeSpaces($type) != "+"){
                $types[$type] = $this->removeSpaces($type);
                $previous = $node->nodeValue;
            }
            else {
                $types[$type] = $previous;
            }
        }

        return $types;
    }

    private function parseType($html, $typeName)
    {
        $message =  "Parsing type " .  $typeName .".";
        if ($this->verbose)  $this->info($message);
        $this->progressbar->setMessage($message);

        $type['description']	= '';
        $type['extends']	    = '';
        $type['properties']	    = [];
        $type['hasExtensionUrl']= '';

        // Create a new DOMDocument
        $doc = new DOMDocument;
        $doc->loadHTML($html);

        // Create a new DOMXPath, to make XPath queries
        $xpath = new DOMXPath($doc);

        // Check if type is extension
        $nodeList = $xpath->query("//div[@id='mainContent']/h1");
        $isExtension = false;
        $extensionUrl = null;
        $extensionPrefix = null;
        foreach ($nodeList as $node) {
            if ($node->nodeValue == "Schema.org Extensions") {
                $isExtension = true;
                if ($this->verbose) $this->info($node->nodeValue);
            }
        }

        if ( $isExtension == true ) {
            // get link
            $nodeList = $xpath->query("//div[@id='mainContent']/ul/li/a/@href");
            foreach ($nodeList as $node) {
                if ($node->nodeValue ) {
                    $extensionUrl = $node->nodeValue;
                    if ($this->verbose) $this->info($extensionUrl);
                }
            }

            $message =  $typeName .  " is defined in the extension  : " . $extensionUrl;
            if ($this->verbose)  $this->info($message);
            $this->progressbar->setMessage($message);

            $type['hasExtensionUrl'] = $extensionUrl;
            return $type;
        }

        $type['parents']= $this->parseTypeParents($xpath);
        $type['description']= $this->parseTypeComment($xpath);
        $type['extends']	= $this->parseTypeExtends($xpath);
        $type['properties']	= $this->parseTypeProperties($xpath, $typeName);

        if ($this->verbose) $this->info ( "parseType: Parsed type " .  $typeName .".");
        return $type;
    }


    /**
     * Retrieve the Type parents
     *
     * @param   DOMXPath  $xpath  The Document object where to search
     *
     * @return	string
     */
    private function parseTypeParents(DOMXPath $xpath)
    {

        if ($this->verbose) $this->info ( "parseParents");

        $nodeList = $xpath->query("//span[@class='breadcrumbs']");


        $parents = [];
        foreach ($nodeList as $node)
        {
            if ($this->verbose) $this->info ($node->nodeValue);

            $value = $this->removeSpaces($node->nodeValue);
            $list = explode(' > ',  $value);
            // Find parent
            if ($this->verbose)  $this->info("List count = " . count($list) );
            if (count($list) == 1 ) {
                $parents[] = $list[0]; // parent is self, first element
            }
            elseif (count($list) == 2 ) {
                $parents[] = $list[0]; // parent of two levels is first element
            }
            else {
                $parents[] = $list[count($list)-2];
            }
        }

        if ($this->verbose)  $this->info ( "parseParents - done");
        return $parents;
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

        if ($this->verbose) $this->info ( "parseTypeComment");

        $nodeList = $xpath->query("//div[@property='rdfs:comment']");

        $comment = '';

        foreach ($nodeList as $node)
        {
            $comment = $node->nodeValue;
        }

        if ($this->verbose) $this->info ( "parseTypeComment - done");
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
        if ($this->verbose) $this->info ( "parseTypeExtends");
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

        if ($this->verbose) $this->info ( "parseTypeExtends - done");
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

        if ($this->verbose) $this->info ( "parseTypeProperties");

        $properties = array();
        $from = null;
        $urls = [];

        // Control if properties are available
        $nodeList = $xpath->query("(//thead[@class='supertype'])//a");
        foreach ($nodeList as $node)
        {
            $values = array();
            $childNodes = $node->childNodes;

            // Retrieve all available information
            foreach ($childNodes as $node)
            {
                if ($value = $this->removeSpaces($node->nodeValue)){
                    $values[] = $value;
                }
            }

            // Here we skip row 'Properties from... '
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


        // Get url of property
        $nodeList = $xpath->query("(//tbody[@class='supertype'])[*]/tr/th/code/a/@href");
        foreach ($nodeList as $node)
        {
            $childNodes = $node->childNodes;

            foreach ($childNodes as $node)
            {
                // We accept empty strings or single words (No rows like 'Properties from CommunicateAction')
                if ( $node->nodeValue == "" || $this->removeSpaces($node->nodeValue)){
                    $urls[] = $node->nodeValue;
                }

            }
        }

        // Retrieve all Type Properties
        $nodeList = $xpath->query("(//tbody[@class='supertype'])[*]/tr");
        $count = 0 ;
        foreach ($nodeList as $node)
        {
            $values = array();
            $childNodes = $node->childNodes;

            // Retrieve all available information
            foreach ($childNodes as $node)
            {
                // We accept empty strings or single words (No rows like 'Properties from CommunicateAction')
                if ( $node->nodeValue == "" || $this->removeSpaces($node->nodeValue)){
                    if ( $node->nodeValue == "" ) {
                        $values[] = null;
                    }
                    else {
                        $values[] = $this->removeSpaces($node->nodeValue);
                    }
                }

            }
            // Here we skip row 'Properties from... '
            if (count($values) == 3 ){
                $expectedTypes = explode(' or ', $values[1]);
                // Create the final $property
                //$this->line($values[0]);
                $properties[$values[0]] = array(
                    'propertyFrom' => $from,
                    'expectedTypes' => $expectedTypes,
                    'description' => $values[2],
                    'url' => $urls[$count]
                );
                $count++;
            }
            else if (count($values) == 2 ){
                // Create the final $property
                //$this->line($values[0]);
                $properties[$values[0]] = array(
                    'propertyFrom' => $from,
                    'expectedTypes' => [],
                    'description' => $values[1],
                    'url' => $urls[$count]
                );
                $count++;
            }
            else {
                //$this->error("Could not parse property " . $values[0]);
                //die();
                $from = $values[0];
            }

        }

        if ($this->verbose) $this->info ( "Done: parseTypeProperties");
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