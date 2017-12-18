<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
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

final class iaBanner extends abstractModuleAdmin
{
    protected static $_table = 'banners';

    private $_imgTypes = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png'
    ];

    private $_flashTypes = [
        'application/x-shockwave-flash' => 'swf'
    ];


    public function __construct()
    {
        $this->iaCore = iaCore::instance();
        $folder = trim($this->iaCore->get('banner_folder'), " /");
        if (!file_exists(IA_UPLOADS . $folder))
        {
            mkdir(IA_UPLOADS . $folder);
            chmod(IA_UPLOADS . $folder, 0777);
        }
    }

    public function getImgTypes()
    {
        return $this->_imgTypes;
    }

    public function getFlashTypes()
    {
        return $this->_flashTypes;
    }

    /**
    * Adds banner record in table
    *
    * @param array $banner banner info
    *
    * @return int
    */
    public function insert(array $itemData)
    {
        $itemData['date_added'] = date(iaDb::DATETIME_FORMAT);

        if ('image' == $itemData['type'])
        {
            $this->_updateImage($itemData);
        }
        elseif ('flash' == $itemData['type'])
        {
            $this->_updateFlash($itemData);
        }

        unset($itemData['folder'], $itemData['imageResize'], $itemData['targetframe'], $itemData['params']);

        return $this->iaDb->insert($itemData, null, self::getTable());
    }

    public function updateBanner($banner, $where = '')
    {
        if ('image' == $banner['type'])
        {
            $this->_updateImage ($banner);
        }
        elseif ('flash' == $banner['type'])
        {
            $this->_updateFlash ($banner);
        }

        unset($banner['folder'], $banner['imageResize'], $banner['targetframe'], $banner['params']);

        return $this->iaDb->update($banner, $where, null, self::getTable());
    }

    /**
    * Updates banner info
    *
    * @param arr $banner
    * @param str $where
    *
    * @return bool
    */
    public function gridRead($params, $columns, array $filterParams = [], array $persistentConditions = [])
    {
        $params || $params = [];
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 15;

        if (!empty($params['sort']) && is_string($params['sort'])) {
            $sort = $params['sort'];
            $dir = in_array($params['dir'], [iaDb::ORDER_ASC, iaDb::ORDER_DESC]) ? $params['dir'] : iaDb::ORDER_ASC;
            $order = ($sort && $dir) ? "`{$sort}` {$dir}" : 't1.`date` DESC';
        }

        $where = $values = [];
        foreach ($filterParams as $name => $type)
        {
            if (isset($params[$name]) && $params[$name])
            {
                $value = iaSanitize::sql($params[$name]);

                switch ($type)
                {
                    case 'equal':
                        $where[] = sprintf('`%s` = :%s', $name, $name);
                        $values[$name] = $value;
                        break;
                    case 'like':
                        $where[] = sprintf('`%s` LIKE :%s', $name, $name);
                        $values[$name] = '%' . $value . '%';
                }
            }
        }

        $where = array_merge($where, $persistentConditions);
        $where || $where[] = iaDb::EMPTY_CONDITION;
        $where = implode(' AND ', $where);
        $this->iaDb->bind($where, $values);

        if (is_array($columns))
        {
            $columns = array_merge(['id', 'update' => 1, 'delete' => 1], $columns);
        }

        $sql =
        "SELECT SQL_CALC_FOUND_ROWS bn.*, bl.name `position_title`, bl.`position` `banner_position`, bl.`id` as `edit_block`, bn.`id` as `update`, 1 as `delete`
        FROM `{$this->iaDb->prefix}banners` bn
        LEFT JOIN `{$this->iaDb->prefix}blocks` bl
            ON bn.`position` = bl.`id` " .
        'WHERE ' . $where . ' ' .
        'LIMIT ' . $start . ', ' . $limit;

        return [
            'data' => $this->iaDb->getAll($sql),
            'total' => (int)$this->iaDb->one(iaDb::STMT_COUNT_ROWS, $where)
        ];
    }

    public function gridDelete($params, $languagePhraseKey = 'deleted')
    {
        $affected = 0;

        foreach($params['id'] as $bannerId)
        {
            $folder = trim($this->iaCore->get('banner_folder'), " /");
            $this->iaDb->setTable(self::getTable());
            $image = $this->iaDb->one("image", "id='" . $bannerId . "'");
            $this->iaDb->delete("`id` = '" . $bannerId . "'");
            $this->iaDb->resetTable();

            // validate it once more... (remove all the / and \ characters)
            $image = str_replace(['/',"\\"], "", $image);
            /**
             * Remove original image
             */
            if (is_file(IA_HOME . "uploads/" . $folder . IA_DS . $image))
            {
                unlink(IA_HOME . "uploads" . IA_DS . $folder . IA_DS . $image);
            }

            /**
             * Remove thumbshot
             */
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $filename = substr($image, 0, strpos($image, '.'));
            if (is_file(IA_HOME . 'uploads/' . $folder . IA_DS . $filename . '~.' . $ext))
            {
                unlink(IA_HOME . 'uploads' . IA_DS . $folder . IA_DS . $filename . '~.' . $ext);
            }

            $this->iaDb->delete('`banner_id` = :id', 'banner_clicks', ['id' => $bannerId]);
        }

        $result['result'] = true;
        $result['message'] = iaLanguage::get('deleted');

        return $result;
    }

    private function _updateImage(&$banner)
    {
        if (isset($_FILES['uploadfile']['error']) && !$_FILES['uploadfile']['error'])
        {
            $iaField = $this->iaCore->factory('field');
            $iaPicture = $this->iaCore->factory('picture');

            $field = [
                'type' => $iaField::IMAGE,
                'thumb_width' => $banner['width'],
                'thumb_height' => $banner['height'],
                'image_width' => $banner['width'],
                'image_height' => $banner['height'],
                'resize_mode' => $iaPicture::FIT,
                'folder_name' => $this->iaCore->get('banner_folder'),
                'file_prefix' => $this->iaCore->get('banner_prefix')
            ];

            empty($banner['image']) || $iaField->deleteUploadedFile('image', self::getTable(), $banner['id'], $banner['image']);

            $imageEntry = $iaField->processUploadedFile($_FILES['uploadfile']['tmp_name'], $field, $_FILES['uploadfile']['name']);

            $banner['image'] = $imageEntry['path'] . '|' . $imageEntry['file'];
        }
    }

    private function _updateFlash (&$banner)
    {
        $file = IA_HOME . 'uploads' . IA_DS . $banner['folder'] . $banner['image'];

        if (is_uploaded_file($_FILES['uploadfile']['tmp_name']))
        {
            if (!empty($banner['image']) && file_exists($file))
            {
                unlink($file);
            }

//          list($iwidth, $iheight) = @getimagesize($_FILES['uploadfile']['tmp_name']);
            $width = $banner['width'];
            $height = $banner['height'];

            // set image
            $banner['image'] = iaUtil::generateToken() . '.swf';
            $file = IA_HOME . 'uploads' . IA_DS . $banner['folder'] . IA_DS . $banner['image'];
            $banner['width'] = $width;
            $banner['height'] = $height;

            if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file))
            {
                chmod($file, 0755);

                return true;
            }
        }

        return false;
    }
}