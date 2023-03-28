<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
throw new Exception('This application must be run on the command line.');
}

/**
* Returns an authorized API client.
* @return Google_Client the authorized client object
*/
function getClient()
{
$client = new Google_Client();
$client->setApplicationName('People API PHP Quickstart');
$client->setScopes(Google_Service_PeopleService::CONTACTS);
$client->setAuthConfig('/var/www/vhosts/fantik.kz/shopuchet.kz/files/Gmail_contacts/credentials.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');


// Load previously authorized token from a file, if it exists.
// The file token.json stores the user's access and refresh tokens, and is
// created automatically when the authorization flow completes for the first
// time.
$tokenPath = '/var/www/vhosts/fantik.kz/shopuchet.kz/files/Gmail_contacts/token.json';
if (file_exists($tokenPath)) {
$accessToken = json_decode(file_get_contents($tokenPath), true);
$client->setAccessToken($accessToken);
}

// If there is no previous token or it's expired.
if ($client->isAccessTokenExpired()) {
// Refresh the token if possible, else fetch a new one.
if ($client->getRefreshToken()) {
$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
} else {
// Request authorization from the user.
$authUrl = $client->createAuthUrl();
printf("Open the following link in your browser:\n%s\n", $authUrl);
print 'Enter verification code: ';
$authCode = trim(fgets(STDIN));

// Exchange authorization code for an access token.
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
$client->setAccessToken($accessToken);

// Check to see if there was an error.
if (array_key_exists('error', $accessToken)) {
throw new Exception(join(', ', $accessToken));
}
}
// Save the token to a file.
if (!file_exists(dirname($tokenPath))) {
mkdir(dirname($tokenPath), 0700, true);
}
file_put_contents($tokenPath, json_encode($client->getAccessToken()));
}
return $client;
}

function LoadContact($firstname, $email, $phonenumber)
{
	// Get the API client and construct the service object.
	$client = getClient();
	$service = new Google_Service_PeopleService($client);

	$person = new Google_Service_PeopleService_Person();
	    

	$name = new Google_Service_PeopleService_Name();
	$name->setGivenName($firstname);
	//$name->setFamilyName($secondname);
	$person->setNames($name);

	$email1 = new Google_Service_PeopleService_EmailAddress();
	$email1->setValue($email);
	$person->setEmailAddresses($email1);

	$phone1 = new Google_Service_PeopleService_PhoneNumber();	
	$phone1->setValue($phonenumber);
	$phone1->setType('home');
	$person->setPhoneNumbers($phone1);
	$exe = $service->people->createContact($person)->execute;
}

$link = mysqli_connect("localhost", "p-681_shop_wp", "R2H0dZrM", "p-681_shop_wp");
if ($link == false){
    echo "Error! Cannot connect to MySQL database " . mysqli_connect_error();
}
else {
    echo "Connected successfully\n";
}

$sql = 'SELECT name, phone, email, idPartner FROM Z_Client WHERE isImported = 0' ;
$result = mysqli_query($link, $sql);
$i = 0;
while ($row = mysqli_fetch_array($result)) {
	$sqlUsers = 'SELECT name, shortName FROM Z_users WHERE id = ' .$row['idPartner'];
	$resultUsers = mysqli_query($link, $sqlUsers);
	$rowUsers = mysqli_fetch_array($resultUsers);
	if (strcmp($rowUsers['shortName'], '') !== 0){
		$str = '(' .$rowUsers['shortName'] .')' .' ' .$row['name'];
	}
	else {
		$str = '(' .$rowUsers['name'] .')' .' ' .$row['name'];
	}
	
    LoadContact($str, $row['email'], $row['phone']);
    $i++;
    echo "Loaded ", $i, " contacts\n";
}
$sql = 'UPDATE Z_Client SET isImported = 1 WHERE isImported = 0';
$result = mysqli_query($link, $sql);

















