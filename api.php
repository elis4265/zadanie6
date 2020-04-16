<?php

define("ERROR_BAD_REQUEST", array("code" => 0, "message" => "Bad request"));
define("ERROR_UNKNOWN_METHOD", array("code" => 1, "message" => "Unknown method"));
define("ERROR_MISSING_PARAM", array("code" => 2, "message" => "Missing param"));
define("ERROR_DATABASE_CONN", array("code" => 3, "message" => "Database connection failed"));
define("ERROR_QUERY", array("code" => 4, "message" => "Database query failed"));
define("ERROR_NAME_NOT_FOUND", array("code" => 5, "message" => "Name not found"));

function makeError($error, $id = null, $data = null) {
    if ($data) {
        $error["data"] = $data;
    }
    return array("jsonrpc" => "2.0", "error" => $error, "id" => $id);
}

function makeResult($result, $id) {
    return array("jsonrpc" => "2.0", "result" => $result, "id" => $id);
}

function namesToArr($names) {
    return explode(", ", $names);
}

function getNamesForDay($conn, $id, $day, $country) {
    $q = $conn->prepare("SELECT name FROM namedays WHERE day=? AND country=?");
    $q->bind_param("ss", $day, $country);
    if (!$q->execute()) {
        return makeError(ERROR_QUERY, $id, $q->error);
    }
    $qresult = $q->get_result();
    $result = array();
    while ($row = $qresult->fetch_assoc()) {
        array_push($result, $row["name"]);
    }
    return makeResult($result, $id);
}

function getDayForName($conn, $id, $name, $country) {
    $q = $conn->prepare("SELECT day FROM namedays WHERE name=? AND country=?");
    $q->bind_param("ss", $name, $country);
    if (!$q->execute()) {
        return makeError(ERROR_QUERY, $id, $q->error);
    }
    $qresult = $q->get_result();
    
    if ($qresult->num_rows == 0) {
        return makeError(ERROR_NAME_NOT_FOUND, $id);
    }

    return makeResult($qresult->fetch_assoc()["day"], $id);
}

function getHolidaysForCountry($conn, $id, $country) {
    $q = $conn->prepare("SELECT name, day FROM holidays WHERE country=?");
    $q->bind_param("s", $country);
    if (!$q->execute()) {
        return makeError(ERROR_QUERY, $id, $q->error);
    }
    $qresult = $q->get_result();

    $result = array();
    while ($row = $qresult->fetch_assoc()) {
        array_push($result, $row);
    }
    return makeResult($result, $id);
}

function getSpecialDaysForCountry($conn, $id, $country) {
    $q = $conn->prepare("SELECT name, day FROM specialdays WHERE country=?");
    $q->bind_param("s", $country);
    if (!$q->execute()) {
        return makeError(ERROR_QUERY, $id, $q->error);
    }
    $qresult = $q->get_result();

    $result = array();
    while ($row = $qresult->fetch_assoc()) {
        array_push($result, $row);
    }
    return makeResult($result, $id);
}

function addNameDay($conn, $id, $day, $name, $country) {
    $q = $conn->prepare("INSERT INTO namedays (day, name, country) VALUES (?, ?, ?)");
    $q->bind_param("sss", $day, $name, $country);
    if (!$q->execute()) {
        return makeError(ERROR_QUERY, $id, $q->error);
    }

    return makeResult(null, $id);
}

function handleRequest($conn, $request) {
    if (!$request
        || !array_key_exists("jsonrpc", $request) 
        || $request["jsonrpc"] != "2.0"
        || !array_key_exists("method", $request)
        || !array_key_exists("params", $request)
        || !array_key_exists("id", $request)) {
        if ($request && array_key_exists("id", $request)) {
            return makeError(ERROR_BAD_REQUEST, $request["id"]);
        }
        return makeError(ERROR_BAD_REQUEST);
    }

    $method = $request["method"];
    $params = $request["params"];
    $id = $request["id"];

    // Check connection
    if ($conn->connect_error) {
        return makeError(ERROR_DATABASE_CONN, $id, $conn->connect_error);
    }

    if ($method == "getNamesForDay") {
        if (!array_key_exists("day", $params)) {
            return makeError(ERROR_MISSING_PARAM, $id, "day");
        }
        if (!array_key_exists("country", $params)) {
            $country = "SK";
        } else {
            $country = $params["country"];
        }

        $day = $params["day"];
        
        return getNamesForDay($conn, $id, $day, $country);
    } else if ($method == "getDayForName") {
        if (!array_key_exists("name", $params)) {
            return makeError(ERROR_MISSING_PARAM, $id, "name");
        }
        if (!array_key_exists("country", $params)) {
            $country = "SK";
        } else {
            $country = $params["country"];
        }

        $name = $params["name"];
        
        return getDayForName($conn, $id, $name, $country);
    } else if ($method == "getHolidaysForCountry") {
        if (!array_key_exists("country", $params)) {
            $country = "SK";
        } else {
            $country = $params["country"];
        }
        
        return getHolidaysForCountry($conn, $id, $country);
    } else if ($method == "getSpecialDaysForCountry") {
        if (!array_key_exists("country", $params)) {
            $country = "SK";
        } else {
            $country = $params["country"];
        }
        
        return getSpecialDaysForCountry($conn, $id, $country);
    } else if ($method == "addNameDay") {
        if (!array_key_exists("name", $params)) {
            return makeError(ERROR_MISSING_PARAM, $id, "name");
        }
        if (!array_key_exists("day", $params)) {
            return makeError(ERROR_MISSING_PARAM, $id, "day");
        }
        if (!array_key_exists("country", $params)) {
            $country = "SK";
        } else {
            $country = $params["country"];
        }

        $name = $params["name"];
        $day = $params["day"];
        
        return addNameDay($conn, $id, $day, $name, $country);
    } else {
        return makeError(ERROR_UNKNOWN_METHOD, $id);
    }
}

$servername_t = "localhost";
$username_t = "xondreakova";
$password = "h7g3Mn9k";
$dbname_t = "namedays";
$conn = new mysqli($servername_t, $username_t, $password, $dbname_t);
mysqli_set_charset($conn, "utf8");

$request = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');
echo json_encode(handleRequest($conn, $request));
