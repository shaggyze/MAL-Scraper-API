<?php

// Return json file
header('Content-type:application/json;charset=utf-8');

ini_set('max_execution_time', 2000);
ini_set('memory_limit', "1024M");
ini_set('max_file_size', 1000000000);

// Set the default timezone to UTC
date_default_timezone_set('UTC');

// Use the Mal-Scraper library
require 'vendor/autoload.php';
use MalScraper\MalScraper;

date_default_timezone_set('UTC');

$myMalScraper = new MalScraper([
    'enable_cache' => true,
    'cache_time'   => 21600,
    'to_api'       => true,
]);

// Get the parameter
$method = isset($_GET['m']) ? $_GET['m'] : '';

$type = isset($_GET['t']) ? $_GET['t'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

$id1 = isset($_GET['id1']) ? $_GET['id1'] : '';
$id2 = isset($_GET['id2']) ? $_GET['id2'] : '';

$page = isset($_GET['p']) ? $_GET['p'] : 1;

$query = isset($_GET['q']) ? $_GET['q'] : '';

$genre = isset($_GET['g']) ? $_GET['g'] : 0;

$year = isset($_GET['y']) ? $_GET['y'] : date('Y');
$season = isset($_GET['s']) ? $_GET['s'] : getCurrentSeason();

$user = isset($_GET['u']) ? $_GET['u'] : '';
$status = isset($_GET['st']) ? $_GET['st'] : 7;

$size = isset($_GET['sz']) ? $_GET['sz'] : '';

function get_subdirectory($type, $id) {
  $id = intval($id);
  $subdirectory_number = floor($id / 10000);
  $subdirectory_path = '../info/' . $type . '/' . $subdirectory_number . '/';
  if (!is_dir($subdirectory_path) && ($type == "anime" || $type == "manga")) {
    mkdir($subdirectory_path, 0777, true); // Create directory with permissions
  }
  return strval($subdirectory_number);
}

// Call the requested method
switch ($method) {

// General Method ----------
    case 'info':
        if ($type && $id) {
            $result = $myMalScraper->getInfo($type, $id);
			$subdirectory = get_subdirectory($type, $id);
			$decodedResult = json_decode($result, true);
			$filePath = '../info/' . $type . '/' . $subdirectory . '/' . $id . '.json';
			if ($decodedResult['status'] == 200) {
				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
				print_r($result);
			} else {
				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
				} else {
					print_r($result);
				}
			}
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'character':
        if ($id) {
            $result = $myMalScraper->getCharacter($id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'people':
        if ($id) {
            $result = $myMalScraper->getPeople($id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'studio-producer':
    case 'studioproducer':
        if ($id) {
            $result = $myMalScraper->getStudioProducer($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'magazine':
        if ($id) {
            $result = $myMalScraper->getMagazine($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'genre':
        if ($type && $id) {
            $result = $myMalScraper->getGenre($type, $id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'review':
        if ($id) {
            $result = $myMalScraper->getReview($id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'recommendation':
        if ($type && $id1 && $id2) {
            $result = $myMalScraper->getRecommendation($type, $id1, $id2);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;

// Additional Method ----------
    case 'character-staff':
    case 'characterstaff':
        if ($type && $id) {
            $result = $myMalScraper->getCharacterStaff($type, $id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'stat':
        if ($type && $id) {
            $result = $myMalScraper->getStat($type, $id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'picture':
        if ($type && $id) {
            $result = $myMalScraper->getPicture($type, $id);
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'character-picture':
    case 'characterpicture':
        if ($id) {
            $result = $myMalScraper->getCharacterPicture($id);
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'people-picture':
    case 'peoplepicture':
        if ($id) {
            $result = $myMalScraper->getPeoplePicture($id);
			if ($size == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'video':
        if ($id) {
            $result = $myMalScraper->getVideo($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'episode':
        if ($id) {
            $result = $myMalScraper->getEpisode($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'anime-review':
    case 'animereview':
        if ($id) {
            $result = $myMalScraper->getAnimeReview($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'manga-review':
    case 'mangareview':
        if ($id) {
            $result = $myMalScraper->getMangaReview($id, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'anime-recommendation':
    case 'animerecommendation':
        if ($id) {
            $result = $myMalScraper->getAnimeRecommendation($id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'manga-recommendation':
    case 'mangarecommendation':
        if ($id) {
            $result = $myMalScraper->getMangaRecommendation($id);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;

// List ----------
    case 'all-anime-genre':
    case 'allanimegenre':
        $result = $myMalScraper->getAllAnimeGenre();
        print_r($result);
        break;
    case 'all-manga-genre':
    case 'allmangagenre':
        $result = $myMalScraper->getAllMangaGenre();
        print_r($result);
        break;
    case 'all-studio-producer':
    case 'allstudioproducer':
        $result = $myMalScraper->getAllStudioProducer();
        print_r($result);
        break;
    case 'all-magazine':
    case 'allmagazine':
        $result = $myMalScraper->getAllMagazine();
        print_r($result);
        break;
    case 'all-review':
    case 'allreview':
        $result = $myMalScraper->getAllReview($type, $page);
        print_r($result);
        break;
    case 'all-recommendation':
    case 'allrecommendation':
        $result = $myMalScraper->getAllRecommendation($type, $page);
        print_r($result);
        break;

// Search ----------
    case 'search-anime':
    case 'searchanime':
        if ($query) {
            $result = $myMalScraper->searchAnime($query, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'search-manga':
    case 'searchmanga':
        if ($query) {
            $result = $myMalScraper->searchManga($query, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'search-character':
    case 'searchcharacter':
        if ($query) {
            $result = $myMalScraper->searchCharacter($query, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'search-people':
    case 'searchpeople':
        if ($query) {
            $result = $myMalScraper->searchPeople($query, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'search-user':
    case 'searchuser':
        if ($query) {
            $result = $myMalScraper->searchUser($query, $page);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;

// Seasonal ----------
    case 'season':
        $result = $myMalScraper->getSeason($year, $season);
        print_r($result);
        break;

// Top List ----------
    case 'top-anime':
    case 'topanime':
        $type = $type ? $type : 0;
        $result = $myMalScraper->getTopAnime($type, $page);
        print_r($result);
        break;
    case 'top-manga':
    case 'topmanga':
        $type = $type ? $type : 0;
        $result = $myMalScraper->getTopManga($type, $page);
        print_r($result);
        break;
    case 'top-character':
    case 'topcharacter':
        $result = $myMalScraper->getTopCharacter($page);
        print_r($result);
        break;
    case 'top-people':
    case 'toppeople':
        $result = $myMalScraper->getTopPeople($page);
        print_r($result);
        break;

// User ----------
    case 'user':
        if ($user) {
            $result = $myMalScraper->getUser($user);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'user-friend':
    case 'userfriend':
        if ($user) {
            $result = $myMalScraper->getUserFriend($user);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'user-history':
    case 'userhistory':
        if ($user) {
            $result = $myMalScraper->getUserHistory($user, $type);
            print_r($result);
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'user-list':
    case 'userlist':
        if ($user) {
            $type = $type ? $type : 'anime';
            $genre = $genre ? $genre : 0;
            $result = $myMalScraper->getUserList($user, $type, $status, $genre);
			$decodedResult = json_decode($result, true);
			$filePath = '../userlist/' . $user . '_' . $type . '_' . $status . '_' . $genre . '.json';
			if ($decodedResult['status'] == 200) {
				/*$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " *[DEL]/ \r" . $result;*/
				
				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);

				print_r($result);
			} else {

				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
				} else {
					print_r($result);
				}
			}
        } else {
            print_r(paramError('2'));
        }
        break;
	case 'user-css':
	case 'usercss':
        if ($user) {
            header('Content-Type: text/css');

            $type = $type ? $type : 'anime';
            $genre = $genre ? $genre : 0;
            $query = $query ? $query : false;

            $result = $myMalScraper->getUserCSS($user, $type, $query, $genre);
			$decodedResult = json_decode($result, true);
			$result = json_decode($result, true);
			if ($result['data'] == 'No UserList') {
				$result = $result['data'];
				print_r(paramError('3'));

				break;
			}
            $result = $result['data'];
			$result = str_replace('-a', '\a', $result);
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
			$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', (urlencode($query))) . '_' . $genre . '.css';
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;

				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);

				print_r($result);
			} else {

				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
				} else {
					print_r($result);
				}
			}
		} else {
            print_r(paramError('2'));
        }
        break;
    case 'user-cover':
    case 'usercover':
        if ($user) {
            header('Content-Type: text/css');

            $type = $type ? $type : 'anime';
            $genre = $genre ? $genre : 0;
            $query = $query ? $query : false;

            $result = $myMalScraper->getUserCover($user, $type, $query, $genre);
			$decodedResult = json_decode($result, true);
			$result = json_decode($result, true);
			if ($result['data'] == 'No UserList') {
				$result = $result['data'];
				print_r(paramError('3'));

				break;
			}
            $result = $result['data'];
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
			$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', (urlencode($query))) . '_' . $genre . '.css';
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				
				file_put_contents('../cache/' . $user . '_' . $type . '_' . hash('sha256', (urlencode($query))) . '_' . $genre . '.css', $result, FILE_USE_INCLUDE_PATH);

				print_r($result);
			} else {

				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
				} else {
					print_r($result);
				}
			}
		} else {
            print_r(paramError('2'));
        }
        break;
		
// Secret ----------
    case 'auto-cover':
    case 'autocover':
        header('Content-Type: text/css');

        $user_url = $_SERVER['HTTP_REFERER'];
        $user_url = str_replace('https://myanimelist.net', '', $user_url);

        preg_match("/\/.+(list)\//", $user_url, $user_type);
        $type = str_replace(['/', 'list'], '', $user_type[0]);

        $user_url = str_replace(['/animelist/', '/mangalist/'], '', $user_url);
        $user_url = preg_replace('/\?+.+/', '', $user_url);

        $user = $user_url;
        $type = $type ? $type : 'anime';
        $genre = $genre ? $genre : 0;
        $query = $query ? $query : false;

        $result = $myMalScraper->getUserCover($user, $type, $query, $genre);
        $result = json_decode($result, true);
        $result = $result['data'];

        print_r($result);
        break;
    default:
        print_r(paramError('1'));
        break;
}

// Return error parameter
function paramError($a = '2')
{
	$result = [];
    if ($a == '1') {
        header('HTTP/1.1 400');
        $result['status'] = 400;
        $result['message'] = 'Method not found';
        $result['data'] = [];
    } elseif ($a == '2') {
        header('HTTP/1.1 400');
        $result['status'] = 400;
        $result['message'] = 'Bad Request';
        $result['data'] = [];
    } else {
		header('HTTP/1.1 404');
		$result['status'] = 404;
		$result['message'] = 'No UserList';
        $result['data'] = [];
	}

    return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Get current season (spring,summer,fall,winter)
function getCurrentSeason()
{
    $currentMonth = date('m');

    if ($currentMonth >= '01' && $currentMonth < '04') {
        return 'winter';
    }
    if ($currentMonth >= '04' && $currentMonth < '07') {
        return 'spring';
    }
    if ($currentMonth >= '07' && $currentMonth < '10') {
        return 'summer';
    }

    return 'autumn';
}
