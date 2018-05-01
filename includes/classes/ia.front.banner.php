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

class iaBanner extends abstractModuleFront
{
    protected static $_table = 'banners';
    protected static $_tableClicks = 'banner_clicks';
    protected static $_tableBlockOptions = 'banners_block_options';


    public static function getTableBlockOptions()
    {
        return self::$_tableBlockOptions;
    }

    /**
     * Return sorted array with all banners
     * @return array|bool
     */
    public function getBanners()
    {
        $rows = $this->iaDb->all(iaDb::ALL_COLUMNS_SELECTION, "`status` = 'active'", null, null, self::getTable());

        if (is_array($rows) && $rows) {
            $banners = [];
            foreach ($rows as $entry) {
                $banners[$entry['position']][] = $entry;
            }

            return $banners;
        }

        return false;
    }

    /**
     * Return sorted array with all banners
     * @return array|bool
     */
    public function getAmountDisplayed()
    {
        return $this->iaDb->keyvalue(['block_id', 'amount_displayed'], null, self::getTableBlockOptions());
    }

    /**
     * Used by banner router to increase number of clicks
     *
     * @param $bannerId int
     * @param $ipAddress string
     * @return bool
     */
    public function click($bannerId, $ipAddress)
    {
        $values = [
            'banner_id' => $bannerId,
            'ip' => $ipAddress
        ];
        $this->iaDb->insert($values, ['date' => iaDb::FUNCTION_NOW], self::$_tableClicks);
        $this->iaDb->query([], iaDb::convertIds($bannerId), ['clicked' => '`clicked` + 1'], self::getTable());

        return true;
    }

    /**
     * Checks if a link was already clicked
     *
     * @param $bannerId int
     * @param $ipAddress string
     * @return int
     */
    public function checkClick($bannerId, $ipAddress)
    {
        $sql = <<<SQL
SELECT `id`
FROM `:prefix:table_clicks`
WHERE `ip` = ':ip'
AND `banner_id` = ':banner_id'
AND (TO_DAYS(NOW()) - TO_DAYS(`date`)) <= 1
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_clicks' => self::$_tableClicks,
            'ip' => $ipAddress,
            'banner_id' => $bannerId,
        ]);

        return $this->iaDb->getOne($sql);
    }

    /**
     * Count banners impressions
     *
     * @param $params array
     *
     * @return void
     */
    public static function impressionsCount(array $params)
    {
        $id = $params['id'];
        $iaCore = iaCore::instance();
        $iaCore->iaDb->update([], iaDb::convertIds($id), ['showed' => "`showed` + 1"], self::getTable());
    }
}