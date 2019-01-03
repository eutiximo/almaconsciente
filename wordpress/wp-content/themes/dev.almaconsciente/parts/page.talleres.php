<?php

//Visualizar caja de busqueda.
$context["prms"]["viewSearchBox"] = true;
//Visualizar las subcategorias.
$context["prms"]["viewSubcats"] = true;
//Visualizar caja de talleres imparidos en el momento.
$context["prms"]["viewCurrentWorkshops"] = true;
//Visualizar horarios del taller
$context["prms"]["viewScheduleWorkshop"] = true;

//Obtener las subcategorias.
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);

$context["current_workshops"] = $MC->get_current_workshops();