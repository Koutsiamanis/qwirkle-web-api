<?php

function connectPlayer() {
    global $mysqli;


    $query = "SELECT COUNT(*) as player_count FROM players";
    $result = $mysqli->query($query);

    if (!$result) {
        return ['error' => 'Database error: ' . $mysqli->error];
    }

    $row = $result->fetch_assoc();
    if ($row['player_count'] >= 2) {
        return ['error' => 'The game already has the maximum number of players.'];
    }


    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');

    if (empty($username)) {
        return ['error' => 'Username is required.'];
    }

    $query = "SELECT token FROM players WHERE name = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['error' => 'Database error: ' . $mysqli->error];
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return ['success' => true, 'token' => $row['token']];
    }

    
    $token = md5($username . time());

    $query = "INSERT INTO players (name, token, score) VALUES (?, ?, 0)";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return ['error' => 'Database error: ' . $mysqli->error];
    }
    $stmt->bind_param('ss', $username, $token);

    if ($stmt->execute()) {
        return ['success' => true, 'token' => $token];
    } else {
        return ['error' => 'Failed to connect player: ' . $mysqli->error];
    }
}

function authenticatePlayer($token) {
    global $mysqli;

    $query = "SELECT * FROM players WHERE token = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        return false; 
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

     // Debugging
     var_dump('Token Passed to DB:', $token);
     var_dump('DB Result Rows:', $result->num_rows);

    return $result->num_rows > 0;
}

function getPlayerTokenByName($username) {
    global $mysqli;

    $query = "SELECT token FROM players WHERE name = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['token'];
    }

    return null;
}

?>
