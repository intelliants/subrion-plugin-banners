<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2015 Intelliants, LLC <http://www.intelliants.com>
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
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

$iaDb->setTable('banners');

$iaBanner = $iaCore->factoryPlugin('banners', iaCore::ADMIN, 'banner');
$allowedAction = array('add_block', 'remove_block', 'save_block');
$configAction = isset($_GET['action']) && in_array($_GET['action'], $allowedAction) ? $_GET['action'] : '';

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	$out = array('msg' => '', 'error' => true);
	$id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0;

	if ('remove_block' == $configAction && $id)
	{
		$iaBlock = $iaCore->factory('block', iaCore::ADMIN);

		if (!$iaBlock->delete($id))
		{
			$out['error'] = true;
			$out['msg'] = iaLanguage::get('block_did_not_delete');
		}
		else
		{
			$iaDb->setTable('banners_block_options');
			$iaDb->delete("`block_id` = '$id'");
			$iaDb->resetTable();

			$out['error'] = false;
		}
	}

	if ('save_block' == $configAction && $id)
	{
		$fields = array(
			'amount' => isset($_GET['amount']) ? intval($_GET['amount']) : 0,
			'amount_displayed' => isset($_GET['amount_displayed']) ? intval($_GET['amount_displayed']) : 0,
			'width' => isset($_GET['width']) ? intval($_GET['width']) : 0,
			'height' => isset($_GET['height']) ? intval($_GET['height']) : 0,
			'slider' => isset($_GET['slider']) ? intval($_GET['slider']) : 0
		);

		if ($fields['amount'] < $fields['amount_displayed'])
		{
			$out['error'] = true;
			$out['msg'] = iaLanguage::get('amount_max_less_displayed');
		}
		else
		{
			$iaDb->setTable('banners_block_options');
			$iaDb->update($fields, "`block_id` = '$id'");
			$iaDb->resetTable();

			$out['error'] = false;
			$out['msg'] = iaLanguage::get('block_updated');
		}
	}

	if (empty($out['data']))
	{
		$out['data'] = '';
	}

	$iaView->assign($out);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	iaBreadcrumb::add(iaLanguage::get('banners_blocks_manage'), IA_ADMIN_URL . 'banners/config/');

	$position = isset($_GET['pos']) && !empty($_GET['pos']) ? ($_GET['pos']) : false;
	$title = isset($_GET['title']) && !empty($_GET['title']) ? ($_GET['title']) : '';
	$num = isset($_GET['num']) && !empty($_GET['num']) ? intval($_GET['num']) + 1 : 1;

	if ('add_block' == $configAction && $position)
	{
		$iaBlock = $iaCore->factory('block', iaCore::ADMIN);

		$block = array(
			'name' => 'banner_block_' . $position . '_' . $num,
			'position' => $position,
			'type' => 'smarty',
			'status' => iaCore::STATUS_ACTIVE,
			'header' => 1,
			'collapsible' => 1,
			'multilingual' => 1,
			'sticky' => 1,
			'title' => $title,
			'external' => 1,
			'filename' => 'extra:banners/render-banners',
			'extras' => 'banners'
		);

		$id = $iaBlock->insert($block);

		$fields = array(
			'amount' => 1,
			'amount_displayed' => 1,
			'width' => 50,
			'height' => 50,
			'block_id' => $id,
		);

		$iaDb->setTable('banners_block_options');
		$iaDb->insert($fields);
		$iaDb->resetTable();

		iaCore::util();
		iaUtil::go_to(IA_ADMIN_URL . IA_CURRENT_PLUGIN . '/config/#position-' . $position);
	}

	$positions = $iaDb->onefield('name', '`menu` = 0', null, null, 'positions');

	$iaView->assign('positions', $positions);
	$blocks = array();

	$iaDb->setTable('blocks');
	foreach ($positions as $pos)
	{
		$b = $iaDb->all(iaDb::ALL_COLUMNS_SELECTION, "`position` = '{$pos}' AND `extras` = 'banners'");
		if ($b)
		{
			$blocks[$pos] = $b;
		}
	}
	$iaDb->resetTable();

	$iaDb->setTable('banners_block_options');
	$options = $iaDb->all(iaDb::ALL_COLUMNS_SELECTION);
	$iaDb->resetTable();

	$blockOptions = array();
	foreach ($options as $option)
	{
		$blockOptions[$option['block_id']] = $option;
	}

	$iaView->assign('blocks_options', $blockOptions);
	$iaView->assign('banner_blocks', $blocks);

	$iaView->display('config');
}

$iaDb->resetTable();