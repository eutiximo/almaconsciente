<?php

/*
 * Template Name: Contact Page
 */

//Definir contexto de Timber
$context = Timber::get_context();

//Obtener post de la pagina.
$context["post"] = $post = new TimberPost();
//Definir la clase de main -> esto sirve para las hojas de estilo.
$context["nameClass"] = "page {$post->slug}";
//Definir funciones a ejecutar en el JSRUN
$context["metajsrun"] = "contactHome,";
//Activar el modo sticky en el nav
$context["navSticky"] = true;
//Saber si se envio un correo correctamente.
$context["mail_status"] = $MC->sent_mail_status();

//Renderear página.
$MC->renderSite("page-contact.twig", $context);
