<?php
/**
 * Created by PhpStorm.
 * User: Daniel-Paz-Horta
 * Date: 9/22/17
 * Time: 10:27 AM
 */

try {

    include_once(__DIR__ . '/../src/aits_grades.php');

    print_r($argv);

    // AITS Sender APP ID
    if(empty($argv[1])){

        throw new \Exception("Error: Specify senderAppId ID as provided from AITS as the 3rd argument.");

    }

    // UIN
    if(empty($argv[2])){

        throw new \Exception("Error: Specify UIN as the 4th argument.");

    }

    // Call the AITS Term API
    $termAPI = new dpazuic\aitsGrades($argv[2], $argv[1], empty($argv[3]) ? null : $argv[3]);
    $termAPI = new dpazuic\aitsGrades("","","");

    // Get the results of a call
    $termAPI->getAITSGrades();

    print_r($termAPI->getResponse('raw'));

} catch (\Exception $e){

    print_r($e->getMessage());
    echo PHP_EOL;
    echo PHP_EOL;

}