<?php

//Definir archivo de include.
$context["tpl_part"] = "archive.talleres.twig";
//Visualizar caja de busqueda:
$context["prms"]["viewSearchBox"] = true;
//Visualizar seccion de subcateogrias:
$context["prms"]["viewSubcats"] = true;
//Visualizar caja de talleres imparidos en el momento.
$context["prms"]["viewCurrentWorkshops"] = true;

//Obtener subcategorías
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);

//obtener talleres que se estan impartiendo en la zona horaria en ciudad de México.
$context["current_workshops"] = $MC->get_current_workshops();