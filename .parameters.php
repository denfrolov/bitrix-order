<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
\Bitrix\Main\Loader::includeModule('sale');
$deliveryList = array();

foreach (\Bitrix\Sale\Delivery\Services\Manager::getActiveList() as $arItem) {
	$deliveryList[$arItem["ID"]] = "[" . $arItem["ID"] . "] " . $arItem["NAME"];
}

$dbPaySystem = CSalePaySystemAction::GetList(array(), array('ACTIVE' => 'Y'));
$arPayList = array();
while ($arPay = $dbPaySystem->Fetch()) {
	$arPayList[$arPay["ID"]] = "[" . $arPay["ID"] . "] " . $arPay["NAME"];
	
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"DELIVERY_ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'Доставка по умолчанию',
			"TYPE" => "LIST",
			'VALUES' => $deliveryList
		),
		"PAY_SYSTEM_ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'Метод оплаты по умолчанию',
			"TYPE" => "LIST",
			'VALUES' => $arPayList
		)
	)
);