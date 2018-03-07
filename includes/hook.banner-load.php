<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2018 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
    $iaBanner = $iaCore->factoryModule('banner', 'banners');
    $iaSmarty->registerPlugin(iaSmarty::PLUGIN_FUNCTION, 'bannerImpressionsCount', array('iaBanner', 'impressionsCount'));
    $banners_slider = $iaDb->keyvalue(array('block_id', 'slider'), null, iaBanner::getTableBlockOptions());

    $iaView->assign('banners_slider', $banners_slider);
    $iaView->assign('banners_displayed', $iaBanner->getAmountDisplayed());
    $iaView->assign('banners', $iaBanner->getBanners());
}