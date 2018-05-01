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


class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'banners';

    protected $_table = 'banners';
    protected $_tableClicks = 'banners_clicks';
    protected $_tableBlockOptions = 'banners_block_options';
    protected $_phraseAddSuccess = 'banner_added';
    protected $_phraseGridEntryDeleted = 'banner_deleted';


    public function __construct()
    {
        parent::__construct();

        $this->setHelper($this->_iaCore->factoryModule('banner', $this->getModuleName()));
    }

    protected function _indexPage(&$iaView)
    {
        $iaView->grid('_IA_URL_modules/' . $this->getModuleName() . '/js/admin/banners');
    }

    protected function _setPageTitle(&$iaView, array $entryData, $action)
    {
        if (in_array($action, [iaCore::ACTION_ADD, iaCore::ACTION_EDIT])) {
            $iaView->title(iaLanguage::get($iaView->get('action') . '_banner'));
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS bn.*, bl.name `position_title`, bl.`position` `banner_position`,
bl.`id` `edit_block`, 1 `update`, 1 `delete`
FROM `:prefix:table_banners` bn
LEFT JOIN `:prefix:table_blocks` bl ON bn.`position` = bl.`id` 
WHERE :where
LIMIT :start, :limit
SQL;
        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_banners' => self::getTable(true),
            'table_blocks' => 'blocks',
            'where' => $where,
            'start' => $start,
            'limit' => $limit,
        ]);

        return $this->_iaDb->getAll($sql);
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry['type'] = null;
        $entry['image'] = '';
        $entry['position'] = null;
        $entry['title'] = null;
        $entry['alt'] = null;
        $entry['content'] = null;
        $entry['planetext_content'] = null;
        $entry['width'] = 0;
        $entry['height'] = 0;
        $entry['url'] = 'http://';
        $entry['target'] = null;
        $entry['no_follow'] = 0;
        $entry['status'] = iaCore::STATUS_ACTIVE;
        $entry['params'] = 0;
        $entry['member_id'] = iaUsers::getIdentity()->id;
    }

    protected function _targets()
    {
        return [
            '_blank' => iaLanguage::get('_blank'),
            '_self' => iaLanguage::get('_self'),
            '_parent' => iaLanguage::get('_parent'),
            '_top' => iaLanguage::get('_top')
        ];
    }

    protected function _types()
    {
        return [
            'local' => iaLanguage::get('local'),
            'remote' => iaLanguage::get('remote'),
            'text' => iaLanguage::get('text'),
            'html' => iaLanguage::get('html')
        ];
    }

    protected function _getPositions()
    {
        $sql = <<<SQL
SELECT bl.*, l.`value` title, COUNT(bn.`id`) bn_col, opt.`amount` bn_max, opt.`width`, opt.`height`
FROM `:prefix:table_blocks` bl
LEFT JOIN `:prefix:table_options` opt ON bl.`id` = opt.`block_id`
LEFT JOIN `:prefix:table_banners` bn ON bn.`position` = bl.`id`
LEFT JOIN `:prefix:table_language` l ON (l.`key` = CONCAT("block_title_", bl.`id`))
WHERE bl.`module` = ':module'
GROUP BY bl.`id`
SQL;
        $sql = iaDb::printf($sql, [
            'prefix' => $this->_iaDb->prefix,
            'table_blocks' => 'blocks',
            'table_options' => 'banners_block_options',
            'table_banners' => self::getTable(),
            'table_language' => iaLanguage::getTable(),
            'module' => self::getModuleName(),
        ]);

        $positions = $this->_iaDb->getAll($sql);

        if (!is_array($positions) || empty($positions)) {
            // todo: implement error handling
            $no_positions = str_replace('%href_to_config%', IA_ADMIN_URL . 'banners/config/',
                iaLanguage::get('please_create_block'));
            $this->_iaCore->iaView->setMessages($no_positions, iaView::ERROR);
        }

        return $positions;
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        parent::_assignValues($iaView, $entryData);

        if (!is_writable(IA_UPLOADS)) {
            $iaView->setMessages(iaLanguage::get('uploads_not_writable'), 'error');
        }
        $iaView->assign('targets', $this->_targets());
        $iaView->assign('types', $this->_types());
        $iaView->assign('positions', $this->_getPositions());
    }

    protected function _entryDelete($entryId)
    {
        $this->getHelper()->delete($entryId);

        return parent::_entryDelete($entryId);
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);

        iaUtil::loadUTF8Functions('ascii', 'validation', 'bad', 'utf8_to_ascii');

        if (!utf8_is_valid($entry['title'])) {
            $entry['title'] = utf8_bad_replace($entry['title']);
        }

        if (!utf8_is_valid($entry['alt'])) {
            $entry['alt'] = utf8_bad_replace($entry['alt']);
        }

        if (!utf8_is_valid($entry['content'])) {
            $entry['content'] = utf8_bad_replace($entry['content']);
        }

        if (!utf8_is_valid($entry['planetext_content'])) {
            $entry['planetext_content'] = utf8_bad_replace($entry['planetext_content']);
        }

        $entry['url'] = (strstr($entry['url'], 'http://') || strstr($entry['url'], 'https://'))
            ? $entry['url'] : 'http://' . $entry['url'];
        $entry['type'] = array_key_exists($entry['type'], $this->_types()) ? $entry['type'] : false;
        $entry['folder'] = trim($this->_iaCore->get('banner_folder'), ' /') . '/';

        if (iaCore::ACTION_EDIT != $action) {
            foreach ($this->_getPositions() as $position) {
                if ($entry['position'] == $position['id']) {
                    if ($position['bn_col'] == $position['bn_max']) {
                        $entry['position'] = false;
                    }
                }
            }
        }

        if (empty($entry['date_added'])) {
            $entry['date_added'] = date(iaDb::DATETIME_FORMAT);
        }

        if (!empty($entry['params'])) {
            $entry['image_width'] = $entry['image_height'] = 0;
        }
        unset($entry['owner'], $entry['targetframe'], $entry['params'], $entry['folder']);

        if (empty($entry['position'])) {
            $messages[] = iaLanguage::get('banner_position_incorrect');
        }

        if (empty($entry['title'])) {
            $messages[] = iaLanguage::get('banner_title_is_empty');
        }

        if (empty($entry['type'])) {
            $messages[] = iaLanguage::get('banner_type_incorrect');
        }

        if ('local' == $entry['type']) {
            if (is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {
                if (array_key_exists($_FILES['uploadfile']['type'], $this->getHelper()->getimgTypes())) {
                    $entry['type'] = 'image';
                } elseif (array_key_exists($_FILES['uploadfile']['type'], $this->getHelper()->getFlashTypes())) {
                    $entry['type'] = 'flash';
                } else {
                    $messages[] = iaLanguage::get('incorrect_filetype');
                }
            } elseif (in_array(pathinfo($entry['image'], PATHINFO_EXTENSION), $this->getHelper()->getimgTypes())) {
                $entry['type'] = 'image';
            } elseif (in_array(pathinfo($entry['image'], PATHINFO_EXTENSION), $this->getHelper()->getFlashTypes())) {
                $entry['type'] = 'flash';
            } elseif (iaCore::ACTION_ADD == $action) {
                $messages[] = iaLanguage::get('unknown_upload');
            }
        }

        if ('html' == $entry['type']) {
            $entry['planetext_content'] = '';
        } elseif ('text' == $entry['type']) {
            $entry['content'] = '';
        } else {
            $entry['content'] = '';
            $entry['planetext_content'] = '';
        }

        if (empty($entry['content']) && empty($entry['planetext_content']) && in_array($entry['type'],
                ['html', 'text'])) {
            $messages[] = iaLanguage::get('content_incorrect');
        }

        if (empty($entry['image']) && 'remote' == $entry['type']) {
            $messages[] = iaLanguage::get('remote_url_incorrect');
        }

        if ('image' == $entry['type']) {
            $this->getHelper()->updateImage($entry);
        } elseif ('flash' == $entry['type']) {
            $this->getHelper()->updateFlash($entry);
        }

        /*
        if (!iaValidate::isUrl($entry['url']) && 'html' != $banner['type'])
        {
            $error = true;
            $messages = iaLanguage::get('banner_url_incorrect');
        }
        */

        return !$this->getMessages();
    }

    protected function _postSaveEntry(array &$entry, array $data, $action)
    {
        $iaLog = $this->_iaCore->factory('log');

        $actionCode = (iaCore::ACTION_ADD == $action)
            ? iaLog::ACTION_CREATE
            : iaLog::ACTION_UPDATE;
        $params = [
            'module' => $this->getModuleName(),
            'item' => 'banner',
            'name' => $entry['title'],
            'id' => $this->getEntryId()
        ];
        $iaLog->write($actionCode, $params);
    }
}
