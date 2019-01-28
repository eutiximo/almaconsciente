<?php

$context = Timber::get_context();

//Activar el modo sticky en el nav
$context["navSticky"] = true;
//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "archive search";
//Obtener el query con el que se hizo la busqueda.
$context['query_search'] = $query_search = get_search_query();

$context['posts'] = $posts = new Timber\PostQuery();
$context['pagination'] = $pagination = Timber::get_pagination();

$MC->renderSite("search.twig", $context);
?>