$g.formatCurrency = function () {
    var Elems = $("[format-currency]");
    
    Elems.each(function (index, value) {
        var getText = $(this).text().split("."),
            getPxPositionCents = $(this).attr("px-cents") || -5;
        
        $(this).html(`${getText[0]}.<sup style="position:relative;top:${getPxPositionCents}px">${(getText[1] || "00")}</sup>`);
    });
};