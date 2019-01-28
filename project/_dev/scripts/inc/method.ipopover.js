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