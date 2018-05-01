$(function () {
    var countBannerClick = function (a) {
        itemid = a.id.substring(1);
        if (parseInt(itemid.charAt(0)) < 1 || itemid.match(/\D/)) {
            return;
        }
        var image = new Image();
        var token = Math.random();

        image.src = intelli.config.url + 'banners/index.json?id=' + itemid + '&h=' + token;
    };

    $("a[id^='b']").click(function () {
        countBannerClick(this);
    });
});