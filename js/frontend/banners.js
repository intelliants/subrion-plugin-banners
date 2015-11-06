var a = $("a[id^='b']").click(function()
{
	countBannerClick(this);
});

function countBannerClick(a)
{
	itemid = a.id.substring(1);
	if(parseInt(itemid.charAt(0)) < 1 || itemid.match(/\D/))
	{
		return true;
	}
	i = new Image();
	h = Math.random();

	i.src= intelli.config.ia_url+'banners/index.json?id=' + itemid + '&h='+h;

	return true;
}
