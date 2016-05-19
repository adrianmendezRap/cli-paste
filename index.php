<?php
// INICIO FUNCIONES Y VARIABLES //
function limpia($txt) {
                if (!is_array($txt)) {
                    $txt = htmlspecialchars(strip_tags(stripslashes($txt)));
                    return strtr($txt, array(
                                  "\0" => "",
                                  "'"  => "",
                                  "\"" => "",
                                  "\\" => "",
                                  "<"  => "",
                                  ">"  => "",	
                                  "."  => "",
                                  "/" => "",
                                  "&" => "",
                    ));
                }
}

function RandomName($length = 4) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$urlhost = "http://".$_SERVER['HTTP_HOST'];
$appname = "paste";
// FIN FUNCIONES Y VARIABLES //

if (!empty($_POST) && isset($_POST["paste"]) && $_POST["paste"] != ""){ // Revisamos si existe entrada de datos por POST y si es por "paste"
	$check = TRUE;
	while ($check){ // Bucle para la busqueda de un nuevo nombre
		$tmpname = RandomName(); // Generamos un nombre aleatorio de 4 caracteres, editable al numero deseado ( siempre > 3 )
		if (file_exists('txt/'.$tmpname)){ //Revisamos que el nombre generado no exista
			$check = TRUE;
			$tmpname = "";
		}else{ // Si no existe creamos el fichero y guardamos el contenido
			$check = FALSE;
			$file = fopen('txt/'.$tmpname, "w"); // Creamos un nuevo archivo con el nombre generado
			fwrite($file, $_POST["paste"]);
			fclose($file);
			$ip = fopen('ip.php', "a+"); // Guardamos la IP de origen y la fecha/hora por motivos de seguridad
			$data = $tmpname." - ".$_SERVER['REMOTE_ADDR']." - ".date("D M j G:i:s T Y")."\n";
			fwrite($ip, $data);
			fclose($ip);
		}
	}
	echo $urlhost."/".$tmpname ; // Enviamos de vuelta la url de acceso al archivo
	echo "\n";
	die();
}elseif(!empty($_GET)&& isset($_GET["d"]) && $_GET["d"] != "" ){ // Revisamos si hay peticion de datos por GET y si es por "d"
	if (!file_exists('txt/'.limpia($_GET["d"]))){ // Revisamos que el archivo de la peticion exista
		echo "File ".limpia($_GET["d"])." not found";
		die();
	}elseif(isset($_GET["l"]) && $_GET["l"] != ""){ // Revisamos si hay peticion para resaltar la sintaxis
		require_once('PHPygments.php');
		$file = file_get_contents('txt/'.limpia($_GET["d"]));
		$result = PHPygments::render($file, limpia($_GET["l"]));
		if ($result["code"] == ""){ // En caso de que no exista o no sea soportada la peticion se informa y se envia el texto sin formatear
			header('Content-type: text/plain; charset=UTF-8');
			echo "Filetype ".limpia($_GET["l"])." not supported \n";
			$file = file_get_contents('txt/'.limpia($_GET["d"]));
			echo $file;
			die();
		}else{ // Si todo funciona correctamente se envia la informacion
			print '<link href="styles/default.css" media="all" rel="stylesheet" type="text/css"/>';
			echo $result["code"];
			die();
		}			
	}else{ // Si no se pide formatear la salida y el archivo existe se envia en formato de texto plano
		header('Content-type: text/plain; charset=UTF-8');
		$file = file_get_contents('txt/'.limpia($_GET["d"]));
		echo $file;
		die();
	}

}else{ // Si no hay entrada y peticion de datos imprimimos la pagina con la informacion
?>
<html>
	<head>
		<title>paste</title>
		<style> a { text-decoration: none } </style>
	</head>
	<body>
		<pre>
<? echo strtolower($appname); ?>(1)                          <? echo strtoupper($appname); ?>                          <? echo strtolower($appname); ?>(1)

NAME
    <? echo strtolower($appname); ?>: command line pastebin. <a href="http://sprunge.us/" target="_blank">sprunge</a> PHP clone

SYNOPSIS
    &lt;command&gt; | curl -F 'paste=&lt;-' <? echo $urlhost; ?>

DESCRIPTION
    add <a href='http://pygments.org/docs/lexers/'>&amp;l&#61;&lt;lang&gt;</a> to resulting url for line numbers and syntax highlighting
    use <a href='data:text/html,<form action="<? echo $urlhost; ?>" method="POST" accept-charset="UTF-8"><textarea name="paste" cols="80" rows="24"></textarea><br><button type="submit">paste</button></form>'>this form</a> to paste from a browser

EXAMPLES
    ~$ cat bin/ching | curl -F 'paste=&lt;-' <? echo $urlhost; ?> 
       <? echo $urlhost; ?>/aXZI
    ~$ firefox <? echo $urlhost; ?>/aXZI&l=py

SEE ALSO
    http://github.com/rupa/sprunge (original py program)
    (github soon)

		</pre>
	</body>
</html>
<?php
die();
}
?>
