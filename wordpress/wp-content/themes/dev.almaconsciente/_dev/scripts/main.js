var console, document, window, $g;

(function () {
    "use strict";
    
    $g = {
        page: {}
    };
    
    //=include inc/method.metaRunJS.js
    //=include inc/method.owl.js
    //=include inc/method.include.js
    //=include inc/method.contactHome.js
    //=include inc/method.nav.js
    //=include inc/method.formatCurrency.js
    //=include inc/method.collpasePanel.js
    //=include inc/method.notify.js
    //=include inc/method.ipopover.js
    //=include inc/method.footerAlwaysBottom.js
    
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