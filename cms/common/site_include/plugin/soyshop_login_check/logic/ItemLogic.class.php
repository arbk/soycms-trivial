<?php

class ItemLogic extends SOY2LogicBase{

	const PLUGIN_ID = "SOYShopLoginCheck";

	private $siteId;
	private $userId;

	function __construct(){}

	function getItems(){
		$old = SOYShopUtil::switchShopMode($this->siteId);

		try{
			$items = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->get();
		}catch(Exception $e){
			$items = array();
		}

		SOYShopUtil::resetShopMode($old);

		return $items;
	}

	function checkPurchasedSingle($itemCode){
		if(!$this->userId) $this->userId = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.LoginCheckLogic", array("siteId" => $this->siteId))->getUserId();
		$userId = $this->userId;

		if(!is_numeric($userId)) return false;

		$old = SOYShopUtil::switchShopMode($this->siteId);
		if(!class_exists("SOYShop_Order")) SOY2::import("domain.order.SOYShop_Order");

		$dao = new SOY2DAO();

		$sql = "SELECT * FROM soyshop_item item ".
				"INNER JOIN soyshop_orders orders ".
				"ON item.id = orders.item_id ".
				"INNER JOIN soyshop_order o ".
				"ON orders.order_id = o.id ".
				"WHERE item.item_code = :code ".
				"AND o.user_id = :userId ".
				"AND o.order_status >= " . SOYShop_Order::ORDER_STATUS_REGISTERED . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . " ";
		$binds = array(":userId" => $userId, ":code" => $itemCode);

		try{
			$results = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			//適当な値を入れてしのぐ
			$results = array("hoge");
		}

		SOYShopUtil::resetShopMode($old);

		//購入していなければ空の配列が返ってくるため、空だったらfalseを返す
		return (count($results)  > 0);
	}

	function checkPurchased($itemCodes){
		$userId = SOY2Logic::createInstance("site_include.plugin.soyshop_login_check.logic.LoginCheckLogic", array("siteId" => $this->siteId))->getUserId();
		if(!is_numeric($userId)) return false;

		$old = SOYShopUtil::switchShopMode($this->siteId);
		if(!class_exists("SOYShop_Order")) SOY2::import("domain.order.SOYShop_Order");

		$dao = new SOY2DAO();

		$sql = "SELECT distinct(item.item_code) FROM soyshop_item item ".
				"INNER JOIN soyshop_orders orders ".
				"ON item.id = orders.item_id ".
				"INNER JOIN soyshop_order o ".
				"ON orders.order_id = o.id ".
				"WHERE item.item_code IN ('" . implode("', '", $itemCodes) ."') ".
				"AND o.user_id = :userId ".
				"AND o.order_status >= " . SOYShop_Order::ORDER_STATUS_REGISTERED . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"AND o.payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . " ";
		$binds = array(":userId" => $userId);

		try{
			$results = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$results = array();
		}

		SOYShopUtil::resetShopMode($old);

		return (count($results) > 0);
	}

	function getItemDetailPageUrl($itemCode){
		$url = "";

		$old = SOYShopUtil::switchShopMode($this->siteId);

		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getByCode($itemCode);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		if(is_numeric($item->getDetailPageId())){
			try{
				$page = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getById($item->getDetailPageId());
			}catch(Exception $e){
				$page = new SOYShop_Page();
			}

			if(strlen($page->getUri())){
				$url = soyshop_get_page_url($page->getUri()) . "/" . $item->getAlias();
			}
		}

		SOYShopUtil::resetShopMode($old);

		return $url;
	}

	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
}
