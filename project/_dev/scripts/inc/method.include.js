/*
 *  Metodo para incrustar html de manera asincrona a un elemento especificando ruta desde atributo del DOM
    typeof = function | parameters = empty
 */
$g.include = function () {
    var Elem = $("[include]");

    Elem.each(function (i, elem) {
        var currentElem = $(this),
            getSrc = currentElem.attr('include');

        getSrc = getSrc === "" ? "/undefined" : getSrc;

        $.get(getSrc, function (response) {
            currentElem.html(response);
        }, 'text').fail(function () {
            currentElem.html("<i style='display:block;text-align:center;font-size:0.7rem'>Error load include</i>").css({"border": "1px solid red", "color": "red"});
        });
    });
};