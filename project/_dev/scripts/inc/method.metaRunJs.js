/*
 *  Metodo para obtener instancia de una funcion especifica y ejecutarla.
 */
$g.metaJsRun = function() {
    var getMetaTag = $("meta[name='jsrun']"),
        getKeys = getMetaTag.attr("content").split(",");

    getKeys.forEach(function (value) {
        if ($g.hasOwnProperty(value)) {
            $g[value]();
        }
    });
};