/*
 *  Metodo para generar diapositivas.
    typeof = object
    
    owl.sets | typeof = object
    - contiene las diferentes configuraciones para mostrar un slide de alguna manera.
    
    owl.model | typeof = function | parameters = Elem
    - Metodo que recibe como parametro el elemento del DOM al cual va aplicar el slide
      extrae nommbre del "set" y otras caracteristicas.
      
    owl.init | typeof = function | parameters = null
    - Metodo cuya funcion es iterar sobre todo el DOM y buscar los elementos con el atributo "owl" para aplicar slide llamando al metodo "model" pasando el elemento iterado.
 */

$g.owl = {
    sets: {
        default: {
            items: 1,
            loop: true,
            nav: true,
            dots: true,
            navText: ["<span></span>", "<span></span>"],
            autoplay: true,
            autoplayHoverPause: true
        },
        set2: {
            items: 1,
            loop: false,
            nav: false,
            dots: true,
            autoHeight: true
        }
    },

    model: function (Elem) {
        var self = this,
            getSet = Elem.attr("owl") || "default",
            getTarget = Elem.attr("owl-target") || "self",
            hasFitContainer = Elem[0].hasAttribute("fit-content"),
            set = self.sets[getSet] || {};

        if (hasFitContainer) {
            let Items = Elem.find(".item");

            Items.css("height", window.innerHeight);
            $(window).on("resize", function () { Items.css("height", window.innerHeight); })
        }

        if (getTarget === "self") {
            Elem.owlCarousel(set);
        } else {
            Elem.find(getTarget).owlCarousel(set);
        }
    },

    init: function () {
        var self = this,
            Elems = $("[owl]");
        
        if (Elems.length) Elems.each(function (i, elem) { self.model($(this)); });
    }
};