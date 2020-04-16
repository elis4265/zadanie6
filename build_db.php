<?php
        $servername_t = "localhost";
        $username_t = "xondreakova";
        $password = "h7g3Mn9k";
        $dbname_t = "namedays";
        $conn = new mysqli($servername_t, $username_t, $password, $dbname_t);
        mysqli_set_charset($conn, "utf8");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

$data = json_decode(file_get_contents("/home/xondreakova/public_html/zadanie6/nameday.json"), true);

        foreach($data as $dayData) {
            $day = $dayData["den"];
            foreach ($dayData as $key => $value) {
                if ($key == "den") continue;


                if ($key == "SKsviatky" || $key == "CZsviatky") {
                    if ($key == "SKsviatky") {
                        $country = "SK";
                    } else {
                        $country = "CZ";
                    }
                    $q = "INSERT INTO holidays (country, name, day) VALUES ('$country', '$value', '$day')";
                    //echo $q .'\n';
                    if (!$conn->query($q)) {
                        echo $conn->error;
                    }
                    continue;
                }
                if ($key == "SKdni") {
                    $country = "SK";
                    $q = "INSERT INTO specialdays (country, name, day) VALUES ('$country', '$value', '$day')";
                    //echo $q .'\n';
                    if (!$conn->query($q)) {
                        echo $conn->error;
                    }
                    continue;
                } 
                $country = $key;
                if ($country == "SK" && array_key_exists("SKd", $dayData)) {
                    continue;
                }
                if ($country == "SKd") {
                    $country = "SK";
                }

                $names = explode(", ", $value);
                foreach ($names as $name) {
                    $q = "INSERT INTO namedays (country, name, day) VALUES ('$country', '$name', '$day')";
                    if (!$conn->query($q)) {
                        echo $conn->error;
                    }
                }
            }
        }
?>