<?php defined('SYSPATH') or die('No direct script access.');
/**
 * J2ME Controller
 * Handles the tasks the J2ME app can't handle by itself or
 * through the API like GeoCoding
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     J2ME Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
*/

class J2me_Controller extends Controller
{
	public function __construct()
    {
        parent::__construct();
		//$profile = new Profiler;
	}
	
	public function index()
	{
		$incident_title = "";
		$incident_description = "";
		$incident_date = "";
		$incident_hour = "";
		$incident_minute = "";
		$incident_ampm = "";
		$incident_category = 0;
		$latitude = "";
		$longitude = "";
		$location_name = "";
		$person_first = "";
		$person_last = "";
		$incident_photo = "";

		if (isset($_GET['incident_title']))
			$incident_title = $_GET['incident_title'];
		if (isset($_GET['incident_description']))
			$incident_description = $_GET['incident_description'];
		if (isset($_GET['incident_date']))
			$incident_date = $_GET['incident_date'];
		if (isset($_GET['incident_hour']))
			$incident_hour = $_GET['incident_hour'];
		if (isset($_GET['incident_minute']))
			$incident_minute = $_GET['incident_minute'];
		if (isset($_GET['incident_ampm']))
			$incident_ampm = $_GET['incident_ampm'];
		if (isset($_GET['incident_category']))
			$incident_category = $_GET['incident_category'];
		if (isset($_GET['latitude']))
			$latitude = $_GET['latitude'];
		if (isset($_GET['longitude']))
			$longitude = $_GET['longitude'];
		if (isset($_GET['location_name']))
			$location_name = $_GET['location_name'];
		if (isset($_GET['person_first']))
			$person_first = $_GET['person_first'];
		if (isset($_GET['person_last']))
			$person_last = $_GET['person_last'];

		if (isset($_GET['image_name']))
			$incident_photo = $_GET['image_name'];

		// On the fly GeoCoding with Google Maps
		$key = Kohana::config('settings.api_google');
		
		$url = "http://maps.google.com/maps/geo?q=".urlencode($location_name)."&output=csv&key=".$key;
		echo $url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($ch);
		curl_close($ch);

		// Check our Response code to ensure success
		if (substr($data,0,3) == "200")
		{
			$data = explode(",",$data);

			$accuracy = $data[1]; // [0-9], 9=Most Detail
			$latitude = $data[2];
			$longitude = $data[3];
			
			$incident_category = serialize( array(intval($incident_category)) );

			$mobile_post = array(
			  'task' =>'report',
			  'incident_title' => $incident_title,
			  'incident_description' => $incident_description, 
			  'incident_date' => date("m/d/Y"), 
			  'incident_hour' => $incident_hour, 
			  'incident_minute' => $incident_minute,
			  'incident_ampm' => $incident_ampm,
			  'incident_category' => $incident_category,
			  'latitude' => $latitude,
			  'longitude' => $longitude, 
			  'location_name' => $location_name,
			  //'incident_photo[]' => $incident_photo
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER,1);
	   		curl_setopt($ch, CURLOPT_URL, url::site().'api' );
	   		curl_setopt($ch, CURLOPT_POST, 1 );
	   		//print_r($mobile_post);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1 );
	   		curl_setopt($ch, CURLOPT_POSTFIELDS, $mobile_post);
	   		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   		$postResult = curl_exec($ch);

	   		if (curl_errno($ch))
			{
	       		print curl_error($ch);
	       		print "Error, unable to post incident!";
	   		}
			else
			{
				echo $postResult;
			}
	   		curl_close($ch);
		}
		else
		{
			echo "Error in geocoding! Http error ".substr($data,0,3);
		}
	}	
}