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
    });
})();