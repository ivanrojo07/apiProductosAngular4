<?php 
require_once 'vendor/autoload.php';

$app = new \Slim\Slim();
function connect_db() {
	$server = 'localhost'; // this may be an ip address instead
	$user = 'root';
	$pass = 'ivanrojo07@';
	$database = 'curso_angular4';
	$connection = new mysqli($server, $user, $pass, $database);

	return $connection;
}
$db = connect_db();
//cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app){
	echo "Hola Mundo desde Slim PHP";
	var_dump($db);
});

$app->post('/productos', function() use($app,$db){
	$json= $app->request->post('json');
	$data = json_decode($json, true);
	// var_dump($json);
	// var_dump($data);
	if(!isset($data['nombre'])){
		$data['nombre']=null;
	}
	if(!isset($data['description'])){
		$data['description']=null;
	}
	if(!isset($data['precio'])){
		$data['precio']=null;
	}
	if(!isset($data['imagen'])){
		$data['imagen']=null;
	}
	$query = "INSERT INTO productos VALUES(NULL, '{$data['nombre']}', '{$data['description']}', '{$data['precio']}', '{$data['imagen']}');";
	var_dump($query);
	$insert = $db->query($query);
	var_dump($insert);
	if ($insert) {
		# code...
		$result = array('status'=> 'success',
			'code' => 200,
			'message' => 'Producto creado correctamente');
	}
	else{
		$result = array('status'=> 'error',
			'code' => 404,
			'message' => 'Producto 	NO SE A creado correctamente');
	}	
	echo json_encode($result);
});
$app->get('/productos', function() use($app,$db){
	$sql="SELECT * FROM productos ORDER BY id DESC;";
	$consulta = $db->query($sql);
	$productos = array();
	// var_dump($consulta);
	if ($consulta->num_rows > 0) {
		# code...
		while ($producto = $consulta->fetch_assoc()) {
			# code...
			// $productos= "{ id: {$columna['id']}, nombre: {$columna['nombre']}, description: {$columna['description']}, precio: {$columna['precio']}, imagen: {$columna['imagen']} }";
			// echo json_encode($productos);
			// var_dump($productos);
			$productos[] = $producto;
			$result = array('status'=> 'success',
			'code' => 200,
			'data' => $productos);
		}
	}
	else{
		$result = array('status'=> 'error',
			'code' => 404,
			'message' => 'No Hay productos');
	}
	echo json_encode($result);
	// echo json_encode($productos);

});
$app->get('/producto/:id', function($id) use($app,$db){
	$sql = "SELECT * FROM productos WHERE id = {$id}";
	$consulta = $db->query($sql);
	if($consulta->num_rows ==1){
		$producto = $consulta->fetch_all();
		$result = array('status'=> 'success',
			'code' => 200,
			'data' => $producto);
	}
	else{
		$result = array('status'=> 'error',
			'code' => 404,
			'message' => 'No Hay productos');
	}
	echo json_encode($result);
});
$app->get('/producto/eliminar/:id', function($id) use($app, $db){
	$sql = "DELETE FROM productos WHERE id = {$id}";
	$delete = $db->query($sql);
	var_dump($delete);
	if($delete){
		$result = array('status'=> 'success',
			'code' => 200,
			'message' => 'Producto eliminado correctamente');
	}
	else{
		$result = array('status'=> 'error',
			'code' => 404,
			'message' => 'Producto 	NO SE A ELIMINADO');
	}	
	echo json_encode($result);
});
$app->post('/productos/actualizar/:id', function($id) use($app, $db){
	$json= $app->request->post('json');
	$data = json_decode($json, true);
	// var_dump($json);
	// var_dump($data);
	$sql = "UPDATE productos SET ";
	if($data['nombre']){
		$sql .= " nombre = '{$data['nombre']}'";
	}
	else if($data['description']){
		$sql .= ", description = '{$data['description']}'";
	}
	else if($data['precio']){
		$sql .=", precio = '{$data['precio']}'";
	}
	else if($data['imagen']){
		$sql .= ", imagen = '{$data['imagen']}'";
	}
	$sql .= " WHERE id = {$id} ;";
	//var_dump($sql);
	$update = $db->query($sql);
	if ($update) {
		# code...
		$result = array('status'=> 'success',
			'code' => 200,
			'message' => 'Producto eliminado correctamente');
	}
	else{
		$result = array('status'=> 'error',
			'code' => 404,
			'message' => 'Producto 	NO SE A ELIMINADO');
	}	
	echo json_encode($result);
	// $insert = $db->query($query);
	// var_dump($insert);
});
// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];
		var_dump($file);

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});

// $app->post('/upload-file', function() use($db, $app){
// 	// var_dump($_FILES);
// 	if (isset($_FILES['uploads'])) {
// 		// echo "LLEGAN LOS DATOS";
// 		$piramideUploader = new PiramideUploader();
// 		$upload = $piramideUploader->upload('image', 'uploads', 'uploads', array('image/jpeg', 'image/png', 'image/gif'));
// 		$file = $piramideUploader->getInfoFile();
// 		$file_name = $file['complete_name'];
// 		var_dump($file);
// 	}else{
// 		$result = array('status'=> 'error',
// 			'code' => 404,
// 			'message' => 'Archivo no enviado');
// 	}
// 	//echo json_encode($result);
// });
$app->run();
 ?> 