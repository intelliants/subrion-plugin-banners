<?php
//##copyright##

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (isset($_GET['id']) && ($_GET['id'] || !preg_match('#\D#', $_GET['id']) || ((int)$_GET['id']) > 0))
	{
		$id = (int)$_GET['id'];
		$ip = $iaCore->util()->getIp();

		$iaBanner = $iaCore->factoryPlugin('banners', iaCore::FRONT, 'banner');

		if (!$iaBanner->checkClick($id, $ip))
		{
			$iaBanner->click($id, $ip);
		}
	}
}