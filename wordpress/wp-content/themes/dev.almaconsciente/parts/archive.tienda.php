<?php

//Definir archivo de include.
$context["tpl_part"] = "archive.tienda.twig";
//Activar JS en los metaruns
$context["metajsrun"] .= "formatCurrency,";
//Visualizar caja de busqueda:
$context["prms"]["viewSearchBox"] = true;
//Visualizar caja de opciones para tienda.
$context["prms"]["viewShopOptions"] = true;
//Visualizar seccion de subcateogrias:
$context["prms"]["viewSubcats"] = true;
// Saber si se efectuo compra o se cancelo y mostrar en pantalla.
$context["payment_notification"] = $paymentNotification = isset($_GET["payment"]) ? $_GET["payment"] : false;

//Obtener subcategorías
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);

// Obtener los ID's de los productos comprados por el usuario.
if ($context["current_user"]->isLogged):
$context["products_of_user"] = $productOfUser = get_field("payment_products", "user_". $context["current_user"]->ID);

endif;

function getProductOfUser($idItem, $productsUser) {
    $elem = false;
    $productsUser = empty($productsUser) ? array() : $productsUser;
    foreach($productsUser as $value) {
        if ($value["id_product"] == $idItem && !$elem) {
            $elem = $value;
        }
    }    
    return $elem;
}