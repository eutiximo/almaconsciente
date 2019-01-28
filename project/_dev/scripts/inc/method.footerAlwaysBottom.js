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