<?php
    class common {
		function curl_file_get_contents($url) {
			if(strstr($url, "https") == 0) {
				return self::curl_file_get_contents_https($url);
			}
			else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				curl_close($ch);
				return $data;
			}
		}
		
		function curl_file_get_contents_https($url) {
			$res = curl_init();
			curl_setopt($res, CURLOPT_URL, $url);
			curl_setopt($res,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($res, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
			$out = curl_exec($res);
			curl_close($res);
			return $out;
        }
        
		function get_prep($value) {
			$value = urldecode(htmlentities(strip_tags($value)));
			
			return $value;
		}
		
		function get_prep2(&$item) {
			$item = htmlentities($item);
		}
		
		function out_prep($array) {
			if (is_array($array)) {
				if (count($array) > 0) {
					array_walk_recursive($array, array($this, 'get_prep2'));
				}
			}
			return $array;
        }
        
        //send emails
		function send_mail($from,$to,$subject,$body) {
			$headers = '';
			$headers .= "From: $from\r\n";
			$headers .= "Reply-to: ".replyMail."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= "Date: " . date('r', time()) . "\r\n";
		
			if (@mail($to,$subject,$body,$headers)) {
				return true;
			} else {
				return false;
			}
		}
		
		function checkSize($file, $array=false) {
			global $options;
			if ($array == true) {
				$data = $file;
			} else {
				$data[] = $file;
			}
			$error = false;
			for ($i = 0; $i < count($data); $i++) {
				$size = (int) (strlen(rtrim($data[$i], '=')) * 3 / 4);
				if($size > $options->get("size")) {
					$error = true;
					break;
				}
			}
			return $error;
		}
		
		function hashPass($string) {
			$count = strlen($string);
			$start = $count/2;
			$list = "";
			for ($i = 0; $i < $start; $i++) {
				$list .= "*";
			}
			$hasPass = substr_replace($string, $list, $start);
			
			return $hasPass;
		}
		
		function initials($string, $lenght=1) {
			$string = trim($string);
			$words = explode(" ", $string);
			$words = array_filter($words);
			$letters = "";
			foreach ($words as $value) {
				$letters .= strtoupper(substr($value, 0, $lenght)).". ";
			}
			$letters = trim(trim($letters), ".");
			
			return $letters;
		}
		
		function getParam($url) {
			$urlData = explode("?", $url);
			$param = explode("&", $urlData[1]);
			$tag = "";
			for ($i = 1; $i < count($param); $i++) {
				if (($param[$i] != "") && (strpos($param[$i], "error=") === false) && (strpos($param[$i], "done=") === false)) {
					$tag .= "&".$param[$i];
				}
			}
			return $tag;
		}

		function seo($id, $type="category") {
			if ($type == "category") {
				global $category;
				$row = $category->listOne($id);
				$id = $row['ref'];
				$name = trim(strtolower($row['category_title']));
				
				$urlLink = explode(" ", $name);
				$link = implode("-", $urlLink);
				
				$result = URL."category/".$id."/".$link."/";
			} else if ($type == "view") {		
				global $category;
				$row = $category->listOne($id);
				$id = $row['ref'];
				$name = trim(strtolower($row['category_title']));
				
				$urlLink = explode(" ", $name);
				$link = implode("-", $urlLink);		
				$result = URL."request/".$id."/".$link."/";
			} else if ($type == "profile") {
				global $users;
				$row = $users->listOne($id);
				$id = $row['ref'];
				$name = trim(strtolower($row['screen_name']));
				
				$urlLink = explode(" ", $name);
				$link = implode("-", $urlLink);
				
				$result = URL."profile/request/".$id."/".$link."/";
			} else {
				
				$result = URL."items/".$id."/";
			}
			
			return $result;
		}
		
		function getExtension($str) {
			$i = strrpos($str,".");
			if (!$i) { return ""; } 
			$l = strlen($str) - $i;
			$ext = substr($str,$i+1,$l);
			return $ext;
		}
		
		function createRandomPassword($len = 7) { 
			$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"; 
			srand((double)microtime()*1000000); 
			$i = 0; 
			$pass = '' ; 
			$count = strlen($chars);
			while ($i <= $len) { 
				$num = rand() % $count; 
				$tmp = substr($chars, $num, 1); 
				$pass = $pass . $tmp; 
				$i++; 
			} 
			return $pass; 
		}

		function getTagFromWord($word, $type="tag", $target="this") {
			$word = str_replace(":"," ", $word);
			$word = str_replace(";"," ", $word);
			$word = str_replace("-"," ", $word);

			$withSpace = explode(",", trim($word));
			if ($target == "this") {
				$tag = " target='_self'";
			} else {
				$tag = " target='_blank'";
			}
			$output = "";
			foreach($withSpace as $key) {
				if (trim($key) != "") {
					if ($type == "category") {
						global $category;
						$output .= "<a href='".$this->seo($key)."'>".$category->getSingle($key)."</a>, ";
					} else {
						$output .= "<a href='".URL."search/keyword/".trim($key)."'".$tag.">".$key."</a> ";
					}
				}
			}
			$output = trim($output);
			return rtrim($output, ",");
		}

		function getTagFromWordClean($word) {
			global $category;
			$word = str_replace(":"," ", $word);
			$word = str_replace(";"," ", $word);
			$word = str_replace("-"," ", $word);

			$withSpace = explode(",", trim($word));
			$output = "";
			foreach($withSpace as $key) {
				if ($key != "") {
					$output .= $category->getSingle($key).", ";
				}
			}

			$output = trim($output);
			return rtrim($output, ",");
		}

		function cleanText($text) {
			switch ($text) {
				case "vendor":
					return "Service Offered";
					break;
				case "client":
					return "Looking for service provider";
					break;
				case "per_hour":
					return "Per hour";
					break;
				case "per_mil":
					return "Per milestone achieved";
					break;
				case "per_job":
					return "Per service";
					break;
				case "0":
					return "Posting this Ad is free";
					break;
				case "1":
					return "Payment card has not been validated";
					break;
				case "2":
					return "Payment card has been validated";
					break;
				default;
					return $text;
			}
		}

		function getLocation() {
			if ((isset($_COOKIE['js_loc'])) && (intval($_COOKIE['loc_check']) < time())) {
				$loc = explode("_", $_COOKIE['js_loc']);
				
				$addressData = $this->googleGeoLocation($loc[1], $loc[0]);
				$data['latitude'] = $addressData['latitude'];
				$data['longitude'] = $addressData['longitude'];
				$data['code'] = $addressData['country_code'];
				$data['city'] = $addressData['city'];
				$data['state'] = $addressData['province'];
				$data['state_code'] = $addressData['province_code'];
				$data['country'] = $addressData['country'];

				$_SESSION['location'] = $data;
				$cookie = serialize($data);
				
				setcookie("loc_check", time()+60*60, time()+(60*60), "/");
				setcookie("l_d", $cookie, time()+(60*60*24), "/");
			} else if ((isset($_SESSION['location'])) && (!isset($_COOKIE['l_d']))) {
				$cookie = serialize($_SESSION['location']);

				setcookie("l_d", $cookie, time()+(60*60*24), "/");
			} else if ((isset($_COOKIE['l_d'])) && (!isset($_SESSION['location']))) { 
				$_SESSION['location'] = unserialize($_COOKIE['l_d']);
			} else if ((!isset($_COOKIE['l_d'])) && (!isset($_SESSION['location']))) {
				$response = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.ip_address));

				$data['latitude'] = $response['geoplugin_latitude'];
				$data['longitude'] = $response['geoplugin_longitude'];
				$data['code'] = $response['geoplugin_countryCode'];
				$data['city'] = $response['geoplugin_city'];
				$data['state'] = $response['geoplugin_region'];
				$data['state_code'] = $response['geoplugin_regionCode'];
				$data['country'] = $response['geoplugin_countryName'];
				$_SESSION['location'] = $data;
				$cookie = serialize($data);

				setcookie("l_d", $cookie, time()+(60*60*24), "/");
			}
		}

		function truncate($text, $chars = 100) {
			$text = $text." ";
			$text = substr($text,0,$chars);
			$text = substr($text,0,strrpos($text,' '));
			$text = $text."...";
			return $text;
		}

		function googleGeoLocation($long, $lat, $address=false) {
			if ($address === false) {
				$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$long."&sensor=false&key=".GoogleAPI;
			} else {
				$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address)."&sensor=false&key=".GoogleAPI;
			}
			$data = json_decode($this->curl_file_get_contents($url), true);
			//city data
			if (@$data['results'][0]['address_components'][0]['types'][0] == "locality") {
				$return['city'] = $data['results'][0]['address_components'][0]['short_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][1]['short_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][2]['short_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][3]['short_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][4]['short_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][5]['short_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "locality") {
				$return['city'] =  $data['results'][0]['address_components'][6]['short_name'];
			}

			//province code
			if (@$data['results'][0]['address_components'][0]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] =  $data['results'][0]['address_components'][0]['short_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][1]['short_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][2]['short_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][3]['short_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][4]['short_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][5]['short_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "administrative_area_level_1") {
				$return['province_code'] = $data['results'][0]['address_components'][6]['short_name'];
			}

			//province
			if (@$data['results'][0]['address_components'][0]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][0]['long_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][1]['long_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][2]['long_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][3]['long_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][4]['long_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][5]['long_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "administrative_area_level_1") {
				$return['province'] = $data['results'][0]['address_components'][6]['long_name'];
			}

			//country
			if (@$data['results'][0]['address_components'][0]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][0]['long_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][1]['long_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][2]['long_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][3]['long_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][4]['long_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][5]['long_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "country") {
				$return['country'] = $data['results'][0]['address_components'][6]['long_name'];
			}

			//postal code
			if (@$data['results'][0]['address_components'][0]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][0]['long_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][1]['long_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][2]['long_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][3]['long_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][4]['long_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][5]['long_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][6]['long_name'];
			} elseif (@$data['results'][0]['address_components'][7]['types'][0] == "postal_code") {
				$return['postal_code'] = $data['results'][0]['address_components'][7]['long_name'];
			}

			//country code
			if (@$data['results'][0]['address_components'][0]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][0]['short_name'];
			} elseif (@$data['results'][0]['address_components'][1]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][1]['short_name'];
			} elseif (@$data['results'][0]['address_components'][2]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][2]['short_name'];
			} elseif (@$data['results'][0]['address_components'][3]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][3]['short_name'];
			} elseif (@$data['results'][0]['address_components'][4]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][4]['short_name'];
			} elseif (@$data['results'][0]['address_components'][5]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][5]['short_name'];
			} elseif (@$data['results'][0]['address_components'][6]['types'][0] == "country") {
				$return['country_code'] = $data['results'][0]['address_components'][6]['short_name'];
			}

			//address
			$return['address'] = $data['results'][0]['formatted_address'];

			//latutude
			$return['latitude'] = $data['results'][0]['geometry']['location']['lat'];

			//longitude
			$return['longitude'] = $data['results'][0]['geometry']['location']['lng'];
			return $return;
		}

		function http2https() {
			//If the HTTPS is not found to be "on"
			if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
				//Tell the browser to redirect to the HTTPS URL.
				header("HTTP/1.1 301 Moved Permanently"); 
				header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
				//Prevent the rest of the script from executing.
				exit;
			}
		}

		function addS($word, $count) {
			if ($count > 1) {
				$two = substr($word, -2); 
				$one = substr($word, -1); 
				if (($two == "ss") || ($two == "sh") || ($two == "ch")) {
					return $word."es";
				} else if (($one == "s") || ($one == "x") || ($one == "z")) {
					return $word."es";
				} else if ($two == "lf") {
					return $word."ves";
				} else if ($two == "ay") {
					return $word."s";
				} else if ($one == "y") {
					return $word."ies";
				} else {
					return $word."s";
				}
			} else {
				return $word;
			}
		}

		function faIcons($tag) {
			switch ($tag) {
				case "post_messages":
					return '<i class="fas fa-tasks"></i>';
					break;
				case "messages":
					return '<i class="fas fa-inbox"></i>';
					break;
				default;
					return '<i class="fa fa-bell" aria-hidden="true"></i>';
			}
		}
		
		function numberPrintFormat($value) {
			if ($value > 999 && $value <= 999999) {
				$result = round(($value / 1000), 2) . ' K';
			} elseif ($value > 999999 && $value < 999999999) {
				$result = round(($value / 1000000), 2) . ' M';
			} elseif ($value > 999999999) {
				$result = round(($value / 1000000000), 2) . ' B';
			} else {
				$result = $value;
			}
			
			return $result;
		}

		function get_time_stamp($post_time) {
			if (($post_time == "") || ($post_time <1)) {
				return false;
			} else {
				$difference = time() - $post_time;
				$periods = array("sec", "min", "hour", "day", "week",
				"month", "years", "decade","century","millenium");
				$lengths = array("60","60","24","7","4.35","12","10","100","1000");
				
				if ($difference >= 0) { // this was in the past
					$ending = "ago";
				} else { // this was in the future
					$difference = -$difference;
					$ending = "time";
				}
				
				for($j = 0; $difference >= $lengths[$j]; $j++)
				$difference = $difference/$lengths[$j];
				$difference = round($difference);
				
				if($difference != 1) $periods[$j].= "s";
				$text = "$difference $periods[$j] $ending";
				return $text;
			}
		}

		function googleDirection($from, $to) {
			global $country;
			
			$from = $from['latitude'].",".$from['longitude'];
			$to = $to['latitude'].",".$to['longitude'];

			$locale = @$from['code'];

			$regionData = $country->getLoc($locale);

			$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$from."&destination=".$to."&key=".GoogleAPI."&units=".$regionData['si_unit']."&region=".$locale;
			$data = json_decode($this->curl_file_get_contents($url), true);
			if (@$data['status'] == "OK") {
				$result['distance']['text'] = @$data['routes'][0]['legs'][0]['distance']['text'];
				$result['distance']['value'] = @round(($data['routes'][0]['legs'][0]['distance']['value']/1000), 2);
				$result['duration']['text'] = @$data['routes'][0]['legs'][0]['duration']['text'];
				$result['duration']['value'] = @$data['routes'][0]['legs'][0]['duration']['value'];
				
				return $result;
			} else {
				return false;
			}
		}

		function print_time($timestamp) {
			if (intval($timestamp) > 0) {
				return date("Y-m-d h:i:s", intval($timestamp));
			} else {
				return "";
			}
		}

        function pagination($page, $count, $pageTitle="page", $type="result_per_page") {
			global $options;
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
			$actual_link = trim(str_replace($pageTitle."=".$page, "", $actual_link), "&");

			if (strpos($actual_link, "?") === false) {
				$actual_link .= "?";
			} else {
				$actual_link .= "&";
			}
			$limit = $options->get($type);
            $lenght = ceil($count/$limit);
            if ($page <= 3) {
                if ($lenght < 5) {
                    $first = 0;
                    $last = $lenght-1;
                } else {
                    $first = 0;
                    $last = 4;
                }
            } else if ($page < ($lenght-2)) {
                $first = $page-2;
                $last = $page+2;
            } else if ($page <= $lenght) {
                $first = $page-5;
                $last = $lenght-1;
            }
            if ($page > 0) {
                $prev = $page-1;
                $next = $page+1;
			} else {
                $prev = 0;
                $next = $page+1;
			}

			if ($lenght > 0) {
				echo '<div class="text-center">Page '.($page+1).' of '.$lenght.'</div>';
				echo '<nav aria-label="Page navigation example">';
				echo '<ul class="pagination justify-content-center">';
				if ($prev > 0) {
					echo '<li class="page-item"><a class="page-link" href="'.$actual_link.$pageTitle.'=0">&laquo;&laquo;</a></li>';
				   	echo '<li class="page-item"><a class="page-link" href="'.$actual_link.$pageTitle.'='.$prev.'">&laquo;</a></li>';
				}
				for ($i = $first; $i<=$last; $i++) {
					if ($i == $page) {
						$active = ' active';
					} else {
						$active = '';
					}
					echo '<li class="page-item'.$active.'"><a class="page-link" href="'.$actual_link.$pageTitle.'='.$i.'">'.($i+1).'</a></li>';
				}
				if ($next < $last) {
					echo '<li class="page-item"><a class="page-link" href="'.$actual_link.$pageTitle.'='.$next.'">&raquo;</a></li>';
					echo '<li class="page-item"><a class="page-link" href="'.$actual_link.$pageTitle.'='.($lenght-1).'">&raquo;&raquo;</a></li>';
				}
				echo '</ul>';
				echo '</nav>';
			}
		}
		
		public function splitURL($string) {
			return explode("/", $string);
		}
	}
?>