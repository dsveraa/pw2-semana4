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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>

<?php 

$json_data = file_get_contents('reservations.json');
$packages = json_decode($json_data, true);

$origin = $destination = $date = $nights = null;

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET)) {
    if (isset($_GET['search'])) {
        $origin = isset($_GET['origin']) ? $_GET['origin'] : null;
        $destination = isset($_GET['destination']) ? $_GET['destination'] : null;
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        $nights = isset($_GET['nights']) ? $_GET['nights'] : null;
    }
}

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
        echo '<div class="my-3">';
        echo '<b>Hotel</b>: ' . $this-> hotel . '<br>';
        echo '<b>Ciudad</b>: ' . $this-> city . '<br>';
        echo '<b>País</b>: ' . $this-> country . '<br>';

        $this->es_date($this->date);
        
        echo '<b>Fecha</b>: ' . $this-> date . '<br>';
        echo '<b>Noches</b>: ' . $this-> nights . '<br>';
        echo '</div>';
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

function add_suggestions($packages, $date, $nights, $destination) {
    $random_index = array_rand($packages);
    $pkg = $packages[$random_index];
    
    if ($pkg['destination'] != $destination) {
        $suggestion = new Package($pkg['hotel'], $pkg['destination'], $pkg['country'], $date, $nights);
    } else {
        add_suggestions($packages, $date, $nights, $destination);
    }
    return $suggestion;
}

?>

<body>
    <div class="bg-body-secondary">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-body-secodary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="index.php">Inicio</a>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Notificaciones <i class="fas fa-bell"></i>
                                <span class="badge bg-danger" id="count-label"><?php
                                    if (isset($_GET['search'])) {
                                        echo '+1';
                                    }
                                    ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right p-2" id="notificationDropdown" aria-labelledby="notificationDropdown">
                                <?php
                                if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET)) {
                                    if (isset($_GET['search'])) {
                                        $suggestion = add_suggestions($packages, $date, $nights, $destination);
                                        echo '¿Qué tal unas vacaciones alojando en el hotel <b>'. $suggestion->hotel . '</b> por <b>'. $nights .' noches</b> en la ciudad de <b>' . $suggestion->city . '</b>?';
                                    } else {
                                        echo 'Sin notificaciones';
                                    }
                                } else {
                                    echo 'Sin notificaciones';
                                }
                                ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>    
    <div class="container my-5">
        <?php if (!isset($_GET['search']) and !isset($_GET['reserve'])): ?>
        <h1>Buscar y reservar vuelos y hoteles</h1>
        <h5 class="text-primary">Santiago, Buenos Aires, Lima o Miami entre el 01 hasta el 05 de octubre.</h5>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" class="my-5">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="origin" class="form-label">Origen:</label>
                    <input type="text" id="origin" name="origin" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="destination" class="form-label">Destino:</label>
                    <input type="text" id="destination" name="destination" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">Fecha:</label>
                    <input type="date" id="date" name="date" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="nights" class="form-label">Noches de hotel:</label>
                    <input type="text" id="nights" name="nights" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="search" class="btn btn-primary">Buscar</button>
        </form>
        <?php 
        endif; 
        ?>
    
        <div id="search-results" class="my-3">
            <?php
            if (isset($_GET['search'])) {
                $new_package = compare_info($packages, $origin, $destination, $date, $nights);

                echo '<h1>Resultado de la búsqueda</h1>';

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
                $message = "El paquete ha sido reservado para la fecha seleccionada.";
            }
            
            ?>
            <?php if (isset($message)): ?>
                <h5><?php echo '<div class="text-success">' . $message . '</h5>'?>
            <?php endif; ?>
        </div>
    </div>
</body>
<script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
<script src="./script.js"></script>
</html>
