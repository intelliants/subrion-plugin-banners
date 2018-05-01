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
    const ADD = 'add_block';
    const REMOVE = 'remove_block';
    const SAVE = 'save_block';

    protected $_name = 'banners';
    protected static $_tableBlockOptions = 'banners_block_options';


    public function __construct()
    {
        parent::__construct();
        $this->setHelper($this->_iaCore->factoryModule('banner', $this->getModuleName()));
    }

    protected function _gridRead($params)
    {
        $output = ['msg' => '', 'error' => true];
        $id = isset($params['id']) && !empty($params['id']) ? intval($params['id']) : 0;

        if (self::REMOVE == $params['action'] && $id) {
            $iaBlock = $this->_iaCore->factory('block', iaCore::ADMIN);

            if (!$iaBlock->delete($id)) {
                $output['error'] = true;
                $output['msg'] = iaLanguage::get('block_did_not_delete');
            } else {
                $this->_iaDb->delete(iaDb::convertIds($id, 'block_id'), self::$_tableBlockOptions);

                $output['error'] = false;
            }
        }

        if (self::SAVE == $params['action'] && $id) {
            $fields = [
                'amount' => isset($params['amount']) ? intval($params['amount']) : 0,
                'amount_displayed' => isset($params['amount_displayed']) ? intval($params['amount_displayed']) : 0,
                'width' => isset($params['width']) ? intval($params['width']) : 0,
                'height' => isset($params['height']) ? intval($params['height']) : 0,
                'slider' => isset($params['slider']) ? intval($params['slider']) : 0
            ];

            if ($fields['amount'] < $fields['amount_displayed']) {
                $output['error'] = true;
                $output['msg'] = iaLanguage::get('amount_max_less_displayed');
            } else {
                $this->_iaDb->update($fields, iaDb::convertIds($id, 'block_id'), null, self::$_tableBlockOptions);

                $output['error'] = false;
                $output['msg'] = iaLanguage::get('block_updated');
            }
        }

        return $output;
    }

    protected function _indexPage(&$iaView)
    {
        $action = empty($_GET['action']) ? '' : $_GET['action'];

        $position = empty($_GET['pos']) ? null : $_GET['pos'];
        $title = empty($_GET['title']) ? '' : $_GET['title'];
        $num = empty($_GET['num']) ? 1 : intval($_GET['num']) + 1;

        if (self::ADD == $action && $position) {
           $this->_addBlock($position, $num, $title);
        }

        $positions = $this->_iaDb->onefield('name', '`menu` = 0', null, null, 'positions');
        $this->_iaCore->iaView->assign('positions', $positions);
        $blocks = [];

        foreach ($positions as $position) {
            $sql = <<<SQL
SELECT b.*, l.`value` `title` FROM `:prefix:table_blocks` b 
LEFT JOIN `:prefix:table_language` l ON (l.`key` = CONCAT('block_title_', b.`id`)) 
WHERE b.`position` = ':position' AND b.`module` = ':module'
SQL;
            $sql = iaDb::printf($sql, [
                'prefix' => $this->_iaDb->prefix,
                'table_blocks' => 'blocks',
                'table_language' => iaLanguage::getTable(),
                'position' => $position,
                'module' => $this->getModuleName()
            ]);
            $block = $this->_iaDb->getAll($sql);
            if ($block) {
                $blocks[$position] = $block;
            }
        }

        $this->_iaDb->setTable(self::$_tableBlockOptions);
        $options = $this->_iaDb->all(iaDb::ALL_COLUMNS_SELECTION);
        $this->_iaDb->resetTable();

        $blockOptions = [];
        foreach ($options as $option) {
            $blockOptions[$option['block_id']] = $option;
        }

        $iaView->assign('blocks_options', $blockOptions);
        $iaView->assign('banner_blocks', $blocks);
        $iaView->display('config');
    }

    protected function _addBlock($position, $num, $title)
    {
        $iaBlock = $this->_iaCore->factory('block', iaCore::ADMIN);

        $block = [
            'name' => 'banner_block_' . $position . '_' . $num,
            'position' => $position,
            'type' => 'smarty',
            'status' => iaCore::STATUS_ACTIVE,
            'header' => 1,
            'collapsible' => 1,
            'sticky' => 1,
            'title' => $title,
            'external' => 1,
            'filename' => 'extra:banners/render-banners',
            'module' => 'banners'
        ];

        $id = $iaBlock->insert($block);

        $fields = [
            'amount' => 1,
            'amount_displayed' => 1,
            'width' => 360,
            'height' => 360,
            'block_id' => $id,
        ];
        $this->_iaDb->insert($fields, null, self::$_tableBlockOptions);

        iaCore::util();
        iaUtil::go_to(IA_ADMIN_URL . IA_CURRENT_MODULE . '/config/#position-' . $position);
    }
}