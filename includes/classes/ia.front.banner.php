<?php
//##copyright##

final class iaBanner extends abstractPlugin
{
	protected static $_table = 'banners';

	protected static $_tableBlockOptions = 'banners_block_options';


	public static function getTableBlockOptions ()
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

		if (is_array($rows) && $rows)
		{
			$banners = array();
			foreach ($rows as $entry)
			{
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
		return $this->iaDb->keyvalue(array('block_id', 'amount_displayed'), null, self::getTableBlockOptions());
	}

	/**
	* Used by banner router to increase number of clicks
	*
	* @param int $bId
	* @param $aIp str
	*
	* @return bool
	*/
	public function click($bannerId, $ipAddress)
	{
		$row = array(
			'banner_id' => $bannerId,
			'ip' => $ipAddress
		);
		$this->iaDb->insert($row, array('date' => iaDb::FUNCTION_NOW), 'banner_clicks');
		$this->iaDb->query("UPDATE `" . self::getTable(true) . "` SET `clicked` = `clicked` + 1 WHERE `id`='" . $bId . "'");

		return true;
	}

	/**
	* Checks if a link was already clicked
	*
	* @param int $aId banner id
	* @param str $aIp ip address
	*
	*
	* @return int
	*/
	public function checkClick($bannerId, $ipAddress)
	{
		$sql = "SELECT `id`
			FROM `" . $this->iaDb->prefix . "banner_clicks`
			WHERE `ip` = '" . $ipAddress . "'
				AND `banner_id` = '" . $bannerId . "'
				AND (TO_DAYS(NOW()) - TO_DAYS(`date`)) <= 1 ";

		return $this->iaDb->getOne($sql);
	}

	/**
	 * Count banners impressions
	 *
	 * @param $params arr
	 *
	 * @return void
	 */
	public static function impressionsCount($params)
	{
		$id = $params['id'];
		$iaCore = iaCore::instance();
		$iaCore->iaDb->update(array(), "`id` = '$id'", array('showed' => "`showed` + '1'"), self::getTable());
	}
}