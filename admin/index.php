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

$iaBanner = $iaCore->factoryPlugin('banners', iaCore::ADMIN, 'banner');

$iaDb->setTable(iaBanner::getTable());

if ($iaView->getRequestType() == iaView::REQUEST_JSON)
{
	switch ($pageAction)
	{
		case iaCore::ACTION_READ:
			$columns = array('title', 'position_title', 'banner_position', 'type', 'showed', 'clicked', 'status');
			$filterParams = array(
				'title' => 'like',
				'status' => 'equal'
			);

			$output = $iaBanner->gridRead($_GET, $columns, $filterParams);

			break;

		case iaCore::ACTION_EDIT:
			$output = $iaBanner->gridUpdate($_POST);

			break;

		case iaCore::ACTION_DELETE:
			$output = $iaBanner->gridDelete($_POST);
	}

	$iaView->assign($output);

}
elseif ($iaView->getRequestType() == iaView::REQUEST_HTML)
{
	if ((iaCore::ACTION_ADD == $pageAction) || (iaCore::ACTION_EDIT == $pageAction))
	{
		if (iaCore::ACTION_EDIT == $pageAction && !isset($iaCore->requestPath[0]))
		{
			return iaView::errorPage(iaVIew::ERROR_NOT_FOUND);
		}

		iaBreadcrumb::replaceEnd(iaLanguage::get($pageAction . '_banner'));

		if (!is_writable(IA_UPLOADS))
		{
			$iaView->setMessages(iaLanguage::get('uploads_not_writable'), 'error');
		}

		$targets = array(
			'_blank' => iaLanguage::get('_blank'),
			'_self' => iaLanguage::get('_self'),
			'_parent' => iaLanguage::get('_parent'),
			'_top' => iaLanguage::get('_top')
		);

		$statuses = array(
			iaCore::STATUS_INACTIVE => iaLanguage::get(iaCore::STATUS_INACTIVE),
			iaCore::STATUS_ACTIVE => iaLanguage::get(iaCore::STATUS_ACTIVE)
		);

		$types = array(
			'local' => iaLanguage::get('local'),
			'remote' => iaLanguage::get('remote'),
			'text' => iaLanguage::get('text'),
			'html' => iaLanguage::get('html')
		);

		$iaDb->setTable('blocks');
		$sql = "
			SELECT bl.*, COUNT(bn.`id`) as bn_col, opt.`amount` as bn_max, opt.`width`, opt.`height`
			  FROM `{$iaDb->prefix}blocks` as bl
			LEFT JOIN `{$iaDb->prefix}banners_block_options` as opt
			  ON bl.`id` = opt.`block_id`
			LEFT JOIN `{$iaDb->prefix}banners` as bn
			  ON bn.`position` = bl.`id`
			WHERE bl.`extras` = 'banners'
			GROUP BY bl.`id`
		";
		$positions = $iaDb->getAll($sql);

		$iaDb->resetTable();

		if (!is_array($positions) || empty($positions))
		{
			$no_positions = str_replace('%href_to_config%', IA_ADMIN_URL . 'banners/config/', iaLanguage::get('please_create_block'));
			$iaView->setMessages($no_positions, iaView::ERROR);
		}

		if (iaCore::ACTION_EDIT == $pageAction)
		{
			$id = (int)$iaCore->requestPath[0];
			$banner = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($id));
			if (!$banner)
			{
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
			}
		}
		else
		{
			$banner = array(
				'type' => null,
				'image' => '',
				'position' => null,
				'title' => null,
				'alt' => null,
				'content' => null ,
				'planetext_content' => null,
				'width' => 0,
				'height' => 0,
				'url' => 'http://',
				'target' => null,
				'no_follow' => 0,
				'status' => iaCore::STATUS_ACTIVE,
				'params' => 0,
				'id' => 0
			);
		}

		$banner = array(
			'id' => $banner['id'],
			'type' => iaUtil::checkPostParam('type', $banner),
			'image' => iaUtil::checkPostParam('image', $banner),
			'position' => iaUtil::checkPostParam('position', $banner),
			'title' => iaUtil::checkPostParam('title', $banner),
			'alt' => iaUtil::checkPostParam('alt', $banner),
			'content' => iaUtil::checkPostParam('content', $banner),
			'planetext_content' => iaUtil::checkPostParam('planetext_content', $banner),
			'width' => iaUtil::checkPostParam('width', $banner),
			'height' => iaUtil::checkPostParam('height', $banner),
			'url' => iaUtil::checkPostParam('url', $banner),
			'target' => iaUtil::checkPostParam('target', $banner),
			'targetframe' => iaUtil::checkPostParam('targetframe', $banner),
			'no_follow' => iaUtil::checkPostParam('no_follow', $banner),
			'status' => iaUtil::checkPostParam('status', $banner),
			'params' => iaUtil::checkPostParam('params', $banner),
		);

		if (isset($_POST['save']))
		{
			$error = false;
			$messages = array();

			iaUtil::loadUTF8Functions('ascii', 'validation', 'bad', 'utf8_to_ascii');

			if (!empty($banner['title']) && !utf8_is_valid($banner['title']))
			{
				$banner['title'] = utf8_bad_replace($banner['title']);
			}

			if (!empty($banner['alt']) && !utf8_is_valid($banner['alt']))
			{
				$banner['alt'] = utf8_bad_replace($banner['alt']);
			}

			if (!empty($banner['content']) && !utf8_is_valid($banner['content']))
			{
				$banner['content'] = utf8_bad_replace($banner['content']);
			}

			if (!empty($banner['planetext_content']) && !utf8_is_valid($banner['planetext_content']))
			{
				$banner['planetext_content'] = utf8_bad_replace($banner['planetext_content']);
			}

			$banner['url'] = !strstr($banner['url'], "http://") ? "http://" . $banner['url'] : $banner['url'];
			$banner['status'] = array_key_exists($banner['status'], $statuses) ? $banner['status'] : 'inactive';
			$banner['type'] = array_key_exists($banner['type'], $types) ? $banner['type'] : false;
			$banner['folder'] = trim($iaCore->get('banner_folder'), ' /') . '/';

			if (iaCore::ACTION_EDIT != $pageAction)
			{
				foreach ($positions as $position)
				{
					if ($banner['position'] == $position['id'])
					{
						if ($position['bn_col'] == $position['bn_max'])
						{
							$banner['position'] = false;
						}
					}
				}
			}

			if ($banner['params'])
			{
				$banner['image_width'] = $banner['image_height'] = 0;
			}

			if (!$banner['position'])
			{
				$error = true;
				$messages[] = iaLanguage::get('banner_position_incorrect');
			}

			if (empty($banner['title']))
			{
				$error = true;
				$messages[] = iaLanguage::get('banner_title_is_empty');
			}

			if (!$banner['type'])
			{
				$error = true;
				$messages[] = iaLanguage::get('banner_type_incorrect');
			}

			if ('local' == $banner['type'])
			{
				if (is_uploaded_file($_FILES['uploadfile']['tmp_name']))
				{
					if (array_key_exists($_FILES['uploadfile']['type'], $iaBanner->getimgTypes()))
					{
						$banner['type'] = 'image';
					}
					elseif (array_key_exists($_FILES['uploadfile']['type'], $iaBanner->getFlashTypes()))
					{
						$banner['type'] = 'flash';
					}
					else
					{
						$error = true;
						$messages[] = iaLanguage::get('incorrect_filetype');
					}
				}
				elseif (in_array(pathinfo($banner['image'], PATHINFO_EXTENSION), $iaBanner->getimgTypes()))
				{
					$banner['type'] = 'image';
				}
				elseif (in_array(pathinfo($banner['image'], PATHINFO_EXTENSION), $iaBanner->getFlashTypes()))
				{
					$banner['type'] = 'flash';
				}
				elseif (iaCore::ACTION_ADD == $pageAction)
				{
					$error = true;
					$messages[] = iaLanguage::get('unknown_upload');
				}
			}

			if ('html' == $banner['type'])
			{
				$banner['planetext_content'] = '';
			}
			elseif ('text' == $banner['type'])
			{
				$banner['content'] = '';
			}
			else
			{
				$banner['content'] = '';
				$banner['planetext_content'] = '';
			}

			if (empty($banner['content']) && empty($banner['planetext_content']) && in_array($banner['type'], array('html','text')))
			{
				$error = true;
				$messages[] = iaLanguage::get('content_incorrect');
			}

			if (empty($banner['image']) && 'remote' == $banner['type'])
			{
				$error = true;
				$messages[] = iaLanguage::get('remote_url_incorrect');
			}

			if (!iaValidate::isUrl($banner['url']) && 'html' != $banner['type'])
			{
				$error = true;
				$messages = iaLanguage::get('banner_url_incorrect');
			}

			if (!$error)
			{
				if (iaCore::ACTION_EDIT == $pageAction)
				{
					$iaBanner->updateBanner($banner);

					if ($iaDb->exists("`status` = 'active'"))
					{
						$iaCore->set('banners_exist', '1', true);
					}
					else
					{
						$iaCore->set('banners_exist', '', true);
					}

					$messages[] = iaLanguage::get('changes_saved');
				}
				else
				{
					unset($banner['targetframe'], $banner['params']);
					$banner['id'] = $iaBanner->insert($banner);

					if ($iaDb->exists("`status` = 'active'"))
					{
						$iaCore->set("banners_exist", "1", true);
					}
					else
					{
						$iaCore->set("banners_exist", "", true);
					}

					//clear_cache();
					$messages[] = iaLanguage::get('banner_added');
				}
				$iaView->setMessages($messages, ($error ? iaView::ERROR : iaView::SUCCESS));

				if (isset($_POST['goto']))
				{
					$url = IA_ADMIN_URL . 'banners/';
					$goto = array(
						'add' => $url . '/add/',
						'list' => $url,
						'stay' => $url . '/edit/' . $banner['id'],
					);
					iaUtil::post_goto($goto);
				}
				else
				{
					iaUtil::go_to(IA_ADMIN_URL . 'banners/edit/' . $banner['id']);
				}

			}
			else
			{
				$iaView->setMessages($messages, ($error ? iaView::ERROR : iaView::SUCCESS));
			}
		}
		$options = array('list' => 'go_to_list', 'add' => 'add_another_one', 'stay' => 'stay_here');
		$iaView->assign('goto', $options);

		$iaView->assign('banner', $banner);
		$iaView->assign('targets', $targets);
		$iaView->assign('types', $types);
		$iaView->assign('positions', $positions);
		$iaView->assign('statuses', $statuses);
	}
	else
	{
		$iaView->grid();
	}

	$iaView->display('index');
}

$iaDb->resetTable();