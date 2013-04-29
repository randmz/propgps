<?php 
/**
 * File to handle all API requests
 * Accepts GET and POST
 *
 * Each request will be identified by TAG
 * Response will be JSON data
 
  /**
 * check for POST request
 */
 
$user_existe= "Ya existe el usuario";
$prop_existe= "La propiedad ya está ingresada en nuestra base de datos.";
$incorrect_mail_pass = "Email o password incorrectos!";
$error_al_registrar = "Ocurrió un error al registrarse";
$invalid_request = "Invalid Request";
$access_denied = "Access Denied";
$error_al_agregar_propiedad = "Error al agregar la propiedad";
 
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // get tag
    $tag = $_POST['tag'];
 
    // include db handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();
 
    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
 
    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // check for user
        $user = $db->getUserByEmailAndPassword($email, $password);
        if ($user != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["telefono"] = $user["telefono"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = $incorrect_mail_pass;
            echo json_encode($response);
        }
    } else if ($tag == 'register') {
        // Request type is Register new user
        $name = $_POST['name'];
        $email = $_POST['email'];
  	$telefono = $_POST['telefono'];
        $password = $_POST['password'];
 
        // check if user is already existed
        if ($db->isUserExisted($email)) {
            // user is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = $user_existe;
            echo json_encode($response);
        } else {
            // store user
            $user = $db->storeUser($name, $email, $telefono, $password);
            if ($user) {
                // user stored successfully
                $response["success"] = 1;
                $response["uid"] = $user["unique_id"];
                $response["user"]["name"] = $user["name"];
                $response["user"]["email"] = $user["email"];
                $response["user"]["created_at"] = $user["created_at"];
                $response["user"]["updated_at"] = $user["updated_at"];
                echo json_encode($response);
            } else {
                // user failed to store
                $response["error"] = 1;
                $response["error_msg"] = $error_al_registrar;
                echo json_encode($response);
            }
        }
    } else if($tag == 'agregar_propiedad') {
	//agregando la propiedad en la base de datos
        $userid = $_POST['userid'];
        $direccion = $_POST['direccion'];
        $dormitorios = $_POST['dormitorios'];
        $banios = $_POST['banios'];
        $descripcion = $_POST['descripcion'];
        $tipo = $_POST['tipo'];
		$precio = $_POST['precio'];
		
		$direccion = $direccion.', Chile';
		
		$direccion = preg_replace('/ /','+',$direccion);
		
		//sacando las coordenadas de la dirección
		$geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$direccion.'&sensor=false');
		$output= json_decode($geocode);

		$lat = $output->results[0]->geometry->location->lat;
		$lon = $output->results[0]->geometry->location->lng;
 
		// VER SI EXISTE LA PROPIEDAD Y AGREGARLA DESPUÉS A LA BASE DE DATOS LOCAL 
		
        // check if user is already existed
        if ($db->isPropExisted($lat, $lon)) {
            // user is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = $prop_existe;
            echo json_encode($response);
        } else {
            // store user
            $user = $db->storeProp($userid, $direccion, $lat, $lon, $dormitorios, $banios, $descripcion, $tipo, $precio);
            if ($user) {
                // prop stored successfully
				
				if(!is_dir('uploads/'.$userid.'/'.$user["id_propiedades"])){
					mkdir('uploads/'.$userid.'/'.$user["id_propiedades"]);
					chmod('uploads/'.$userid.'/'.$user["id_propiedades"], 0777);
				}
				
                $response["success"] = 1;
				$response["id_prop"] = $user;
                echo json_encode($response);
            } else {
                // error
                $response["error"] = 1;
                $response["error_msg"] = $error_al_agregar_propiedad;
                echo json_encode($response);
            }
        }
	} else if($tag == 'get_coordenadas') {
		
		// get coords
		$result = $db->getCoordenadas();

		print(json_encode($result));

	} else if($tag == 'get_mis_propiedades') {

		// get mis propiedades
		$userid = $_POST['userid'];
		
		$result = $db->getMisPropiedades($userid);

		print(json_encode($result));

	} else if($tag == 'fin_propiedad') {

		// fin propiedad
		$prop_id = $_POST['prop_id'];
		
		$result = $db->FinPropiedad($prop_id);
		
		if($result != false){
			$response["success"] = 1;
			print(json_encode($response));
		}else{
			$response["error"] = 1;
			print(json_encode($response));
		}
		
	} else if($tag == 'get_phone') {

		// fin propiedad
		$userid = $_POST['userid'];
		
		$user = $db->getPhone($userid);
		
		if ($user != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["telefono"] = $user["telefono"];
			$response["email"] = $user["email"];
			$response["nombre"] = $user["name"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            echo json_encode($response);
        }
		
	} else {
        echo $invalid_request;
    }
} else {
    echo $access_denied;
}
?>
