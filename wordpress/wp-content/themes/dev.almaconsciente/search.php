<?php

$context = Timber::get_context();

//Activar el modo sticky en el nav
$context["navSticky"] = true;
//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "archive search";

$context['query_search'] = $query_search = get_search_query();
$context['posts'] = $posts = new Timber\PostQuery();

//var_dump($title, $posts);

$MC->renderSite("search.twig", $context);
?>