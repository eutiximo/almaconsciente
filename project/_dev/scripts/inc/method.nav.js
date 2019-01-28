/*
 *  Metodo para la parte del nav y controlar comportamiento para desktop y mobile
    - typeof = object
    
    - nav.Nav | typeof = object DOM | value = undefined
        Aqui se guarda el objeto del DOM referente al nav que se define en la funcion "init" definida mas adelante.
        
    - nav.isMobiel | typeof = boolean | value = false
        Variable de tipo bandera para saber si esta activa la modalidad para mobiles y usarla en condicionales alrededor del metodo.
        
    - nav.onDesktop | typeof = function | parameters = empty
        Metodo para activar la parte visual y interactividad del nav para version de escritorio.
        
    - nav.onMobile | typeof = function | parameters = empty
        Metodo para activar la parte visual e interactiva del nav para version de mobiles y tablets
        
    - nav.pipe | typeof = function | parameters = windowWidth
        Metodo que sirve como filtro para saber que version visual & interactiva del nav se debe mostrar y obtener metricas para saber cuando activarse segun la resoluvion de pantalla. Como parametro resive el ancho de la ventana en pixeles.
        
    - nav.init | typeof = function | parameters = empty
        Metodo que sirve para definir variables dentro de la clase y arrancar de manera controlada el nav.
 */

$g.nav = {
    Nav: undefined,
    
    isMobile: false,
    
    onDesktop: function () {
        this.Nav.removeClass("nav-mobile-ok open").removeAttr("style");
        this.Nav.off("click");
        
    },
    
    onMobile: function () {
        var self = this;
        
        self.Nav.addClass("nav-mobile-ok");
        
        self.Nav.on("click", function (event) {
            var isOpen = $(this).hasClass("open"),
                hasOffAction = event.target.hasAttribute("off-action");
            
            if (!hasOffAction) {
                if (isOpen) {
                    $(this).removeClass("open").animate({left: -260}, 500);
                } else {
                    $(this).addClass("open").animate({left: 0}, 500);
                }
            }
        });
        
    },
    
    pipe: function (windowWidth) {
        var self = this;
        
        if (windowWidth >= 1000 && self.isMobile) {
            self.isMobile = false;
            self.onDesktop();
            
        } else if (windowWidth < 1000 && !self.isMobile) {
            self.isMobile = true;
            self.onMobile();
        }
    },
    
    init: function () {
        var self = this;
        
        self.Nav = $("#main-nav");
        
        self.pipe(window.innerWidth);
        $(window).on("resize", function () { self.pipe(this.innerWidth) });
    }
};