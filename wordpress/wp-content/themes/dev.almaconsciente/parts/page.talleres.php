<?php

//Visualizar caja de busqueda.
$context["prms"]["viewSearchBox"] = true;
//Visualizar las subcategorias.
$context["prms"]["viewSubcats"] = true;
//Visualizar caja de talleres imparidos en el momento.
$context["prms"]["viewCurrentWorkshops"] = true;
//Visualizar horarios del taller
$context["prms"]["viewScheduleWorkshop"] = true;
//Visualizar caja de compra de talleres
$context["prms"]["viewBuyWorkshop"] = $viewBuyWorkshop = get_field("sale_workshop", $post->ID);
$context["prms"]["viewFormWorkshopless"] = $viewFormWorkshopless = get_field("workshopless_form", $post->ID) && is_user_logged_in();
//Obtener el status del mail
$context["mail_status"] = $MC->sent_mail_status();

//Obtener las subcategorias.
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);

$context["current_workshops"] = $MC->get_current_workshops();