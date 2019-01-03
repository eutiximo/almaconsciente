<?php

//Definir contexto de Timber
$context = Timber::get_context();

//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "home";

//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "contactHome";

//Añadir al contexto la información de la "página INICIO"
$context["post"] = $post = new Timber\Post("inicio");

//Posts para la seccion de diapositivas
$context["slidePosts"] = $slidePosts = Timber::get_posts(array(
    "meta_query" => array(
        "relation" => "AND",
        array(
            "key" => "append_slide_home",
            "value" => 1
        )
    )
));

//Obtener las ultimas 3 publicaciones.
$context["lastPosts"] = $lastPosts = Timber::get_posts(array("posts_per_page" => 3));

//Obtener pagina de Contacto para explotar su contenido en la pagina Inicio.
$context["mail_status"] = $MC->sent_mail_status();
$getIdContactPage = get_page_by_path("contacto")->ID;
$context["argsContact"] = $argsContact = array(
    "enabled" => get_field("view_sec_contact", $getIdContactPage),
    "iframe_link" => get_field("iframe_link"),
    "content_contact" => get_field("content_contact", $getIdContactPage)
);

//Renderear página.
$template = "index.twig";
$MC->renderSite($template, $context);

?>  