<?php

//Visualizar caja de busqueda.
$context["prms"]["viewSearchBox"] = true;
//Visualizar las subcategorias.
$context["prms"]["viewSubcats"] = true;
//Visualizar los posts destacados.
$context["prms"]["viewGreatPosts"] = true;
//Obtener las subcategorias.
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);
//Obtener posts destacados.
$context["great_posts"] = $great_posts = $MC->get_great_posts($catprms);