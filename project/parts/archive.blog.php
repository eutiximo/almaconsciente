<?php

//Definir archivo de include.
$context["tpl_part"] = "archive.blog.twig";
//Visualizar caja de busqueda:
$context["prms"]["viewSearchBox"] = true;
//Visualizar seccion de subcateogrias:
$context["prms"]["viewSubcats"] = true;
//Visualizar sección de artículos destacados:
$context["prms"]["viewGreatPosts"] = true;

//Obtener subcategorías
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);
//Obtener posts destacados
$context["great_posts"] = $great_posts = $MC->get_great_posts($catprms);

?>