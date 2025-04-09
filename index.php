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

ini_set('max_execution_time', 2000);
ini_set('memory_limit', "1024M");
ini_set('max_file_size', 1000000000);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$order = isset($_GET['o']) ? $_GET['o'] : 0;

$year = isset($_GET['y']) ? $_GET['y'] : date('Y');
$season = isset($_GET['s']) ? $_GET['s'] : getCurrentSeason();

$user = isset($_GET['u']) ? $_GET['u'] : '';
$status = isset($_GET['st']) ? $_GET['st'] : 7;

$size = isset($_GET['sz']) ? $_GET['sz'] : '';
$nonseasonal = isset($_GET['ns']) ? $_GET['ns'] : '';

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

function get_subdirectory($type, $id) {
  $id = intval($id);
  $subdirectory_number = floor($id / 10000);
  $subdirectory_path = '../info/' . $type . '/' . $subdirectory_number . '/';
  if (!is_dir($subdirectory_path) && ($type == "anime" || $type == "manga")) {
    mkdir($subdirectory_path, 0777, true); // Create directory with permissions
  }
  return strval($subdirectory_number);
}

function clearCronJobs($scriptPath) {
    $existingCrontab = shell_exec('crontab -l 2>&1');
    $crontabLines = explode("\n", trim($existingCrontab));

    $newCrontabLines = [];
    foreach ($crontabLines as $line) {
        if (strpos($line, $scriptPath) === false) {
            $newCrontabLines= $line;
        }
    }

    $finalCrontab = implode("\n", $newCrontabLines) . "\n";
    $command = "echo " . escapeshellarg($finalCrontab) . " | crontab -";
    exec($command);
}

function visit_url($url_base, $params, $query) {
    $scriptPath = '/home/shagzgjm/public_html/scripts/visit_url.php';
    $qFilePath = '/home/shagzgjm/public_html/scripts/q_files/';
    $debugFile = '/home/shagzgjm/public_html/scripts/debug.log';
    $lockFile = '/tmp/visit_url.lock'; // Temporary lock file
    $enableLocking = false; // Set to false to disable locking for testing

    if (!is_dir($qFilePath)) {
        mkdir($qFilePath, 0755, true);
    }

    if ($enableLocking) {
        // Acquire Lock with Timeout
        $lockHandle = fopen($lockFile, 'c+');
        $lockStartTime = time();
        $locked = false;

        if ($lockHandle) {
            while (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
                if ((time() - $lockStartTime) >= 300) { // 5 minutes timeout
                    fclose($lockHandle);
                    //file_put_contents($debugFile, "Failed to acquire lock after 5 minutes, giving up.\n", FILE_APPEND);
                    return;
                }
                sleep(60); // Wait for 1 minute
            }
            $locked = true;
        } else {
            //file_put_contents($debugFile, "Could not open lock file.\n", FILE_APPEND);
            return;
        }

        if (!$locked) {
            return; // Exit if locking failed
        }
    }

    try {
        $existingCrontabOutput = shell_exec('crontab -l 2>&1');
        //file_put_contents($debugFile, "--- Crontab Output ---\n" . $existingCrontabOutput . "\n---\n", FILE_APPEND);
        $crontabLines = explode("\n", trim($existingCrontabOutput));
        //file_put_contents($debugFile, "Contents of \$crontabLines:\n" . print_r($crontabLines, true) . "\n---\n", FILE_APPEND);

        $existingCronJobDetails = [];
        $index = 0;
        foreach ($crontabLines as $line) {
            if (strpos($line, $scriptPath) !== false) {
                $details = parseCronJobDetails($line);
                //file_put_contents($debugFile, "Details after parsing: " . print_r($details, true) . "\n", FILE_APPEND);
                if (is_array($details)) {
                    $existingCronJobDetails[$index] = $details;
                    $index++;
                } else {
                    //file_put_contents($debugFile, "parseCronJobDetails failed for line: " . $line . "\n", FILE_APPEND);
                }
            }
        }

        //file_put_contents($debugFile, "Contents of \$existingCronJobDetails after parsing:\n" . print_r($existingCronJobDetails, true) . "\n---\n", FILE_APPEND);

        $urlParams = array_merge(['url' => $url_base], $params);
        if (isset($query) && !empty($query)) {
            $qFileName = 'q_' . hash('sha256', $query) . '.txt';
            file_put_contents($qFilePath . $qFileName, $query);
            $urlParams['qfile'] = $qFilePath . $qFileName;
            unset($urlParams['q']);
        }
        ksort($urlParams);

        $cronUrl = $url_base; // Define $cronUrl here

        // --- START Duplicate Check ---
        $cronJobDetails = [
            'url' => $cronUrl,
            'params' => $urlParams
        ];

        $isDuplicate = false;
        foreach ($existingCronJobDetails as $existingJob) {
            // Compare the base URL
            if (isset($existingJob['url']) && $existingJob['url'] == $cronUrl) {
                $paramsMatch = true;

                // First, check if all parameters in the current job match the existing job
                if (isset($cronJobDetails['params']) && is_array($cronJobDetails['params'])) {
                    foreach ($cronJobDetails['params'] as $key => $value) {
                        if ($key !== 'url') {
                            if (!isset($existingJob['params'][$key]) || $existingJob['params'][$key] != $value) {
                                $paramsMatch = false;
                                break;
                            }
                        }
                    }
                } else {
                    $paramsMatch = false;
                }

                // If the parameters from the current job match, now check if the existing job has any extra parameters
                if ($paramsMatch && isset($existingJob['params']) && is_array($existingJob['params'])) {
                    foreach ($existingJob['params'] as $key => $value) {
                        if ($key !== 'url') {
                            if (!isset($cronJobDetails['params'][$key]) || $cronJobDetails['params'][$key] != $value) {
                                $paramsMatch = false;
                                break;
                            }
                        }
                    }
                }

                // If both URL and parameters match, it's a duplicate
                if ($paramsMatch) {
                    $isDuplicate = true;
                    file_put_contents($debugFile, "-- Duplicate Job Found --\n", FILE_APPEND);
                    break;
                }
            }
        }

        //file_put_contents($debugFile, "Contents of \$existingCronJobDetails AFTER duplicate check:\n" . print_r($existingCronJobDetails, true) . "\n---\n", FILE_APPEND);
        // --- END Duplicate Check ---

        if (!$isDuplicate) {
            //file_put_contents($debugFile, "-- New Cron Job Will Be Added --\n", FILE_APPEND);

            // Determine the next cron job time (line 202)
            $validMinutes = [1, 6, 11, 16, 21, 26, 31, 36, 41, 46, 51, 56];
            $startHourInterval = 6;
            $foundSlot = false;
            $cronMinute = 1;
            $cronHour = 6;

            for ($hourInterval = $startHourInterval; $hourInterval <= 12; $hourInterval++) {
                foreach ($validMinutes as $minute) {
                    $jobCountForMinute = 0;
                    foreach ($existingCronJobDetails as $job) {
                        if (isset($job['minute']) && (int)$job['minute'] === $minute && isset($job['interval']) && (int)$job['interval'] === $hourInterval) {
                            $jobCountForMinute++;
                        }
                    }

                    if ($jobCountForMinute < 5) {
                        $cronMinute = $minute;
                        $cronHour = $hourInterval;
                        $foundSlot = true;
                        break 2; // Break out of both loops
                    }
                }
            }

            if (!$foundSlot) {
                // Fallback, maybe clear jobs or log an error
                clearCronJobs('/home/shagzgjm/public_html/scripts/visit_url.php');
                return;
            }

            // Added logging for calculated cron time
            //file_put_contents($debugFile, "Calculated Next Cron Time: HourInterval=" . $cronHour . ", Minute=" . $cronMinute . "\n", FILE_APPEND);

            $existingCrontab = shell_exec('crontab -l'); // Re-fetching here to ensure it's up-to-date
            $cronJobCommand = $cronMinute . " */" . $cronHour . " * * * /usr/local/bin/php " . $scriptPath;
            foreach ($urlParams as $key => $value) {
                $cronJobCommand .= " " . $key . "='" . $value . "'";
            }
            //$cronJobCommand .= " >> /home/shagzgjm/cron_output.log 2>> /home/shagzgjm/cron_errors.log";

            $newCronJobLine = $cronJobCommand; // Assign the value to $newCronJobLine

            $finalCrontab = trim($existingCrontab) . "\n" . $newCronJobLine . "\n";
            $command = "echo " . escapeshellarg($finalCrontab) . " | crontab -";
            exec($command);

            // Debugging log
            //file_put_contents($debugFile, "Added cron job: " . $cronJobCommand . "\n", FILE_APPEND);
        } else {
            //file_put_contents($debugFile, "-- Cron Job Not Added (Duplicate) --\n", FILE_APPEND);
        }

    } finally {
        if ($enableLocking && isset($lockHandle)) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}

function parseCronJobDetails($jobLine) {
    $parts = explode(' ', $jobLine);
    if (count($parts) < 6) {
        return null; // Invalid cron job line
    }

    $minute = $parts[0];
    $hourPart = $parts[1];
    $scriptPath = '/home/shagzgjm/public_html/scripts/visit_url.php';

    if ($parts[5] !== '/usr/local/bin/php' || (isset($parts[6]) && $parts[6] !== $scriptPath)) {
        return null; // Not our script
    }

    $details = [
        'minute' => $minute,
        'hour' => null, // Changed to null initially
        'interval' => null, // Added a field for the interval
        'url' => null,
        'params' => [],
    ];

    if (preg_match('/^\*\/\d+$/', $hourPart, $matches)) {
        $details['interval'] = (int)substr($hourPart, 2);
        $details['hour'] = 0; // Maybe set hour to 0 or some indicator that it's an interval
    } else {
        $details['hour'] = (int)$hourPart;
    }

    $foundScript = false;
    for ($i = 6; $i < count($parts); $i++) {
        if (!$foundScript && $parts[$i] === $scriptPath) {
            $foundScript = true;
            continue;
        }

        if ($foundScript && strpos($parts[$i], '=') !== false) {
            list($key, $valueWithQuotes) = explode('=', $parts[$i], 2);
            $value = trim($valueWithQuotes, "'");
            $details['params'][$key] = $value;
            if ($key === 'url') {
                $details['url'] = $value;
            }
        }
    }
    ksort($details['params']);
    return $details;
}

function getPreset($query, $type) {
			if (strtolower($query) == "more") {
				if (strtolower($type) == "anime") {
					$query = "#more{anime_id} {background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = "#more{manga_id} {background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "moretdbeforelink") {
				if (strtolower($type) == "anime") {
					$query = "#more{anime_id} td:before, a[href^=\"/{type}/{anime_id}/\"]:before {background:url(\"{anime_image_path}\")}";
				} else {
					$query = "#more{manga_id} td:before, a[href^=\"/{type}/{manga_id}/\"]:before {background:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "animetitle") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "animetitlebefore") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "animetitleafter") {
				if (strtolower($type) == "anime") {
					$query = ".animetitle a[href^=\"/{type}/{anime_id}/\"]:after{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".animetitle a[href^=\"/{type}/{manga_id}/\"]:after{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "datatitle") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "datatitlebefore") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "datatitleafter") {
				if (strtolower($type) == "anime") {
					$query = ".data.title a[href^=\"/{type}/{anime_id}/\"]:after{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.title a[href^=\"/{type}/{manga_id}/\"]:after{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "dataimagelink") {
				if (strtolower($type) == "anime") {
					$query = ".data.image a[href^=\"/{type}/{anime_id}/\"]{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.image a[href^=\"/{type}/{manga_id}/\"]{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "dataimagelinkbefore" || strtolower($query) == ":preset:") {
				if (strtolower($type) == "anime") {
					$query = ".data.image a[href^=\"/{type}/{anime_id}/\"]:before{background-image:url(\"{anime_image_path}\")}";
				} else {
					$query = ".data.image a[href^=\"/{type}/{manga_id}/\"]:before{background-image:url(\"{manga_image_path}\")}";
				}
			} elseif (strtolower($query) == "dataimagelinkafter") {
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
			$subdirectory = get_subdirectory($type, $id);
			$decodedResult = json_decode($result, true);
			$filePath = '../info/' . $type . '/' . $subdirectory . '/' . $id . '.json';

			if ($decodedResult['status'] == 200) {

$imageUrls = $decodedResult['data']['images'];
if ($imageUrls) {
    $reverseCoverFilePath = '../info/reversecover.json';

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
						print_r ('a.status-button.all_anime::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['anime_stat']['status']['total'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.watching::after{content: "(' . $decodedResult['data']['anime_stat']['status']['watching'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['anime_stat']['status']['completed'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['anime_stat']['status']['on_hold'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['anime_stat']['status']['dropped'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantowatch::after{content: "(' . $decodedResult['data']['anime_stat']['status']['plan_to_watch'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}');
					} else if (strtolower($type) == "manga") {
						print_r ('a.status-button.all_anime::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.all_anime::after{content: "(' . $decodedResult['data']['manga_stat']['status']['total'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.watching::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.reading::after{content: "(' . $decodedResult['data']['manga_stat']['status']['reading'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.completed::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.completed::after{content: "(' . $decodedResult['data']['manga_stat']['status']['completed'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.onhold::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.onhold::after{content: "(' . $decodedResult['data']['manga_stat']['status']['on_hold'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.dropped::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.dropped::after{content: "(' . $decodedResult['data']['manga_stat']['status']['dropped'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}a.status-button.plantowatch::before{ content:"\f6ed ";padding: inherit;  width: inherit;  margin-left: inherit;  font-size: 16px !important;  position: inherit;}a.status-button.plantoread::after{content: "(' . $decodedResult['data']['manga_stat']['status']['plan_to_read'] . ')" !important;color: red !important; opacity: 1 !important;  font-size: 12px !important;    background: transparent !important;    top: 33px !important;}');
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
            $result = $myMalScraper->getUserList($user, $type, $status, $genre, $order);
			$decodedResult = json_decode($result, true);
			$filePath = '../userlist/' . $user . '_' . $type . '_' . $status . '_' . $genre .  '_' . $order . '.json';
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
			$status = $status ? $status : 7;
            $genre = $genre ? $genre : 0;
            $query = $query ? $query : false;
			if (strlen($user) >= 17 || strlen($user) == 1) {
				print_r(paramError('3'));
				break;
			}
			if (strtolower($type) == ":type:") { $type = "all"; }
			if (strtolower($user) == "all" & strtolower($type) == "all" & strtolower($query) == "more") { $type = "anime"; }
			if (strtolower($user) == "all" || strtolower($user) == "username") {
				$user = "_All_";
			}
			$typewas = $type;
			$querywas = $query;
			if (strtolower($type) == "all") {
				$typewas = "all";
				$type = "manga";
			if (strtolower($querywas) == "hidehentai" || strtolower($querywas) == "hidegenre") {
				if (strtolower($type) == "anime") {
					$query = "body[data-work=\"anime\"] span.add a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .add-edit-more a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .link[href^=\"/anime/{anime_id}/\"] ~ .add-edit-more, body[data-work=\"anime\"] .progress-{anime_id}, body[data-work=\"anime\"] #tags-{anime_id} { display: none !important; } body[data-work=\"anime\"] #tags-{anime_id} ~ * { display: none !important; } body[data-work=\"anime\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: none; } body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: hidden !important; } body[data-work=\"anime\"] .data.title a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"anime\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-work=\"anime\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-work=\"anime\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/anime/{anime_id}/\"]::before { content:\"unavailable\" !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-work=\"manga\"] span.add a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .add-edit-more a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .link[href^=\"/manga/{manga_id}/\"] ~ .add-edit-more, body[data-work=\"manga\"] .progress-{manga_id}, body[data-work=\"manga\"] #tags-{manga_id} { display: none !important; } body[data-work=\"manga\"] #tags-{manga_id} ~ * { display: none !important; } body[data-work=\"manga\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: none; } body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: hidden !important; } body[data-work=\"manga\"] .data.title a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"manga\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-work=\"manga\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-work=\"manga\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/manga/{manga_id}/\"]::before { content:\"unavailable\" !important; }";
				}
				$query = $query;
			} else if (strtolower($querywas) == "showownerhentai" || strtolower($querywas) == "showownergenre") {
				if (strtolower($type) == "anime") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{anime_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{anime_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{anime_id}, body[data-owner=\"1\"] #tags-{anime_id} { display: inline-block !important; } body[data-owner=\"1\"] #tags-{anime_id} ~ * { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: inherit !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{manga_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{manga_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{manga_id}, body[data-owner=\"1\"] #tags-{manga_id} { display: inline-block !important; } body[data-owner=\"1\"] #tags-{manga_id} ~ * { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: inherit !important; }";
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
				$result = "/* User: " . $user . " - " . $type . ", query: " . str_replace('*/', '*[DEL]/', $querywas) . " */ \r" . $result;
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
			if (strtolower($querywas) == "hidehentai" || strtolower($querywas) == "hidegenre") {
				if (strtolower($type) == "anime") {
					$query = "body[data-work=\"anime\"] span.add a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .add-edit-more a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .link[href^=\"/anime/{anime_id}/\"] ~ .add-edit-more, body[data-work=\"anime\"] .progress-{anime_id}, body[data-work=\"anime\"] #tags-{anime_id} { display: none !important; } body[data-work=\"anime\"] #tags-{anime_id} ~ * { display: none !important; } body[data-work=\"anime\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: none; } body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: hidden !important; } body[data-work=\"anime\"] .data.title a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"anime\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-work=\"anime\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"], body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-work=\"anime\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-work=\"anime\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/anime/{anime_id}/\"]::before { content:\"unavailable\" !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-work=\"manga\"] span.add a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .add-edit-more a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .link[href^=\"/manga/{manga_id}/\"] ~ .add-edit-more, body[data-work=\"manga\"] .progress-{manga_id}, body[data-work=\"manga\"] #tags-{manga_id} { display: none !important; } body[data-work=\"manga\"] #tags-{manga_id} ~ * { display: none !important; } body[data-work=\"manga\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: none; } body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: hidden !important; } body[data-work=\"manga\"] .data.title a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 0 !important; } body[data-work=\"manga\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-work=\"manga\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { font-size: 13px; content: \"Unavailable\"; } body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"], body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-work=\"manga\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-work=\"manga\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: url(https://shaggyze.github.io/Themes/unavailable.png) !important; border: 2px solid #fff; } .list-table .list-table-data:hover .data.title .link[href^=\"/manga/{manga_id}/\"]::before { content:\"unavailable\" !important; }";
				}
				$query = $query;
			} else if (strtolower($querywas) == "showownerhentai" || strtolower($querywas) == "showownergenre") {
				if (strtolower($type) == "anime") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{anime_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{anime_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{anime_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{anime_id}, body[data-owner=\"1\"] #tags-{anime_id} { display: inline-block !important; } body[data-owner=\"1\"] #tags-{anime_id} ~ * { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{anime_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{anime_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{anime_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{anime_id}/\"]:before { background-image: inherit !important; }";
				} else if (strtolower($type) == "manga") {
					$query = "body[data-owner=\"1\"] span.add a[href*=\"{manga_id}\"], body[data-owner=\"1\"] .add-edit-more a[href*=\"/{manga_id}/\"] { display: inline-block !important; } body[data-owner=\"1\"] .link[href^=\"/{type}/{manga_id}/\"] ~ .add-edit-more, body[data-owner=\"1\"] .progress-{manga_id}, body[data-owner=\"1\"] #tags-{manga_id} { display: inline-block !important; } body[data-owner=\"1\"] #tags-{manga_id} ~ * { display: table-cell !important; } body[data-owner=\"1\"] td > a[href*=\"/{manga_id}/\"] { pointer-events: auto; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"] .image { visibility: visible !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort { font-size: 12px !important; } body[data-owner=\"1\"] .data.title a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title.clearfix a[href*=\"/{manga_id}/\"].link.sort:after { content: \"\"; } body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"], body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:before, body[data-owner=\"1\"] .data.image a[href*=\"/{manga_id}/\"]:after, body[data-owner=\"1\"] .data.title [href^=\"/{manga_id}/\"]:before { background-image: inherit !important; }";
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
			} elseif (strtolower($size) == "medium") {
				$result = str_replace('l.jpg', '.jpg', $result);
			}
			$filePath = '../cache/' . $user . '_' . $type . '_' . $status . '_' . hash('sha256', $query) . '_' . $genre . '.css';
			
			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				$result = "/* User: " . $user . " - " . $type . ", query: " . str_replace('*/', '*[DEL]/', $querywas) . " */ \r" . $result;
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

//$scriptPath = '/home/shagzgjm/public_html/scripts/visit_url.php'; // Replace with your script path
//clearCronJobs($scriptPath);
			visit_url("https://shaggyze.website/msa/usercss", ['u' => trim($user), 't' => $type, 'st' => $status, 'q' => $query, 'g' => $genre], $query);
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
			$order = $order ? $order : 0;
            $query = $query ? $query : 'dataimagelinkbefore';

			if (strlen($user) >= 17 || strlen($user) == 1) {
				print_r(paramError('3'));
				break;
			}
			if (strtolower($type) == ":type:") { $type = "all"; }
			if (strtolower($query) == ":preset:") { $query = "dataimagelinkbefore"; }
			if (strtolower($user) == "all" & strtolower($type) == "all" & strtolower($query) == "more") { $type = "anime"; }
			if (strtolower($user) == "all" || strtolower($user) == "username") {
				$user = "_All_";
			}
			$typewas = $type;
			$querywas = $query;
			if (strtolower($type) == "all") {
				$typewas = "all";
				$type = "manga";
				$query = getPreset($query, $type);
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
				$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', $query) . '_' . $genre . '_' . $order .'.css';
				if ($decodedResult['status'] == 200) {
					$timestamp = date('Y-m-d\TH:i:s.u\Z');
					$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
					$result = "/* User: " . $user . " - " . $type . ", query: " . $querywas . " */ \r" . $result;
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
			$query = getPreset($querywas, $type);
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
			$filePath = '../cache/' . $user . '_' . $type . '_' . hash('sha256', $query) . '_' . $genre . '_' . $order .'.css';

			if ($decodedResult['status'] == 200) {
				$timestamp = date('Y-m-d\TH:i:s.u\Z');
				$result = "/* Accessed  " . $timestamp . " */ \r" . $result;
				file_put_contents($filePath, $result, FILE_USE_INCLUDE_PATH);
			$result = "/* User: " . $user . " - " . $type . ", query: " . $querywas . " */ \r" . $result;
				print_r($result);
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
		$order = $order ? $order : 0;
        $query = $query ? $query : false;

        $result = $myMalScraper->getUserCover($user, $type, $query, $genre, $order);
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
