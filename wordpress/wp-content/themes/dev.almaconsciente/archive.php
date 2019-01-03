<?php
$pathSite = explode("/", $_SERVER['REQUEST_URI']);

//Definir contexto de Timber
$context = Timber::get_context();

//Obtener categoría activa
$context["catprms"] = $catprms = $MC->get_category_params();
//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "archive {$catprms->data_parent->slug}";
//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "";
//Activar el modo sticky en el nav
$context["navSticky"] = true;
//Definir parametros generales
$context["prms"] = array();

//Cargar Scripts según la sección del archivo.
if ($catprms->data_parent->slug === "blog"):
    require_once("parts/archive.blog.php");
elseif ($catprms->data_parent->slug === "tienda"):
    require_once("parts/archive.tienda.php");
elseif ($catprms->data_parent->slug === "talleres"):
    require_once("parts/archive.talleres.php");
else:
    require_once("parts/archive.blog.php");
endif;

//Añadir al contexto la información los post de la categoria "Blog"
global $paged;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$argsPosts = array( "paged" => $paged, "cat" => $catprms->cat_ID );

$context["posts"] = $posts = new Timber\PostQuery($argsPosts);

//Renderear página.
$MC->renderSite("archive.twig", $context);

?>