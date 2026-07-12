<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'nova_news';

function getDB()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    global $servername, $username, $password, $dbname;

    $conn = @new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $conn = new class {
            public $num_rows = 0;

            public function query($sql)
            {
                return new class {
                    public $num_rows = 0;

                    public function fetchColumn()
                    {
                        return 0;
                    }

                    public function fetch_assoc()
                    {
                        return null;
                    }
                };
            }

            public function prepare($sql)
            {
                return false;
            }
        };
    }

    return $conn;
}

$conn = getDB();