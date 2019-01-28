<?php

//Definir contexto de Timber
$context = Timber::get_context();

//Cargar post activo
$context["post"] = $post = Timber::query_post();

//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "single";

//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "";

//Activar el modo sticky en el nav
$context["navSticky"] = true;

//Definir parametros generales
$context["prms"] = array();

// Definir parametros de la cateogoría activa.
$context["catprms"] = $catprms = $MC->get_category_params($post->ID);

// Filtrar configuraciones por categoría del post.
if ($catprms->data_parent->template === "blog"):
    require_once("parts/page.blog.php");
elseif ($catprms->data_parent->template === "tienda"):
    require_once("parts/page.tienda.php");
elseif ($catprms->data_parent->template === "talleres"):
    require_once("parts/page.talleres.php");
else:
    require_once("parts/page.blog.php");
endif;

//Renderear página.
$MC->renderSite($tpl ? $tpl : "single.twig", $context);

?>