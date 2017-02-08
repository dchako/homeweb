<?php
//cambiar el contenido entre comillas por la dirección de su dominio -A- la cual se desea enviar el mail
$correo="info@mailclick.com.ar";
$email_subject = "Contacto desde el sitio web";
//se incluye la clase php-captcha

//Si no han pasado alguna variable por POST o no valida el captcha -> error


$body="";
$body = "Detalles del formulario de contacto:\n\n";
$body .= "Nombre: " . $_POST['nombre'] . "\n";
$body .= "Apellido: " . $_POST['email'] . "\n";
$body .= "E-mail: " . $_POST['url'] . "\n";
$body .= "Teléfono: " . $_POST['telefono'] . "\n";
$body .= "Comentarios: " . $_POST['mensaje'] . "\n\n";

    //estos campos no se envian al cuerpo del mensaje
$hide = array("successfully", "errorOcurred", "subject", "captcha", "submit"); 

    //para cada campo enviado del formulario - si no son especiales se agregan al cuerpo
foreach ($_POST as $key => $value) if (!in_array($key, $hide)) $body .= $key . ": " . $value. "\n";

    //se envia el mail
$header = 'From: ' . $_POST['email']. " \r\n";
$header .= "X-Mailer: PHP/" . phpversion() . " \r\n";
$header .= "Mime-Version: 1.0 \r\n";
$header .= "Content-Type: text/plain";

mail($correo,$email_subject,$body,$header);


    //se direcciona a la pagina de agradecimiento
header("Location: ".$_POST['successfully']);
echo "¡El formulario se ha enviado con éxito!";
	

?>
