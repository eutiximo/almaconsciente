/*
 *  Metodo para buscar en el home seccion de contacto y aplicar funcionalidad, en este caso al presionar el boton mostrar mapa, oculta el formulario y info de contacto y muestra el mapa que esta por detras, con una animación sencilla.
    - typeof = function | parameters = empty
 */

$g.contactHome = function () {
    var Elem = $("#contacto"),
        btnToggle = Elem.find(".toggleViewMap"),
        textToggle = btnToggle.data("text-toggle").split(','),
        isHide = false;

    btnToggle.on("click", function () {
        var SubElems = Elem.find(".overlay, .container");

        if (Elem.hasClass("hide")) {
            btnToggle.find("span").text(textToggle[0]);
            Elem.removeClass("hide").css("height", "auto");
            SubElems.animate({opacity: 1}, 500);

        } else {
            btnToggle.find("span").text(textToggle[1]);
            SubElems.animate({opacity: 0}, 500, function () {
                Elem.css("height", Elem.outerHeight()).addClass("hide");
            });
        }
    });
};