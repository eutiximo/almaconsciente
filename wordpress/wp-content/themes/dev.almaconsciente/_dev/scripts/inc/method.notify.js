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