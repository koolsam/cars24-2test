<?php
namespace CleverTap;

/**
 * CleverTap PHP (unofficial) - Push events from your PHP application
 *
 * @author Saneem <hello@saneem.me>
 * @license MIT License
 * @version 0.1
 *
 */
use Yii;
require \Yii::getAlias('@vendor/guzzlehttp/psr7/src/functions.php');
require \Yii::getAlias('@vendor/guzzlehttp/promises/src/functions.php');

class Clevertap
{
    const API_URL  = 'https://api.clevertap.com/1/upload';
    


    /**
     * Push the user's profile
     * More details at https://support.clevertap.com/docs/api/working-with-user-profiles.html#uploading-user-profiles
     *
     * @param string $identity Identity to recognize a user uniquely.
     * @param array $data This will be pushed as the user's info.
     *				Example: [
     *				     		'Name' => 'Jack Montana',
     *							'Email' => 'jack@gmail.com',
     *							'CustomerCategory' => 'Diamond'
     *						 ]
     * @return mixed
     */
    public function setProfile($identity, $data)
    {
    	$this->identity = $identity;

    	$profile = [
    		'identity' => $identity,
    		'ts' => time(),
    		'type' => 'profile',
    		'profileData' => $data
    	];
    	
    	$body = json_encode([
    		'd' => [
    			$profile
    		]
    	]);
    	try {
    	    $client = new \GuzzleHttp\Client();
    	    $response = $client->request(
    	        'POST',
    	        self::API_URL,
    	        ['headers' => [
    	         		'X-CleverTap-Account-Id' => $this->account_id,
    	         		'X-CleverTap-Passcode' => $this->passcode,
    	                'Content-Type' => 'application/json'
    	         ],
    	         'body' => $body,
    	         'connect_timeout' => $this->timeout
    	        ]
    	    );
            
    	    $response_json = json_decode((string) $response->getBody(), true);
    	    if(!empty($response_json)) {
    	        return $response_json;
    	    } else {
    	        return NULL;
    	    }
    	} catch (\Exception $e) {
    		error_log("Exception in CleverTap setProfile");
    	    return NULL;
    	}
    }

    /**
     * Push an event
     * More details at https://support.clevertap.com/docs/api/working-with-events.html#upload-events
     *
     * @param string $event_name Name of the event
     * @param array $event_data Any information that has to be pushed with the event
     * @return mixed
     */
    public function pushEvent($event_name, $event_data)
    {
        
    	$event = [
    		'type' => 'event',
    		'evtName' => $event_name,
    		'ts' => time(),
    		'identity' => $event_data['identity'],
    		'evtData' => $event_data['evtData']
    	];

    	$body = json_encode([
    		'd' => [
    			$event
    		]
    	]);
        $client = new \GuzzleHttp\Client();
    	    $response = $client->request(
    	        'POST',
    	        self::API_URL,
    	        ['headers' => [
    	         		'X-CleverTap-Account-Id' => $this->account_id,
    	         		'X-CleverTap-Passcode' => $this->passcode,
    	                'Content-Type' => 'application/json'
    	         ],
    	         'body' => $body,
    	         'connect_timeout' => $this->timeout
    	        ]
    	    );
    	    
    	    $response_json = json_decode((string) $response->getBody(), true);
            if(!empty($response_json['unprocessed'])) {
                throw new \Exception($response->getBody());
                return $response_json;
            } else {
                return $response_json;
            }
    }
}
