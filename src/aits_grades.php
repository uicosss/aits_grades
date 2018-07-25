<?php
/**
 * Created by PhpStorm.
 * User: Daniel-Paz-Horta
 * Date: 9/22/17
 * Time: 10:27 AM
 */

namespace dpazuic;


class aitsGrades
{

    /**
     * The UIN you are querying for
     *
     * @var string
     */
    protected $uin;

    /**
     * The Period/Term you are querying for
     *
     * @var string
     */
    protected $term;

    /**
     * The senderAppID provided by AITS when registering your app's access with the AITS Term API
     *
     * @var string
     */
    protected $senderAppID;

    /**
     * The result of an AITS term request call
     *
     * @var object
     */
    protected $response;

    /**
     * aitsGrades constructor.
     *
     * @param null $uin
     * @param null $senderAppID
     * @param null $campus
     * @param null $term
     * @throws \Exception
     */
    public function __construct($uin = NULL, $senderAppID = NULL, $term = NULL)
    {

        // Compute campus from $term if it is a Banner Term Code
        if(empty($uin)){

            // Throw an Error
            throw new \Exception('UIN cannot be blank. Provide a fully qualified UIN.');

        }

        // Set the UIN to be queried
        $this->setUIN($uin);

        // Set the AITS provided SenderAppId
        $this->setSenderAppId($senderAppID);

        // Set the term that is to be queried
        $this->setTerm($term);

    }

    /**
     * Void method that validates and sets the $term property
     *
     * @param $term
     */
    private function setUIN($uin)
    {

        // Validate Period
        if(!empty($term)) {
            $uin = $this->checkUIN($uin);
        }

        $this->uin = $uin;

    }


    /**
     * Void method that validates and sets the $term property
     *
     * @param $term
     */
    private function setTerm($term)
    {

        // Validate Period
        if(!empty($term)) {
            $term = $this->checkTermCode($term);
        }

        $this->term = $term;

    }

    /**
     * Void method that sets the senderAppID property
     *
     * @param $senderAppID
     * @throws \Exception
     */
    private function setSenderAppId($senderAppID)
    {

        // Check to see if the $senderAppID was set
        if(empty($senderAppID)) {

            throw new \Exception('The senderAppId cannot be blank. Please contact AITS for a senderAppId');

        }

        $this->senderAppID = $senderAppID;

    }

    /**
     * Method that returns the value of $this->senderAppID
     *
     * @return string
     */
    public function getSenderAppId()
    {

        return $this->senderAppID;

    }

    /**
     * Method that returns the value of $this->response
     *
     * @return object
     */
    public function getResponse($outputFormat = 'json')
    {

        // Validate format
        $outputFormat = $this->checkFormatParam($outputFormat);


        switch(strtolower($outputFormat)) {
            case 'raw':
                // Convert to Array
                $array = json_decode($this->response->data);

                // Check to see that $obj is an object, thus has data
                if (!is_array($array)) {

                    throw new \Exception('Communication with the AITS term API is not available. Try again later.');

                }
                return $array;
                break;

            case 'json':
            default:
                return $this->response->data;
                break;
        }

    }

    /**
     * @return string
     */
    public function getUin()
    {
        return $this->uin;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Method used to communicate with the AITS Term API and return data in a specified format
     * https://www.aits.uillinois.edu/cms/One.aspx?portalId=558&pageId=632773
     *
     * @param string $outputFormat
     */
    public function getAITSGrades()
    {

        // AITS Term API Source
        $source = 'https://webservices-dev.admin.uillinois.edu/epWS/StudentApi/api/students/' . $this->uin . '/aitsGrades';

        if(!empty($this->term)) {

            $source .= '?term=' . $this->term;

        }

        // Initialize a curl resource
        $curl = curl_init();

        // Set curl options
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $source);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array(
                "X-senderappid: " . $this->senderAppID
            )
        );

        // JSON Response
        $response = curl_exec($curl);

        // todo - should check for a 200 from AITS service


        // Cache the response in $this->response
        $this->response = new \stdClass();
        $this->response->type = 'JSON';
        $this->response->data = $response;

    }

    /**
     * Method used to validate the term code against expected values
     *
     * @param $term string ([0-9]{6}|current|nextterm|lastterm|nextsemester|lastsemester|nextyear|lastyear)
     * @return string
     * @throws Exception
     */
    private function checkTermCode($term)
    {

        // Convert $campus to lowercase
        $term = strtolower($term);

        // Clean the code, check that it matches
        $termArray = preg_grep("/^([0-9]{6})$/", explode("\n", $term));

        if(empty($termArray)){

            // Throw exception, banner term code is not valid
            throw new \Exception('The term code: "' . $term . '" is not a valid. Accepted values are valid Banner Term Codes ie. "120168", "220171", "420168" or relative periods: "current", "nextTerm", "lastTerm", "nextSemester", "lastSemester"');

        }

        // Check to see if the $term specified is a banner term code, thus checking for digits,
        // specifically first digit of string
        $bannerTermArray = preg_grep("/^([0-9]{6})$/", explode("\n", $term));
        if(count($bannerTermArray) == 1){

            // Get the first digit from $term, cast it as an integer (just in case it comes back as a string)
            $termFirstDigit = (int) substr($term, 0 , 1);

            // Check that $term is a valid term (prefixed with a 1, 2 or 4
            if($termFirstDigit == 1 OR $termFirstDigit == 2 OR $termFirstDigit == 4){

                return $term;

            } else {

                // Throw exception, banner term code is not valid
                throw new \Exception('The term code: "' . $term . '" is not a valid Banner Term code.');
            }

        }

        return $term;

    }

    /**
     * Method used to validate the uin against an expected format
     *
     * @param $uin
     * @return mixed
     * @throws \Exception
     */
    private function checkUIN($uin)
    {

        // Clean the code, check that it matches
        $uinArray = preg_grep("/^([0-9]{9})$/", explode("\n", $uin));

        if(empty($uinArray)){

            // Throw exception, banner term code is not valid
            throw new \Exception('The uin: "' . $uin . '" is not a valid.');

        }

        return $uin;

    }


    /**
     * Method used to validate the format for the response
     *
     * @param $format (json|xml)
     * @return string
     * @throws Exception
     */
    private function checkFormatParam($format)
    {
        $format = strtolower($format);

        // Clean the code, check that it matches
        $formatArray = preg_grep("/^(json|raw)$/", explode("\n", $format));

        if(empty($formatArray)){

            // Throw exception, banner term code is not valid
            throw new \Exception('The format: "' . $format . '" is not supported. Use json or xml');

        }

        return $format;

    }

}