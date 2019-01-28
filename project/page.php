<?php

//Definir contexto de Timber
$context = Timber::get_context();

//Obtener post de la pagina.
$context["post"] = $post = new TimberPost();
//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "page {$post->slug}";
//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "";
//Activar el modo sticky en el nav
$context["navSticky"] = true;

if ($post->slug === "inicio"):
    wp_redirect( $context["site"]->home_url );
    exit;
elseif ($post->slug === "contacto"):
    $tpl = "page.contacto.twig";
else:
    $tpl = "page.twig";
endif;

//Renderear página.
$MC->renderSite($tpl, $context);

?>