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
$acf_slideContent = get_field('slide_content', $post->ID);
$slidesNotPost = $acf_slideContent ? $acf_slideContent : array();
$slidesArr = array();
$slidePosts = Timber::get_posts(array(
    "orderby" => "modified",
    "meta_query" => array(
        "relation" => "AND",
        array(
            "key" => "append_slide_home",
            "value" => 1
        )
    )
));
foreach ($slidePosts as $slide) {
    $getTextStyes = get_field("style_text_slide", $slide->ID);
    $data = array(
        "title" => $slide->post_title,
        "content" => $slide->post_content,
        "excerpt" => $slide->post_excerpt,
        "bg_image" => get_field("img_slide_bg", $slide->ID),
        "text_style" => "color:{$getTextStyes['color']};{$getTextStyes['add_css']}",
        "link" => $slide->link
    );
    array_push($slidesArr, $data);
}
foreach ($slidesNotPost as $slide) {
    $order = intval($slide['order']);
    $data = array(
        "title" => $slide['title'],
        "content" => $slide['summary'],
        "excerpt" => "",
        "bg_image" => $slide['add_bg_image'],
        "text_style" => "color:{$slide['color_text']};{$slide['add_css']}",
        "link" => ($slide['add_link'] != '' ? $slide['add_link'] : false)
    );

    if ($order) {
        array_splice($slidesArr, ($order - 1), 0, array($data));
    } else {
        array_push($slidesArr, $data);
    }
}
$context["slidePosts"] = $slidesArr;

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