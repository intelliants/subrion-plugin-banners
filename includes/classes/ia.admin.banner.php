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

class iaBanner extends abstractModuleAdmin
{
    protected static $_table = 'banners';

    protected $_folder;
    protected $_imgTypes = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png'
    ];

    protected $_flashTypes = [
        'application/x-shockwave-flash' => 'swf'
    ];


    public function __construct()
    {
        $this->iaCore = iaCore::instance();
        $folder = trim($this->iaCore->get('banner_folder'), '/');
        if (!file_exists(IA_UPLOADS . $folder)) {
            mkdir(IA_UPLOADS . $folder);
            chmod(IA_UPLOADS . $folder, 0777);
        }
        $this->_folder = $folder;
    }

    public function getImgTypes()
    {
        return $this->_imgTypes;
    }

    public function getFlashTypes()
    {
        return $this->_flashTypes;
    }

    public function delete($id)
    {
        $banner = $this->getById($id);
        if ('flash' == $banner['type']) {
            $file = IA_UPLOADS . $this->_folder . IA_DS . $banner['image'];
            if (is_file($file)) {
                unlink($file);
            }
        } elseif ('image' == $banner['type']) {
            list($folder, $image) = explode('|', $banner['image']);
            foreach (['large', 'original', 'thumbnail'] as $size) {
                $file = IA_UPLOADS . $folder . $size . IA_DS . $image;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        $this->iaDb->delete('`banner_id` = :id', 'banner_clicks', ['id' => $id]);

        return true;
    }

    public function updateImage(&$banner, $id)
    {

        if (isset($_FILES['uploadfile']['error']) && !$_FILES['uploadfile']['error']) {
            $iaField = $this->iaCore->factory('field');
            $this->iaCore->factory('picture');
            empty($banner['image']) || $iaField->deleteUploadedFile('image', self::getTable(), $id,
                $banner['image']);

            $path = $iaField->uploadImage($_FILES['uploadfile'], $banner['width'],
                $banner['height'], $banner['width'], $banner['height'],
                iaPicture::FIT, $this->_folder, $this->iaCore->get('banner_prefix'));

            $banner['image'] = $path;
        }
    }

    public function updateFlash(&$banner)
    {
        $file = IA_UPLOADS . $this->_folder . IA_DS . $banner['image'];
        if (is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {
            if (!empty($banner['image']) && file_exists($file)) {
                unlink($file);
            }

            $width = $banner['width'];
            $height = $banner['height'];

            $banner['image'] = iaUtil::generateToken() . '.swf';
            $banner['width'] = $width;
            $banner['height'] = $height;
            $file = IA_UPLOADS . $this->_folder . IA_DS . $banner['image'];

            if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
                chmod($file, 0755);
            }
        }
    }
}
