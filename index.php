<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar y reservar vuelos y hoteles</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
</head>

<?php 

$json_data = file_get_contents('reservations.json');
$packages = json_decode($json_data, true);

class Package {
    public $hotel;
    public $city;
    public $country;
    public $date;
    public $nights;

    public function __construct($hotel, $city, $country, $date, $nights) {
        $this->hotel = $hotel;
        $this->city = $city;
        $this->country = $country;
        $this->date = $date;
        $this->nights = $nights;
    }

    public function show_info() {
        echo 'Hotel: ' . $this-> hotel . '<br>';
        echo 'Ciudad: ' . $this-> city . '<br>';
        echo 'País: ' . $this-> country . '<br>';

        $this->es_date($this->date);
        
        echo 'Fecha: ' . $this-> date . '<br>';
        echo 'Noches: ' . $this-> nights . '<br>';
    }

    private function es_date() {
        list($year, $month, $day) = explode("-", $this->date);
        $this->date = "$day-$month-$year";
    }
}

function compare_info($packages, $origin, $destination, $date, $nights) {
    $matching_package = [];
    if ($packages) {
        foreach ($packages as $package) {
            if (strtolower($package['origin']) == strtolower($origin) && 
            strtolower($package['destination']) == strtolower($destination) && 
            in_array($date, $package['dates']))  {
                $matching_package[] = $package;
            }
        }
        if (!empty($matching_package)) {
            $first_package = $matching_package[0];
            $new_package = create_package($first_package, $date, $nights);
            return $new_package;
        }
    }
}

function create_package($pkg, $date, $nights) {
    $new_package = new Package($pkg['hotel'], $pkg['destination'], $pkg['country'], $date, $nights);
    return $new_package;
}

?>

<body>
    <div class="container">
        <a href="index.php">Inicio</a>
        <h1>Buscar y reservar vuelos y hoteles</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
            <label for="origin">Origen:</label>
            <input type="text" id="origin" name="origin" required><br>
            <label for="destination">Destino:</label>
            <input type="text" id="destination" name="destination" required><br>
            <label for="date">Fecha:</label>
            <input type="date" id="date" name="date"><br>
            <label for="nights">Noches de hotel:</label>
            <input type="text" id="nights" name="nights" required><br>
            <input type="submit" name="search" value="Buscar">
        </form>
    
        <div id="search-results" class="my-3">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET)) {
                if (isset($_GET['search'])) {
                    $origin = isset($_GET['origin']) ? $_GET['origin'] : null;
                    $destination = isset($_GET['destination']) ? $_GET['destination'] : null;
                    $date = isset($_GET['date']) ? $_GET['date'] : null;
                    $nights = isset($_GET['nights']) ? $_GET['nights'] : null;

                    $new_package = compare_info($packages, $origin, $destination, $date, $nights);

                    if (!empty($new_package)) {
                        $new_package->show_info();
                    ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
                            <input type="submit" name="reserve" value="Reservar" class="btn btn-success my-2">
                        </form>
                    <?php
                    } else {
                        $message = 'No se encontraron coincidencias.';
                    }
                } else if (isset($_GET['reserve'])) {
                    $message = "El paquete ha sido reservado.";
                }
            }
            ?>
            <?php if (isset($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
<script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
</html>
