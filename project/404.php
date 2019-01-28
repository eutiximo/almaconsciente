<?php

$context = Timber::get_context();

//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "page {$post->slug}";
//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "";
//Activar el modo sticky en el nav
$context["navSticky"] = true;

$MC->renderSite("404.twig", $context);

?>