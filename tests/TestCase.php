<?php

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        // return require __DIR__.'/../bootstrap/app.php';
    }

    /** @test */
    public function getTypes(){

        $this->assertEquals( 0, 0 );
    }
}
