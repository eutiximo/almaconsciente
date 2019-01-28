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