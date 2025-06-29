<?php

function getBoard()
{
    global $mysqli;

    $query = "SELECT * FROM board ORDER BY x, y";
    $result = $mysqli->query($query);

    if (!$result) {
        return ['error' => 'Failed to fetch board: ' . $mysqli->error];
    }

    $board = [];
    while ($row = $result->fetch_assoc()) {
        $board[] = $row;
    }

    return $board;
}

function startGame()
{
    global $mysqli;


    $query = "SELECT name FROM players";
    $result = $mysqli->query($query);

    if (!$result || $result->num_rows !== 2) {
        return ['error' => 'The game cannot start. Two players are required.'];     
    }

    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row['name'];
    }

    $player1 = $players[0];
    $player2 = $players[1];


    $query = "CALL clean_board()";
    if (!$mysqli->query($query)) {
        return ['error' => 'Failed to reset the board: ' . $mysqli->error];
    }


    $query = "INSERT INTO games (player1, player2, current_player, status) VALUES (?, ?, ?, 'started')";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $player1, $player2, $player1);

    if (!$stmt->execute()) {
        return ['error' => 'Failed to initialize game: ' . $mysqli->error];
    }


    $player1Tiles = [
        ['color' => 'red', 'shape' => 'circle'],
        ['color' => 'blue', 'shape' => 'square'],
        ['color' => 'green', 'shape' => 'triangle'],
        ['color' => 'yellow', 'shape' => 'circle'],
        ['color' => 'purple', 'shape' => 'square'],
        ['color' => 'orange', 'shape' => 'triangle']
    ];

    $player2Tiles = [
        ['color' => 'red', 'shape' => 'square'],
        ['color' => 'blue', 'shape' => 'circle'],
        ['color' => 'green', 'shape' => 'square'],
        ['color' => 'yellow', 'shape' => 'triangle'],
        ['color' => 'purple', 'shape' => 'circle'],
        ['color' => 'orange', 'shape' => 'square']
    ];

    if (!assignTilesToPlayer($player1, $player1Tiles) || !assignTilesToPlayer($player2, $player2Tiles)) {
        return ['error' => 'Failed to assign tiles to players'];
    }

    return ['success' => 'Game started successfully', 'player1' => $player1, 'player2' => $player2];
}


function assignTilesToPlayer($player, $tiles)
{
    global $mysqli;

    foreach ($tiles as $tile) {
        $query = "UPDATE tiles SET owner = ? WHERE color = ? AND shape = ? AND owner IS NULL LIMIT 1";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sss', $player, $tile['color'], $tile['shape']);

        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            return false; 
        }
    }

    return true;
}

function assignNewTile($playerName) {
    global $mysqli;


    $query = "SELECT color, shape FROM tiles WHERE owner IS NULL AND placed = FALSE LIMIT 1";
    $result = $mysqli->query($query);

    if ($result->num_rows === 0) {
        return ['error' => 'No tiles available'];
    }

    $tile = $result->fetch_assoc();


    $query = "UPDATE tiles SET owner = ? WHERE color = ? AND shape = ? AND owner IS NULL LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('sss', $playerName, $tile['color'], $tile['shape']);

    if (!$stmt->execute()) {
        return ['error' => 'Failed to assign new tile: ' . $stmt->error];
    }

    return ['success' => true, 'tile' => $tile];
}

function placeTiles($tiles) {
    global $mysqli;


    $query = "SELECT current_player FROM games WHERE status = 'started' LIMIT 1";
    $result = $mysqli->query($query);

    if (!$result || $result->num_rows === 0) {
        return ['error' => 'Game not found'];
    }

    $currentPlayer = $result->fetch_assoc()['current_player'];

    $totalPoints = 0;

    foreach ($tiles as $tile) {
        $x = $tile['position']['x'];
        $y = $tile['position']['y'];
        $color = $tile['color'];
        $shape = $tile['shape'];

        $query = "CALL place_tile(?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sssii', $currentPlayer, $color, $shape, $x, $y);

        if (!$stmt->execute()) {
            return ['error' => $stmt->error];
        }

        $points = calculatePoints($x, $y);
        $totalPoints += $points;
    }

    $query = "UPDATE players SET score = score + ? WHERE name = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('is', $totalPoints, $currentPlayer);

    if (!$stmt->execute()) {
        return ['error' => 'Failed to update player score: ' . $stmt->error];
    }

    $query = "SELECT COUNT(*) as remaining FROM tiles WHERE placed = TRUE";
    $result = $mysqli->query($query);

    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['remaining'] === 0) {

            $query = "SELECT name, score FROM players ORDER BY score DESC LIMIT 1";
            $result = $mysqli->query($query);

            if ($result && $result->num_rows > 0) {
                $winner = $result->fetch_assoc();

                $query = "UPDATE games SET status = 'ended', winner = ? WHERE status = 'started'";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('s', $winner['name']);

                if (!$stmt->execute()) {
                    return ['error' => 'Failed to end game: ' . $stmt->error];
                }

                return [
                    'success' => true,
                    'message' => 'Game ended',
                    'winner' => $winner['name'],
                    'score' => $winner['score']
                ];
            }
        }
    }


    $newTile = assignNewTile($currentPlayer);

    if (isset($newTile['error'])) {
        return ['error' => $newTile['error']];
    }

    return [
        'success' => true,
        'message' => 'Tiles placed successfully',
        'player' => $currentPlayer,
        'points' => $totalPoints,
        'new_score' => $totalPoints,
        'new_tile' => $newTile['tile']
    ];
}


function calculatePoints($x, $y)
{
    global $mysqli;

    $query = "SELECT COUNT(*) as count FROM board WHERE y = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $y);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $rowCount = $row['count'];

    $query = "SELECT COUNT(*) as count FROM board WHERE x = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $x);
    $stmt->execute();
    $result = $stmt->get_result();
    $col = $result->fetch_assoc();
    $colCount = $col['count'];

    $points = $rowCount + $colCount;

    if ($rowCount === 6 || $colCount === 6) {
        $points += 6;
    }

    return $points;
}
