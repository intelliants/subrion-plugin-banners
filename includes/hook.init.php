<?php

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $iaBanner = $iaCore->factoryModule('banner', 'banners', iaCore::FRONT);
    $iaSmarty->registerPlugin(iaSmarty::PLUGIN_FUNCTION, 'bannerImpressionsCount', ['iaBanner', 'impressionsCount']);
    $banners_slider = $iaDb->keyvalue(['block_id', 'slider'], null, iaBanner::getTableBlockOptions());

    $iaView->assign('banners_slider', $banners_slider);
    $iaView->assign('banners_displayed', $iaBanner->getAmountDisplayed());
    $iaView->assign('banners', $iaBanner->getBanners());
}