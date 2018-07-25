# aits_grades

PHP Library for using the AITS Grades API (contact AITS for additional details on API)

## Usage
To use the library, you need to:

### Include library in your program 
```
include_once(aits_grades.php');
```
### or use composer `composer require dpazuic\aits_grades`
```
include_once('vendor/autoload.php');
```
### Instantiate an object of class `dpazuic\aits_grades`
```
$uin = '123456789'; 
$senderAppID = 'YOUR_SENDER_APP_ID'; // Contact AITS for this
$term = '220181'; // Optional
$gradesAPI = new dpazuic\aits_grades($uin, $senderAppID, $term); // Includin 3rd argument, term, is optional
```
**Note**: By default the third argument, term, is optional. If term is provided, the API fetches the grade information for the provided term. If the term is not provided, the API fetches the grade information for each term the student has registered courses.


### Getting Results from an API call
The default response will be JSON, but you can also request the raw data which will be an array of StdClass objects.
```
$gradesAPI->getAITSTerms();
$response = $gradesAPI->getResponse('JSON');
```

## Examples:
You can use the attached `examples/cli-test.php` file from the command line to test functionality.
`php cli-test.php YOUR_SENDER_APP_ID UIN`