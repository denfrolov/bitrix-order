<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale,
	Bitrix\Main\Context,
	Bitrix\Currency\CurrencyManager,
	Bitrix\Sale\Order,
	Bitrix\Sale\Basket,
	Bitrix\Sale\Delivery,
	Bitrix\Sale\PaySystem;


// Джигурда

class dfSaleOrder extends CBitrixComponent
{
	function getAvailablePaySystems($order): array
	{
		$payment = Sale\Payment::create($order->getPaymentCollection());
		$payment->setField('SUM', $order->getPrice());
		$payment->setField('CURRENCY', $order->getCurrency());
		
		$paySystemList = PaySystem\Manager::getListWithRestrictions($payment);
		foreach ($paySystemList as $key => $paySystem) {
			$objPay = new PaySystem\Service($paySystem);
			$arPay = $objPay->getFieldsValues();
			$paySystemList[$key] = $arPay;
		}
		
		return $paySystemList;
	}
	
	function basketUpdate($id, $quantity)
	{
		$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
		if ($item = $basket->getItemById($id)) {
			$item->setField('QUANTITY', $quantity);
			$item->save();
		}
	}
	
	function basketDelete($id)
	{
		$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
		$basket->getItemById($id)->delete();
		$basket->save();
	}
	
	function dfOrder($arParams)
	{
		/**
		 * @var array $arParams
		 * @var array $arResult
		 */
		global $USER;
		Bitrix\Main\Loader::includeModule("sale");
		Bitrix\Main\Loader::includeModule("catalog");
		
		if ($_REQUEST['METHOD'] == 'UPDATE' && $_REQUEST['ID'] && $_REQUEST['QUANTITY']) {
			$this->basketUpdate($_REQUEST['ID'], $_REQUEST['QUANTITY']);
		}
		if ($_REQUEST['METHOD'] == 'DELETE' && $_REQUEST['ID']) {
			$this->basketDelete($_REQUEST['ID']);
		}


//todo: Оформление заказа
		
		
		$siteId = Context::getCurrent()->getSite();
		$currencyCode = CurrencyManager::getBaseCurrency();

// Создаёт новый заказ
		$order = Order::create($siteId, $USER->isAuthorized() ? $USER->GetID() : 539);
		$order->setPersonTypeId(1);
		$order->setField('CURRENCY', $currencyCode);


// todo: Корзина
		$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
		$order->setBasket($basket);
		
		foreach ($basket->getBasketItems() as $basketItem) {
			$arBasketItem = array();
			$arBasketItem['QUANTITY'] = $basketItem->getQuantity();
			$arBasketItem['PRICE'] = $basketItem->getPrice();
			$arBasketItem['BASE_PRICE'] = $basketItem->getBasePrice();
			$arBasketItem['PRODUCT_ID'] = $basketItem->getProductId();
			$arBasketItem['DETAIL_PAGE_URL'] = $basketItem->getField('DETAIL_PAGE_URL');
			$arOrder = array("SORT" => "ASC");
			$arFilter = array("ACTIVE" => "Y", "ID" => $arBasketItem['PRODUCT_ID']);
			$arSelectFields = array("IBLOCK_ID", "ID", "ACTIVE", "NAME", 'PREVIEW_PICTURE', 'DETAIL_PICTURE');
			$rsElements = CIBlockElement::GetList($arOrder, $arFilter, FALSE, FALSE, $arSelectFields);
			if ($arElementObj = $rsElements->GetNextElement()) {
				$arElement = $arElementObj->GetFields();
				$imgID = 0;
				if ($arElement['PREVIEW_PICTURE']) {
					$imgID = $arElement['PREVIEW_PICTURE'];
				} elseif ($arElement['DETAIL_PICTURE']) {
					$imgID = $arElement['DETAIL_PICTURE'];
				}
				$arImg = CFile::ResizeImageGet($imgID, array("width" => 160, "height" => 160), null);
				if ($arImg) {
					$arElement['PREVIEW_PICTURE_SRC'] = $arImg["src"];
				}
				$arElement['PROPERTIES'] = $arElementObj->GetProperties();
				$arBasketItem = array_merge($arElement, $arBasketItem);
			}
			$measure = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($arBasketItem['PRODUCT_ID']);
			$arBasketItem['MEASURE_RATIO'] = $measure[$arBasketItem['PRODUCT_ID']]['RATIO'];
			$arResult['BASKET_ITEMS'][] = $arBasketItem;
		}


// todo: Отгрузка
		$arResult['DELIVERY_ID'] = ($_REQUEST['DELIVERY_ID'] ? $_REQUEST['DELIVERY_ID'] : $arParams['DELIVERY_ID']);
		$arResult['DELIVERY_LIST'] = Delivery\Services\Manager::getActiveList();
		unset($arResult['DELIVERY_LIST'][Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId()]);
		$arResult['DELIVERY_LIST'][$arResult['DELIVERY_ID']]['CHECKED'] = true;
		if ($arResult['DELIVERY_ID']) {
			$shipmentCollection = $order->getShipmentCollection();
			$shipment = $shipmentCollection->createItem();
			$service = Delivery\Services\Manager::getById($arResult['DELIVERY_ID']);
			$shipment->setFields(array(
				'DELIVERY_ID' => $service['ID'],
				'DELIVERY_NAME' => $service['NAME'],
			));
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			foreach ($order->getBasket() as $item) {
				$shipmentItem = $shipmentItemCollection->createItem($item);
				$shipmentItem->setQuantity($item->getQuantity());
			}
		}

// todo: Оплата
		
		$arResult['PAY_SYSTEM_ID'] = ($_REQUEST['PAY_SYSTEM_ID'] ? $_REQUEST['PAY_SYSTEM_ID'] : $arParams['PAY_SYSTEM_ID']);
		$arResult['PAY_SYSTEM_LIST'] = $this->getAvailablePaySystems($order);
		$arResult['PAY_SYSTEM_LIST'][$arResult['PAY_SYSTEM_ID']]['CHECKED'] = true;
		if ($arResult['PAY_SYSTEM_ID']) {
			$paymentCollection = $order->getPaymentCollection();
			$payment = $paymentCollection->createItem();
			$paySystemService = PaySystem\Manager::getObjectById($arResult['PAY_SYSTEM_ID']);
			$payment->setFields(array(
				'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
				'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
			));
		}
		
		$order->doFinalAction(true);
		$propertyCollection = $order->getPropertyCollection();
		
		$arResult['SUM'] = $order->getPrice();
		$arResult['BASE_SUM'] = $order->getBasePrice();
		$arResult['PRODUCTS_PRICE'] = $order->getPrice() - $order->getDeliveryPrice();
		$arResult['PRODUCTS_BASE_PRICE'] = $order->getBasePrice() - $order->getDeliveryPrice();
		$arResult['DELIVERY_PRICE'] = $order->getDeliveryPrice();
		
		if ($USER->IsAuthorized()) {
			$dbSaleUserProfiles = CSaleOrderUserProps::GetList(array(), array('USER_ID' => $USER->GetID()));
			if (!$dbSaleUserProfiles->SelectedRowsCount()) {
				$arFields = array(
					"NAME" => "Профиль 1",
					"USER_ID" => $USER->GetID(),
					"PERSON_TYPE_ID" => 1
				);
				CSaleOrderUserProps::Add($arFields);
			}
			$saleUserProfiles = $dbSaleUserProfiles->Fetch();
		}
		
		
		foreach ($propertyCollection->getGroups() as $group) {
			foreach ($propertyCollection->getGroupProperties($group['ID']) as $property) {
				$p = $property->getProperty();
				
				if ($_REQUEST['PROPERTIES'][$p['CODE']] && $p['TYPE'] != 'FILE') {
					$property->setValue($_REQUEST['PROPERTIES'][$p['CODE']]);
				}
				if ($p['TYPE'] == 'FILE') {
					$fileArray = array(
						'name' => $_FILES['PROPERTIES']['name'][$p['CODE']],
						'size' =>  $_FILES['PROPERTIES']['size'][$p['CODE']],
						'tmp_name' => $_FILES['PROPERTIES']['tmp_name'][$p['CODE']],
						'type' => $_FILES['PROPERTIES']['type'][$p['CODE']],
					);
					$property->setValue(CFile::SaveFile($fileArray, "orders"));
				}
				if ($p['REQUIRED'] == 'Y' && !$_REQUEST['PROPERTIES'][$p['CODE']] && $_REQUEST['save'] == 'y') {
					$arResult['ERRORS'][$p['CODE']] = "Не заполнено поле {$p['NAME']}";
				}
				
				$p['FORM_NAME'] = "PROPERTIES[{$p['CODE']}]";
				if (stripos($p['CODE'], 'phone') !== false) {
					$p['HTML_TYPE'] = 'tel';
				} elseif (stripos($p['CODE'], 'email') !== false) {
					$p['HTML_TYPE'] = 'email';
				} elseif (stripos($p['CODE'], 'hidden') !== false) {
					$p['HTML_TYPE'] = 'hidden';
				} elseif ($p['TYPE'] == 'FILE') {
					$p['HTML_TYPE'] = 'file';
				} else {
					$p['HTML_TYPE'] = 'text';
				}
				$p['VALUE'] = $_REQUEST['PROPERTIES'][$p['CODE']];
				
				if ($USER->IsAuthorized()) {
					if ($_REQUEST['PROPERTIES'][$p['CODE']]) {
						$fields = array(
							"USER_PROPS_ID" => $saleUserProfiles['ID'],
							"ORDER_PROPS_ID" => $p['ID'],
							"NAME" => $p['NAME'],
							"VALUE" => $_REQUEST['PROPERTIES'][$p['CODE']]
						);
					}
					$dbSaleUserProps = CSaleOrderUserPropsValue::GetList(array(), array('USER_PROPS_ID' => $saleUserProfiles['ID'], 'ORDER_PROPS_ID' => $p['ID']));
					if ($arSaleUserProps = $dbSaleUserProps->Fetch()) {
						if ($_REQUEST['PROPERTIES'][$p['CODE']]) {
							CSaleOrderUserPropsValue::Update($arSaleUserProps['ID'], $fields);
						} else {
							$p['VALUE'] = $arSaleUserProps['VALUE'];
						}
					} else {
						if ($_REQUEST['PROPERTIES'][$p['CODE']]) {
							CSaleOrderUserPropsValue::Add($fields);
						}
					}
				}
				$arResult['PROPERTIES'][$p['CODE']] = $p;
			}
		}
		
		
		if ($_REQUEST['USER_DESCRIPTION']) {
			$order->setField('USER_DESCRIPTION', $_REQUEST['USER_DESCRIPTION']);
		}
		
		if ($_REQUEST['save'] == 'y' && $arResult['BASKET_ITEMS'] && !$arResult['ERRORS']) {
			$result = $order->save();
			if ($result->isSuccess()) {
				$arResult['ORDER_ID'] = $order->getId();
			}
		}
		
		return $arResult;
	}
	
	public function executeComponent()
	{
		$this->arResult = array_merge($this->arResult, $this->dfOrder($this->arParams));
		$this->includeComponentTemplate();
	}
}