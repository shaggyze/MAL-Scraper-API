<?php
// Set the Access-Control-Allow-Origin header
header("Access-Control-Allow-Origin: https://myanimelist.net");

// Optionally, allow specific HTTP methods (e.g., GET, POST)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Optionally, allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Optionally, allow credentials (e.g., cookies)
header("Access-Control-Allow-Credentials: true");

// Return json file
header('Content-type:application/json;charset=utf-8');

ini_set('max_execution_time', 20000);
ini_set('memory_limit', "2048M");
ini_set('max_file_size', 1000000000);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone to UTC
date_default_timezone_set('UTC');


if (defined('LC_CTYPE')) { // Check if LC_CTYPE is defined
    setlocale(LC_CTYPE, 'C'); // Or 'en_US.UTF-8' or your preferred POSIX locale
} else if (defined('LC_ALL')) { // Fallback to LC_ALL if LC_CTYPE is not defined
    setlocale(LC_ALL, 'C');
}
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
$order = isset($_GET['o']) ? $_GET['o'] : 0;

$year = isset($_GET['y']) ? $_GET['y'] : date('Y');
$season = isset($_GET['s']) ? $_GET['s'] : getCurrentSeason();

$user = isset($_GET['u']) ? $_GET['u'] : '';
$status = isset($_GET['st']) ? $_GET['st'] : 7;

$size = isset($_GET['sz']) ? $_GET['sz'] : '';
$nonseasonal = isset($_GET['ns']) ? $_GET['ns'] : '';

$printed = false;
$printed0 = false;
$printed2 = false;

function logApiRequestInfo() {
    // Define the log file path
    $logFile = 'api_request_log.txt';

    // Get the current timestamp
    $timestamp = date('Y-m-d H:i:s');

    // Start building the log message
    $logMessage = "---------------------------------------------------\n";
    $logMessage .= "API Request Timestamp: " . $timestamp . "\n";

    // Log server-related information
    $logMessage .= "\n--- Server Information ---\n";
    $logMessage .= "Server Name: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'N/A') . "\n";
    $logMessage .= "Server Address: " . (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A') . "\n";
    $logMessage .= "Server Port: " . (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 'N/A') . "\n";
    $logMessage .= "Script Filename: " . (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'N/A') . "\n";
    $logMessage .= "Request URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A') . "\n";
    $logMessage .= "Request Method: " . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'N/A') . "\n";
    $logMessage .= "HTTP Referer: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A') . "\n";
    $logMessage .= "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'Yes' : 'No') . "\n";
    $logMessage .= "Content Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'N/A') . "\n";
    $logMessage .= "Content Length: " . (isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'N/A') . "\n";

    // Log user-related information
    $logMessage .= "\n--- Client Information ---\n";
    $logMessage .= "Remote Address (IP): " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A') . "\n";
    $logMessage .= "Remote Host: " . (isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : 'N/A') . "\n";
    $logMessage .= "Remote Port: " . (isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : 'N/A') . "\n";
    $logMessage .= "HTTP User Agent: " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A') . "\n";
    $logMessage .= "HTTP Accept: " . (isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'N/A') . "\n";
    $logMessage .= "HTTP Accept Language: " . (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'N/A') . "\n";
    $logMessage .= "HTTP Accept Encoding: " . (isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : 'N/A') . "\n";

    // Log request headers
    $logMessage .= "\n--- Request Headers ---\n";
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $headerName = str_replace('HTTP_', '', $key);
            $headerName = str_replace('_', '-', strtolower($headerName));
            $logMessage .= htmlspecialchars($headerName) . ": " . htmlspecialchars($value) . "\n";
        }
        // Special case for Authorization header (not always prefixed with HTTP_)
        if ($key === 'Authorization') {
            $logMessage .= "authorization: " . htmlspecialchars($value) . "\n";
        }
    }

    // Log GET parameters
    if (!empty($_GET)) {
        $logMessage .= "\n--- GET Parameters ---\n";
        foreach ($_GET as $key => $value) {
            $logMessage .= htmlspecialchars($key) . ": " . htmlspecialchars($value) . "\n";
        }
    }

    // Log POST/PUT/DELETE body (if any)
    $requestBody = file_get_contents('php://input');
    if (!empty($requestBody)) {
        $logMessage .= "\n--- Request Body ---\n";
        // Attempt to decode JSON for better readability (you can add other content type handling)
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $decodedBody = json_decode($requestBody, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $logMessage .= print_r($decodedBody, true) . "\n";
            } else {
                $logMessage .= htmlspecialchars($requestBody) . "\n";
            }
        } else {
            $logMessage .= htmlspecialchars($requestBody) . "\n";
        }
    }

    $logMessage .= "---------------------------------------------------\n\n";

    // Open the log file in append mode
    $logHandle = fopen($logFile, 'a');

    if ($logHandle) {
        // Write the log message to the file
        fwrite($logHandle, $logMessage);

        // Close the log file
        fclose($logHandle);

        // Optionally, you can return a success message or boolean
        return true;
    } else {
        // Handle the error if the log file cannot be opened
        error_log("Error: Could not open API log file for writing.");
        return false;
    }
}

function get_subdirectory($dir, $type, $id) {
  $id = intval($id);
  if ($id) {
  $subdirectory_number = floor($id / 10000);
  if ($type == "anime" || $type == "manga") {
      $subdirectory_path = '../maldb/' . $dir . '/' . $type . '/' . $subdirectory_number . '/';
  } else {
	  $subdirectory_path = '../maldb/' . $dir . '/' . $subdirectory_number . '/';
  }
  if (!is_dir($subdirectory_path)) {
    mkdir($subdirectory_path, 0777, true); // Create directory with permissions
  }
  return strval($subdirectory_number);
  } else {
    mkdir('../maldb/' . $dir . '/', 0777, true); // Create directory with permissions
  }
}

function clearCronJobs($scriptPath) {
    $existingCrontab = shell_exec('crontab -l 2>&1');
    $crontabLines = explode("\n", trim($existingCrontab));

    $newCrontabLines = [];
    foreach ($crontabLines as $line) {
        if (strpos($line, $scriptPath) === false && strpos($line, 'cronjob.php') === false) {
            $newCrontabLines[] = $line;
        }
    }

    $finalCrontab = implode("\n", $newCrontabLines) . "\n";
    $command = "echo " . escapeshellarg($finalCrontab) . " | crontab -";
    exec($command);
}

function visit_url($url_base, $params, $query) {
    $qFilePath = '/home/shagzgjm/public_html/scripts/q_files/';
    $jsonFilePath = $qFilePath . 'cron_tasks.json';

    if (!is_dir($qFilePath)) {
        mkdir($qFilePath, 0755, true);
    }

    $urlParams = array_merge(['url' => $url_base], $params);
    if (isset($query) && !empty($query)) {
        $qFileName = 'q_' . hash('sha256', $query) . '.txt';
        file_put_contents($qFilePath . $qFileName, $query);
        $urlParams['qfile'] = $qFilePath . $qFileName;
        unset($urlParams['q']);
    }
    ksort($urlParams);

    $newTask = [
        'url' => $url_base,
        'params' => $urlParams,
    ];

    $existingTasks = [];
    if (file_exists($jsonFilePath)) {
        $existingTasks = json_decode(file_get_contents($jsonFilePath), true);
        if (!is_array($existingTasks)) {
            $existingTasks = [];
        }
    }

    $isDuplicate = false;
    foreach ($existingTasks as $task) {
        // Sort parameters for consistent comparison
        $taskParams = $task['params'];
        ksort($taskParams);
        $newTaskParams = $newTask['params'];
        ksort($newTaskParams);

        if ($task['url'] === $newTask['url'] && $taskParams === $newTaskParams) {
            $isDuplicate = true;
            break;
        }
    }

    if (!$isDuplicate) {
        $existingTasks[] = $newTask;
        file_put_contents($jsonFilePath, json_encode($existingTasks, JSON_PRETTY_PRINT));
    }
}


// Add the new cron job to run cronjob.php every 6 hours
$cronjobScriptPath = '/usr/local/bin/php /home/shagzgjm/public_html/scripts/cron_job.php';
//$newCronJobLine = '0 */6 * * * ' . $cronjobScriptPath . ' >> /home/shagzgjm/cron_output.log 2>> /home/shagzgjm/cron_errors.log';
$newCronJobLine = '*/15 */6 * * * ' . $cronjobScriptPath;

$existingCrontab = shell_exec('crontab -l 2>&1');
if ($existingCrontab) {
	$crontabLines = explode("\n", $existingCrontab);
} else {
	$crontabLines = [];
}
$cronjobExists = false;
foreach ($crontabLines as $line) {
    if (trim($line) === $newCronJobLine) {
        $cronjobExists = true;
        break;
    }
}

//if (!$cronjobExists) {
//    $finalCrontab = $existingCrontab . "\n" . $newCronJobLine . "\n";
//    $command = "echo " . escapeshellarg($finalCrontab) . " | crontab -";
//    exec($command);
//}

function getPreset($query, $type) {
			$queryis = str_replace(':', '', $query);
			if (strtolower($queryis) == "more") {
				if (strtolower($type) == "anime") {
					$query = "#more{anime_id} {background:url(\"{anime_image_path}\");background-image:url(\"{anime_image_path}}";
				} else {
					$query = "#more{manga_id} {background:url(\"{manga_image_path}\");background-image:url(\"{manga_image_path}}";
				}
			} elseif (strtolower($queryis) == "moretdbeforelink") {
				if (strtolower($type) == "anime") {
					$query = "#more{anime_id} td:before, a[href^=\"/{type}/{anime_id}/\"]:before {background:url(\"{anime_image_path}\")}";
				} else {
					$query = "#more{manga_id} td:before, a[href^=\"/{type}/{manga_id}/\"]:before {background:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "animetitle") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "animetitlebefore") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "animetitleafter") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]:after{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]:after{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "datatitle") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "datatitlebefore") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "datatitleafter") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]:after{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]:after{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "dataimagelink") {
				if (strtolower($type) == "anime") {
					$query = ".data.image a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.image a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "dataimagelinkbefore" || strtolower($queryis) == "preset") {
				if (strtolower($type) == "anime") {
					$query = ".data.image a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.image a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($queryis) == "dataimagelinkafter") {
				if (strtolower($type) == "anime") {
					$query = ".data.image a[href^=\"/{type}/{anime_id}/\"]:after{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.image a[href^=\"/{type}/{manga_id}/\"]:after{background-image:url(\"{manga_image_path}\")}";
				}
			}
		return $query;
}
logApiRequestInfo();
// Call the requested method
switch ($method) {

// General Method ----------
    case 'info':
        if ($type && $id) {
            $result = $myMalScraper->getInfo($type, $id);
			$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
			$decodedResult = json_decode($result, true);
			$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . $type . '/' . $subdirectory . '/' . $id . '.json';

			if ($decodedResult['status'] == 200) {

$imageUrls = $decodedResult['data']['images'];
if ($imageUrls) {
    $reverseCoverFilePath = '../maldb/info/reversecover.json';

    // 1. Read existing JSON (if any)
    if (file_exists($reverseCoverFilePath)) {
        $existingJson = file_get_contents($reverseCoverFilePath);
        $existingData = json_decode($existingJson, true);
        if ($existingData === null) {
            $existingData = []; // Handle invalid JSON
        }
    } else {
        $existingData = []; // Start with an empty array
    }

    // 2. Add the new data
foreach ($imageUrls as $imageUrl) {
    $existingData[$imageUrl] = [
        'id' => $id,
        'type' => $type,
    ];
}
    // 3. Encode the updated array back into JSON
    $reverseCoverJson = json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

file_put_contents($reverseCoverFilePath, $reverseCoverJson, FILE_USE_INCLUDE_PATH);
			}
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', '.jpg', $result);
				$result = str_replace('.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
			} elseif (strtolower($size) == "large") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
				$result = str_replace('.jpg', 'l.jpg', $result);
			}
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
			$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
			$decodedResult = json_decode($result, true);
			$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . $subdirectory . '/' . $id . '.json';
			file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
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
				$result = str_replace('l.jpg', '.jpg', $result);
				$result = str_replace('.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
			} elseif (strtolower($size) == "large") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
				$result = str_replace('.jpg', 'l.jpg', $result);
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
				$result = str_replace('l.jpg', '.jpg', $result);
				$result = str_replace('.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
			} elseif (strtolower($size) == "large") {
				$result = str_replace(['t.jpg', 'l.jpg'], '.jpg', $result);
				$result = str_replace('.jpg', 'l.jpg', $result);
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
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;
    case 'all-manga-genre':
    case 'allmangagenre':
        $result = $myMalScraper->getAllMangaGenre();
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;
    case 'all-studio-producer':
    case 'allstudioproducer':
        $result = $myMalScraper->getAllStudioProducer();
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;
    case 'all-magazine':
    case 'allmagazine':
        $result = $myMalScraper->getAllMagazine();
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;
    case 'all-review':
    case 'allreview':
        $result = $myMalScraper->getAllReview($type, $page);
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;
    case 'all-recommendation':
    case 'allrecommendation':
        $result = $myMalScraper->getAllRecommendation($type, $page);
		$subdirectory = get_subdirectory(str_replace("-", "", $method), $type, $id);
		$decodedResult = json_decode($result, true);
		$filePath = '../maldb/' . str_replace("-", "", $method) . '/' . str_replace("-", "", $method) . '.json';
		file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
        print_r($result);
        break;

// Search ----------
    case 'search-anime':
    case 'searchanime':
        if ($query) {
            $result = $myMalScraper->searchAnime(str_replace([' ', '_', '-'], "%20", str_replace('"', '', $query)), $page);
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
        $result = $myMalScraper->getSeason($year, $season, $nonseasonal);
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

// User ---------
    case 'user':
        if ($user) {
			$type = $type ? $type : 'anime';
            $genre = $genre ? $genre : 0;
            $query = $query ? $query : false;
			if (strtolower($type) == ":type:") { $type = "all"; }
			if ((strtolower($user) == "all" || strtolower($user) == "username") & strtolower($type) == "all") { $type = "anime"; }
			if (strtolower($user) == "all" || strtolower($user) == "username") {
				$user = "_All_";
			}
            $result = $myMalScraper->getUser($user);

			if (strtolower($query) == "totalentries") {
				header('Content-Type: text/css');
				$decodedResult = json_decode($result, true);
				if (strtolower($user) == "iridescentjaune") {
					if (strtolower($type) == "anime") {
						print_r ('a.status-button.all_anime::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['anime_stat']['status']['total'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.watching::after{content: "(' . $decodedResult['data']['anime_stat']['status']['watching'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['anime_stat']['status']['completed'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['anime_stat']['status']['on_hold'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['anime_stat']['status']['dropped'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantowatch::after{content: "(' . $decodedResult['data']['anime_stat']['status']['plan_to_watch'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}');
					} else if (strtolower($type) == "manga") {
						print_r ('a.status-button.all_anime::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['manga_stat']['status']['total'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.reading::after{content: "(' . $decodedResult['data']['manga_stat']['status']['reading'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['manga_stat']['status']['completed'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['manga_stat']['status']['on_hold'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['manga_stat']['status']['dropped'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ font-size: 9px !important; content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantoread::after{content: "(' . $decodedResult['data']['manga_stat']['status']['plan_to_read'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}');
					}
				} else {
					if (strtolower($type) == "anime") {
						print_r ('a.status-button.all_anime::before { content: "(' . $decodedResult['data']['anime_stat']['status']['total'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px; }a.status-button.watching::before { content: "(' . $decodedResult['data']['anime_stat']['status']['watching'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 65px; width: 60px; margin-left: -18px; }a.status-button.completed::before { content: "(' . $decodedResult['data']['anime_stat']['status']['completed'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px; }a.status-button.onhold::before { content: "(' . $decodedResult['data']['anime_stat']['status']['on_hold'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 20px; width: 60px; margin-left: -18px;}a.status-button.dropped::before { content: "(' . $decodedResult['data']['anime_stat']['status']['dropped'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px;}a.status-button.plantowatch::before { content: "(' . $decodedResult['data']['anime_stat']['status']['plan_to_watch'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px;}');
					} else if (strtolower($type) == "manga") {
						print_r ('a.status-button.all_anime::before { content: "(' . $decodedResult['data']['manga_stat']['status']['total'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px; }a.status-button.reading::before { content: "(' . $decodedResult['data']['manga_stat']['status']['reading'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 65px; width: 60px; margin-left: -18px; }a.status-button.completed::before { content: "(' . $decodedResult['data']['manga_stat']['status']['completed'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px; }a.status-button.onhold::before { content: "(' . $decodedResult['data']['manga_stat']['status']['on_hold'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 20px; width: 60px; margin-left: -18px;}a.status-button.dropped::before { content: "(' . $decodedResult['data']['manga_stat']['status']['dropped'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px;}a.status-button.plantoread::before { content: "(' . $decodedResult['data']['manga_stat']['status']['plan_to_read'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px;}');
					}
				}
			} else if (strtolower($query) == "totalentries2") {
				header('Content-Type: text/css');
				$decodedResult = json_decode($result, true);
				if (strtolower($user) == "zaos_10") {
					if (strtolower($type) == "anime") {
						print_r ('a.status-button.all_anime::before{ content:"\f621 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['anime_stat']['status']['total'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ content:"\f401 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.watching::after{content: "(' . $decodedResult['data']['anime_stat']['status']['watching'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ content:"\f560 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['anime_stat']['status']['completed'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ content:"\f28b ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['anime_stat']['status']['on_hold'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ content:"\f165 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['anime_stat']['status']['dropped'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ content:"\e472 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantowatch::after{content: "(' . $decodedResult['data']['anime_stat']['status']['plan_to_watch'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}');
					} else if (strtolower($type) == "manga") {
						print_r ('a.status-button.all_anime::before{ content:"\f621 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['manga_stat']['status']['total'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ content:"\f401 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.reading::after{content: "(' . $decodedResult['data']['manga_stat']['status']['reading'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ content:"\f560 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['manga_stat']['status']['completed'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ content:"\f28b ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['manga_stat']['status']['on_hold'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ content:"\f165 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['manga_stat']['status']['dropped'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ content:"\e472 ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantoread::after{content: "(' . $decodedResult['data']['manga_stat']['status']['plan_to_read'] . ')" !important;color: #891E35 !important; opacity: 1 !important;  font-size: 14px !important;    background: transparent !important;    top: 33px !important;}');
					}
				} else {
					if (strtolower($type) == "anime") {
						print_r ('#status-menu > div > a:before {padding-top: 25px !important;}a.status-button.all_anime::before { content: "(' . $decodedResult['data']['anime_stat']['status']['total'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px; }a.status-button.watching::before { content: "(' . $decodedResult['data']['anime_stat']['status']['watching'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 65px; width: 60px; margin-left: -18px; }a.status-button.completed::before { content: "(' . $decodedResult['data']['anime_stat']['status']['completed'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px; }a.status-button.onhold::before { content: "(' . $decodedResult['data']['anime_stat']['status']['on_hold'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 20px; width: 60px; margin-left: -18px;}a.status-button.dropped::before { content: "(' . $decodedResult['data']['anime_stat']['status']['dropped'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px;}a.status-button.plantowatch::before { content: "(' . $decodedResult['data']['anime_stat']['status']['plan_to_watch'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px;}');
					} else if (strtolower($type) == "manga") {
						print_r ('#status-menu > div > a:before {padding-top: 25px !important;}a.status-button.all_anime::before { content: "(' . $decodedResult['data']['manga_stat']['status']['total'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px; }a.status-button.reading::before { content: "(' . $decodedResult['data']['manga_stat']['status']['reading'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 65px; width: 60px; margin-left: -18px; }a.status-button.completed::before { content: "(' . $decodedResult['data']['manga_stat']['status']['completed'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px; }a.status-button.onhold::before { content: "(' . $decodedResult['data']['manga_stat']['status']['on_hold'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 20px; width: 60px; margin-left: -18px;}a.status-button.dropped::before { content: "(' . $decodedResult['data']['manga_stat']['status']['dropped'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 25px; width: 60px; margin-left: -18px;}a.status-button.plantoread::before { content: "(' . $decodedResult['data']['manga_stat']['status']['plan_to_read'] . ') "!important; font: var(--fa-font-solid); font-size: 10px !important; position: absolute; padding: 20px 35px; width: 60px; margin-left: -18px;}');
					}
				}
			} else {
				print_r($result);
			}
        } else {
            print_r(paramError('2'));
        }
        break;
    case 'user-friend':
    case 'userfriend':
        if ($user) {
            $result = $myMalScraper->getUserFriend($user, $page);
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
			$order = $order ? $order : 0;
			if (strtolower($type) == ":type:") { $type = "all"; }
			if ((strtolower($user) == "all" || strtolower($user) == "username") & strtolower($type) == "all" & strtolower($query) == "more") { $type = "anime"; }
			if (strtolower($user) == "all" || strtolower($user) == "username") {
				$user = "_All_";
			}
			$filePath = '../maldb/userlist/' . $user . '_' . $type . '_' . $status . '_' . $genre .  '_' . $order . '.json';
			//if (file_exists($filePath)) {
			//	$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
			//	print_r($file);
			//	$printed0 = true;
			//}
            $result = $myMalScraper->getUserList($user, $type, $status, $genre, $order);
			$decodedResult = json_decode($result, true);

			if ($decodedResult['status'] == 200) {
				/*$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " *[DEL]/ \r" . $result;*/
				
				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);

				if ($printed0 == false) print_r($result);
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

            $type = in_array(strtolower($type ?? ''), ['anime', 'manga', 'all']) ? strtolower($type ?? '') : 'anime';
			//$type = $type ? $type : 'anime';
			$status = $status ? $status : 7;
            $genre = $genre ? $genre : 0;
            $query = $query ? $query : false;
			if (strlen($user) >= 17 || strlen($user) == 1) {
				print_r(paramError('3'));
				break;
			}
			if (strtolower($type) == ":type:") { $type = "all"; }
			if (strtolower($type) == "all" & str_contains(strtolower($query), 'more')) { 
				if (str_contains(strtolower($query), 'manga')) {
					$type = "manga";
					$query = str_replace('anime', 'manga', strtolower($query));
				} else {
					$type = "anime";
					$query = str_replace('manga', 'anime', strtolower($query));
				}
			}
			if (strtolower($user) == "all" || str_contains(strtolower($query), 'username') || str_contains(strtolower($user), 'username')) {
				$user = "_All_";
			}
			$typewas = $type;
			$querywas = $query;
			if (strtolower($type) == "all") {
				$typewas = "all";
				$type = "manga";
			if (strtolower($querywas) == "hidehentai" || strtolower($querywas) == "hidegenre" || str_contains(strtolower($querywas), "hidehentai")) {
				if (strtolower($type) == "anime") {
					$query = "body[data-work=\"{type}\"] span.add a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .add-edit-more a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-work=\"{type}\"] .progress-{anime_id}, body[data-work=\"{type}\"] #tags-{anime_id} { display: none !important; } body[data-work=\"{type}\"] tr.list-table-data > #tags-{anime_id} ~ td { display: none !important; } body[data-work=\"{type}\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: none; } body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: hidden !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-work=\"{type}\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/{type}/{anime_id}/\"]::before { content:\"unavailable\" !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-work=\"{type}\"] span.add a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .add-edit-more a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-work=\"{type}\"] .progress-{manga_id}, body[data-work=\"{type}\"] #tags-{manga_id} { display: none !important; } body[data-work=\"{type}\"] tr.list-table-data > #tags-{manga_id} ~ td { display: none !important; } body[data-work=\"{type}\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: none; } body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: hidden !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-work=\"{type}\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/{type}/{manga_id}/\"]::before { content:\"unavailable\" !important; }";
				}
				$query = $query;
			} else if (strtolower($querywas) == "showownerhentai" || strtolower($querywas) == "showownergenre" || str_contains(strtolower($querywas), "showownerhentai")) {
				if (strtolower($type) == "anime") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{anime_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{anime_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{anime_id}, body[data-owner=\"1\"] #tags-{anime_id} { display: inline-block !important; } body[data-owner=\"1\"] tr.list-table-data > #tags-{anime_id} ~ td { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: inherit !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{manga_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{manga_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{manga_id}, body[data-owner=\"1\"] #tags-{manga_id} { display: inline-block !important; } body[data-owner=\"1\"] tr.list-table-data > #tags-{manga_id} ~ td { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: inherit !important; }";
				}
			}
			if (strtolower($querywas) == "synopsis") {
				if (strtolower($type) == "anime") {
					$query = 'body[data-work="' . $type . '"] #tags-{anime_id}:after {content: \'{synopsis}\';}';
				} else if (strtolower($type) == "manga") {	
					$query = 'body[data-work="' . $type . '"] #tags-{manga_id}:after {content: \'{synopsis}\';}';
				}
			}
            $result = $myMalScraper->getUserCSS($user, $type, $query, $status, $genre);
			$decodedResult = json_decode($result, true);
			$result = json_decode($result, true);
			if ($result['data'] == 'No UserList') {
				$result = $result['data'];
				print_r(paramError('3'));

				break;
			}
            $result = $result['data'];
			$result = str_replace('-a', '\a', $result);
			$result = str_replace('-"', '\"', $result);
			$result = str_replace("-'", "\'", $result);
			$result = str_replace("[DEL]", "", $result);
			$result = preg_replace('/"([ef][0-9a-f]{3,}) /', '"\\\$1 ', $result);
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} else if (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
			$filePath = '../cache/' . $user . '_' . $type . '_' . $status . '_' . hash('sha256', $query) . '_' . $genre . '.css';
			
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				$result = "/* User: " . $user . " - " . $type . ", query: " . str_replace('*/', '*[DEL]/', $query) . " */ \r" . $result;
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
			$type = "anime";
			}
			if (strtolower($querywas) == "hidehentai" || strtolower($querywas) == "hidegenre" || str_contains(strtolower($querywas), "hidehentai")) {
				if (strtolower($type) == "anime") {
					$query = "body[data-work=\"{type}\"] span.add a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .add-edit-more a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-work=\"{type}\"] .progress-{anime_id}, body[data-work=\"{type}\"] #tags-{anime_id} { display: none !important; } body[data-work=\"{type}\"] tr.list-table-data > #tags-{anime_id} ~ td { display: none !important; } body[data-work=\"{type}\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: none; } body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: hidden !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"], body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-work=\"{type}\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-work=\"{type}\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/{type}/{anime_id}/\"]::before { content:\"unavailable\" !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-work=\"{type}\"] span.add a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .add-edit-more a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-work=\"{type}\"] .progress-{manga_id}, body[data-work=\"{type}\"] #tags-{manga_id} { display: none !important; } body[data-work=\"{type}\"] tr.list-table-data > #tags-{manga_id} ~ td { display: none !important; } body[data-work=\"{type}\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: none; } body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: hidden !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"{type}\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-work=\"{type}\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"], body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-work=\"{type}\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-work=\"{type}\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/{type}/{manga_id}/\"]::before { content:\"unavailable\" !important; }";
				}
				$query = $query;
			} else if (strtolower($querywas) == "showownerhentai" || strtolower($querywas) == "showownergenre" || str_contains(strtolower($querywas), "showownerhentai")) {
				if (strtolower($type) == "anime") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{anime_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{anime_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{anime_id}, body[data-owner=\"1\"] #tags-{anime_id} { display: inline-block !important; } body[data-owner=\"1\"] tr.list-table-data > #tags-{anime_id} ~ td { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: inherit !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{manga_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{manga_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{manga_id}, body[data-owner=\"1\"] #tags-{manga_id} { display: inline-block !important; } body[data-owner=\"1\"] tr.list-table-data > #tags-{manga_id} ~ td { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: inherit !important; }";
				}
			}
			if (strtolower($querywas) == "synopsis") {
				if (strtolower($type) == "anime") {
					$query = 'body[data-work=\"{type}\"] #tags-{anime_id}:after {content: \'{synopsis}\';}';
				} else if (strtolower($type) == "manga") {	
					$query = 'body[data-work=\"{type}\"] #tags-{manga_id}:after {content: \'{synopsis}\';}';
				}
			}
            $result = $myMalScraper->getUserCSS($user, $type, $query, $status, $genre);
			$decodedResult = json_decode($result, true);
			$result = json_decode($result, true);
			if ($result['data'] == 'No UserList') {
				$result = $result['data'];
				print_r(paramError('3'));

				break;
			}
            $result = $result['data'];
			$result = str_replace('-a', '\a', $result);
			$result = str_replace('-"', '\"', $result);
			$result = str_replace("-'", "\'", $result);
			$result = str_replace("[DEL]", "", $result);
			$result = preg_replace('/"([ef][0-9a-f]{3,}) /', '"\\\$1 ', $result);
			if (strtolower($size) == "small") {
				$result = str_replace('l.jpg', 't.jpg', $result);
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
			$filePath = '../cache/' . $user . '_' . $type . '_' . $status . '_' . hash('sha256', $query) . '_' . $genre . '.css';
			
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				$result = "/* User: " . $user . " - " . $type . ", query: " . str_replace('*/', '*[DEL]/', $query) . " */ \r" . $result;
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

			visit_url("https://shaggyze.website/msa/usercss", ['u' => trim($user), 't' => $type, 'st' => $status, 'q' => $query, 'g' => $genre], $query);
		} else {
            print_r(paramError('2'));
        }
        break;
    case 'user-cover':
    case 'usercover':
        if ($user) {
            header('Content-Type: text/css');

            $type = in_array(strtolower($type ?? ''), ['anime', 'manga', 'all']) ? strtolower($type ?? '') : 'anime';
			//$type = $type ? $type : 'anime';
            $genre = $genre ? $genre : 0;
			$order = $order ? $order : 0;
            $query = $query ? $query : 'dataimagelinkbefore';
			if (strlen($user) >= 17 || strlen($user) == 1) {
				print_r(paramError('3'));
				break;
			}
			if (strtolower($type) == ":type:") { $type = "all"; }
			if (strtolower($query) == ":preset:") { $query = "dataimagelinkbefore"; }
			if (strtolower($type) == "all" & ($query == "more" || str_contains(strtolower($query), 'more'))) { 
				if (str_contains(strtolower($query), 'manga')) {
					$type = "manga";
					$query = str_replace('anime', 'manga', strtolower($query));
				} else {
					$type = "anime";
					$query = str_replace('manga', 'anime', strtolower($query));
				}
			}
			if (strtolower($user) == "all" || strtolower($user) == "username" || strtolower($user) == "usernameq=body") {
				$user = "_All_";
			}
			$typewas = $type;
			$querywas = $query;
			if (strtolower($type) == "all") {
				$typewas = "all";
				$type = "manga";
				$query = getPreset($query, $type);
				$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', $query) . '_' . $genre . '_' . $order .'.css';
				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
					$printed = true;
				}
				$result = $myMalScraper->getUserCover($user, $type, $query, $genre, $order);
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
				if ($decodedResult['status'] == 200) {
					$timestamp = date('Y-m-d\TH:i:s.u\Z');
					$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
					$result = "/* User: " . $user . " - " . $type . ", query: " . $querywas . " */ \r" . $result;
					file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
					if ($printed == false) print_r($result);
				} else {

					if (file_exists($filePath)) {
						$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
						print_r($file);
					} else {
						print_r($result);
					}
				}
				$type = "anime";
			}
			$query = getPreset($querywas, $type);
			$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', $query) . '_' . $genre . '_' . $order .'.css';
			if (file_exists($filePath)) {
				$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
				print_r($file);
				$printed2 = true;
			}
            $result = $myMalScraper->getUserCover($user, $type, $query, $genre, $order);
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
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				$result = "/* User: " . $user . " - " . $type . ", query: " . $querywas . " */ \r" . $result;
				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
				if ($printed2 == false) print_r($result);
			} else {

				if (file_exists($filePath)) {
					$file = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
					print_r($file);
				} else {
					print_r($result);
				}
			}

			visit_url("https://shaggyze.website/msa/usercover", ['u' => trim($user), 't' => $type, 'q' => $query, 'g' => $genre, 'o' => $order], $query);
		} else {
            print_r(paramError('2'));
        }
        break;
		
// Depriciated, but can still work ----------
    case 'auto-cover':
    case 'autocover':
        header('Content-Type: text/css');

		$user_url = $_SERVER['HTTP_REFERER'] ?? 'https://myanimelist.net/animelist/_All_';
		$path = parse_url($user_url, PHP_URL_PATH);
		$path = str_replace('.css', '', $path);
		if (preg_match("/\/(animelist|mangalist)\/(.*)/", $path, $matches)) {
			$type = str_replace('list', '', $matches[1]);
			$user = $matches[2];
		} else {
			$type = 'anime';
			$user = '_All_';
		}

        $type = $type ? $type : 'anime';
        $genre = $genre ? $genre : 0;
		$order = $order ? $order : 0;
        $query = $query ? $query : false;

        $result = $myMalScraper->getUserCover($user, $type, $query, $genre, $order);
        $result = json_decode($result, true);
        $result = $result['data'];
		$timestamp = date('Y-m-d\TH:i:s.u\Z');
		$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
		$result = "/* User: " . $user . " - " . $type . ", query: " . $query . " */ \r" . $result;
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
