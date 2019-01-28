var console, document, window, $g;

(function () {
    "use strict";
    
    $g = {
        page: {}
    };
    
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
    $g.formatCurrency = function () {
        var Elems = $("[format-currency]");
        
        Elems.each(function (index, value) {
            var getText = $(this).text().split("."),
                getPxPositionCents = $(this).attr("px-cents") || -5;
            
            $(this).html(`${getText[0]}.<sup style="position:relative;top:${getPxPositionCents}px">${(getText[1] || "00")}</sup>`);
        });
    };
    $g.collapsePanel = function () {
            var Elems = $("[collapse-p]");
            
            Elems.find("[main-panel]").hide();
            Elems.find("[main-panel='in']").show();
            
            Elems.on("click", "[btn-collapse-p]", function () {
                var self = $(this),
                    getTarget = $(this).parent().find("[main-panel]");
                
                getTarget.slideToggle(function () {
                    let getClass = self.find(".fa").attr("class");
                    
                    if (getTarget.is(":visible")) {
                        self.find(".fa").attr("class", getClass.replace("-down", "-up"));
                    } else {
                        self.find(".fa").attr("class", getClass.replace("-up", "-down"));
                    }
                });
                
                return false;
            });
        };
    $g.notify = {
        actives: [],
        defaultDelayIn: 0,
        defaultDelayOut: 2500,
        defaultMode: 'minibox',
        idGen: new Date().getTime(),
        
        minibox: function (prms) {
            var self = this;
            
            prms.Element.animate({steps: 150}, {
                step: function (now) { $(this).css({"transform": `translate(-50%, -${now}%)`}); },
                duration: 500
                
            }).delay(prms.delayOut).animate({steps: 0}, {
                step: function (now) { $(this).css({"transform": `translate(-50%, -${now}%)`}); },
                complete: function () {
                    $(this).remove();
                    self.actives.splice(self.actives.indexOf(prms.id));
                }
            });
        },
        
        fullscreen: function (prms) {
            var self = this;
            
            prms.Element.fadeIn().delay(prms.delayOut).fadeOut(function () {
                $(this).remove();
                self.actives.splice(self.actives.indexOf(prms.id), 1);
            });
        },
        
        Pipe: function (prms) {
            if (!this.actives.includes(prms.id) && prms.message && prms.message !== "")  {
                prms.Element = $(`<div id="notify-box" class="${prms.mode} ${prms.type}">${prms.message}</div>`);
                prms.Element.appendTo("body");
                
                this[prms.mode](prms);
                this.actives.push(prms.id);
            }
        },
            
        Run: function (elem) {
            var self = this,
                getDelayIn = +$(elem).attr("delay-in"),
                getDelayOut = +$(elem).attr("delay-out"),
                getType = $(elem).attr("type"),
                getMode = $(elem).attr("mode"),
                getMessage = $(elem).attr("message") || $(elem).html(),
                id = self.idGen = self.idGen + 1,
                Prms = {};
            
            getDelayIn = isNaN(getDelayIn) ? self.defaultDelayIn : getDelayIn * 1000;
            
            Prms.id = id;
            Prms.type = getType;
            Prms.message = getMessage;
            Prms.delayOut = isNaN(getDelayOut) ? self.defaultDelayOut : getDelayOut * 1000;
            Prms.mode = getMode && self.hasOwnProperty(getMode) ? getMode : self.defaultMode;
            
            setTimeout(function () { self.Pipe(Prms); }, getDelayIn);
            
            if (elem.tagName === "NOTIFY") { $(elem).remove(); }
        },
        
        Init: function () {
            var self = this,
                Elems = $("notify");
            
            Elems.each(function () {
                self.Run(this);
            });
        }
    };
    $g.ipopover = function () {
        var Elems = $(".ipopover"),
            positionModes = ["top-box", "right-box", "bottom-box", "left-box"];
    
        function check_position(elem) {
            var elemRect = elem.getBoundingClientRect(),
                scrollTop = $(window).scrollTop(),
                scrollLeft = $(window).scrollLeft(),
                thePosition = positionModes[0];
    
            if (elemRect.top < scrollTop) {
                thePosition = positionModes[2];
            }
            if (elemRect.left < scrollLeft) {
                thePosition = positionModes[1];
    
            } else if (elemRect.left + elemRect.width > scrollLeft + $(window).width()) {
                thePosition = positionModes[3];
            }
            return thePosition;
        }
    
        Elems.hover(
            function () {
                var elem = $(this).find(".popover-box");
                elem.fadeIn(200).addClass(check_position(elem[0]));
            },
            function () {
                var elem = $(this).find(".popover-box"),
                    getClassPosition = elem.attr("class").split(" ");
    
                getClassPosition = getClassPosition.filter((item) => { return positionModes.indexOf(item) >= 0; })[0];
                elem.fadeOut(200, function () { $(this).removeClass(getClassPosition); });
            }
        );
    }
    $g.footer_always_bottom = function () {
            
        function adapting_rects () {
            var Footer = document.querySelector("#main-footer"),
                Main = document.querySelector("body > main > *:last-child"),
                FooterRect,
                MainRect;
    
            //Reset height Main and remove Style attribute to Footer.
            Footer.removeAttribute("style");
            Main.style.height = "auto";
            //Define rectBounding Main and Footer
            FooterRect = Footer.getBoundingClientRect()
            MainRect = Main.getBoundingClientRect();
    
            if (FooterRect.top + FooterRect.height < window.innerHeight) {
                $(Footer).css({
                    "position": "fixed",
                    "top": "100%",
                    "transform": "translateY(-100%)"
                });
    
                FooterRect = Footer.getBoundingClientRect();
    
                let MainRectHeight = MainRect.top + MainRect.height;
                while(MainRectHeight < FooterRect.top && MainRectHeight < window.innerHeight) {
                    MainRectHeight += 1;
                }
                Main.style.height = `${MainRectHeight - MainRect.top}px`;
            }
        }
        
        function push_footer () {
            var Footer = document.querySelector("#main-footer"),
                Main = document.querySelector("body > main > *:last-child"),
                FooterRectClient = Footer.getBoundingClientRect(),
                footerTopHeight = FooterRectClient.top + FooterRectClient.height;
            
            if (footerTopHeight < window.innerHeight) {
                
                let MainHeight = Main.clientHeight;
                while (footerTopHeight < window.innerHeight) {
                    MainHeight = MainHeight + 1;
                    Main.style.height = MainHeight + "px";
                    footerTopHeight += 1;
                }
                
            }
        }
    
        //$(window).on("resize", adapting_rects);
        //adapting_rects();
        
        $(window).on("resize", push_footer);
        push_footer();
    };
    
    $g.responsiveEmbed = function () {
        var Elems = $("video, iframe");
        
        Elems.each(function () {
            var getParent = $(this).parent(),
                parentWidth = getParent.width(),
                Elem = $(this),
                ElemWidth = Elem.width();
            
            if ((!parentWidth || ElemWidth > parentWidth) && !getParent.hasClass("embed-responsive")) {
                Elem.removeAttr("height width style");
                Elem.addClass("embed-responsive embed-responsive-16by9");
                Elem.wrap(`<div class="embed-responsive embed-responsive-16by9"></div>`);
                $(this).replaceWith(Elem);
            }
        });
    };
    
    $g.tabs = function () {
        var Elems = $("[tabs]");
        
        function build (Elem) {
            var getNav = Elem.find('nav'),
                getButtonsNav = getNav.find('button'),
                getTabs = Elem.find('[tab-display]'),
                current = 0;
            
            getButtonsNav.each(function (index) {
                $(this).attr('tab-target', index);
                getTabs.eq(index).attr('tab-display', index);
            });
            
            //Activar activar los tabs de inicio
            getButtonsNav.eq(current).addClass('act');
            getTabs.eq(current).addClass('act');
            
            //Programar eventos
            getButtonsNav.on('click', function () {
                var getTarget = +$(this).attr('tab-target');
                
                if (getTarget !== current) {
                    getButtonsNav.eq(current).removeClass('act');
                    getTabs.eq(current).removeClass('act');
                    
                    current = getTarget;
                    
                    $(this).addClass('act');
                    getTabs.eq(current).addClass('act');
                }
                
                return false;
            });
        }
        
        Elems.each(function () { build($(this)); });
    };
    
    /* Document Ready */
    $(function () {
        $g.owl.init();
        $g.include();
        $g.metaJsRun();
        $g.nav.init();
        $g.collapsePanel();
        $g.notify.Init();
        $g.ipopover();
        $g.footer_always_bottom();
        $g.responsiveEmbed();
        $g.formatCurrency();
        $g.tabs();
    });
})();