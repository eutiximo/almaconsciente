<?php

$context = Timber::get_context();
$context["post"] = new Timber\PostQuery();
$view = array("index.twig");

Timber::render($view, $context);

?>