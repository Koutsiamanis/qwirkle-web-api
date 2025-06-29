<?php
require_once "lib/dbconnect.php";
require_once 'lib/board.php';
require_once 'lib/users.php';


$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (empty($contentType)) {
    header('Content-Type: application/json');
    $_SERVER['CONTENT_TYPE'] = 'application/json';
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    $input = [];
}


if ($request[0] === 'player' && $method === 'POST') {
    $username = $input['username'] ?? null;

    if (!$username) {
        echo json_encode(['error' => 'Username is required'], JSON_PRETTY_PRINT);
        exit;
    }

    $response = connectPlayer($username);
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}


if ($request[0] === 'board') {
    if ($method === 'GET' && count($request) === 1) {
        // GET /board -> Επιστροφή κατάστασης του board
        echo json_encode(getBoard(), JSON_PRETTY_PRINT);
        exit;
    } elseif ($method === 'POST' && count($request) === 2 && $request[1] === 'start') {
        // POST /board/start -> Έναρξη του παιχνιδιού
        echo json_encode(startGame(), JSON_PRETTY_PRINT);
        exit;
    } elseif ($method === 'POST' && count($request) === 2 && $request[1] === 'place') {
        // POST /board/place -> Τοποθέτηση πλακιδίων
        $tiles = $input['tiles'] ?? null;

        if (!$tiles || !is_array($tiles)) {
            echo json_encode(['error' => 'Tiles are required'], JSON_PRETTY_PRINT);
            exit;
        }

        echo json_encode(placeTiles($tiles), JSON_PRETTY_PRINT);
        exit;
    } else {
        echo json_encode(['error' => 'Invalid request for board'], JSON_PRETTY_PRINT);
        exit;
    }
}


echo json_encode(['error' => 'Invalid endpoint'], JSON_PRETTY_PRINT);
?>
