<?php

//Ver caja de descarga si es por password
if (get_field("unlock_by_password", $post->ID)) {
    $logProduct = $_POST["password_product"] === get_field("password_unlock_product", $post->ID);
    $context["pass_fail"] = isset($_POST["password_product"]) && !$logProduct;
}
//Ver caja de descargas si fue por compra.
elseif ($context["current_user"]->isLogged) {
    $productOfUser = get_field("payment_products", "user_". $context["current_user"]->ID);
    $productOfUser = empty($productOfUser) ? array() : $productOfUser;
    foreach ($productOfUser as $obj) { if (intval($post->ID) === intval($obj["id_product"]) && $obj["unlock"]) $logProduct = true; }
    //array_filter($productOfUser, function ($obj) use ($post) { return intval($post->ID) === intval($obj["id_product"]) && $obj["unlock"]; });
}

//Obtener los productos protegidos si pasan las validaciones
if ($logProduct) {
    $getMediaAssets = get_field("media_assets", get_the_ID());
    $arrData = array("aside" => array(), "top" => array(), "bottom" => array());
    
    foreach ($getMediaAssets as $value) {
        $pos = $value["position_content"];
        array_push($arrData[$pos], $value);
    }
}

//Visualizar caja de descargas
$context["prms"]["viewDownloadBox"] = $logProduct;
//Visualizar caja de busqueda.
$context["prms"]["viewSearchBox"] = true;
//Visualizar las subcategorias.
$context["prms"]["viewSubcats"] = true;
//Visualizar caja de pagar producto.
$context["prms"]["showPaymentProductBox"] = true;
//Activar JS en los metaruns
$context["metajsrun"] .= "formatCurrency,";
//Obtener las subcategorias.
$context["subcats"] = $subcats = $MC->get_subcategories($catprms);
//Arreglo con los productos protegidos.
$context["protected_content"] = $arrData;
//Definir template
$tpl = "single.tienda.twig";