<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 */


use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\Declension;

?>

<? if ($arResult['ORDER_ID']): ?>
	<div class="text-center fw-bold"> Заказ №<?= $arResult['ORDER_ID'] ?> оформлен, с вами свяжутся, ожидайте!</div>
<? elseif (!$arResult['BASKET_ITEMS']): ?>
	<div class="basket">
		<div class="basket__text my-5">
			В Вашей корзине нет товаров
		</div>
	</div>
<?php else: ?>
	<div class="basket">
		<div class="row justify-content-between">
			<div class="col-lg-7">
				<? foreach ($arResult['BASKET_ITEMS'] as $i => $arBasketItem): ?>
					<div class="js-basket-item" data-id="<?= $arBasketItem['ID'] ?>">
						<div class="row align-items-md-center">
							<div class="col-auto col-sm-3">
								<a href="<?= $arBasketItem['DETAIL_PAGE_URL'] ?>" class="item-basket__img">
									<img src="<?= $arBasketItem['PREVIEW_PICTURE_SRC'] ?>" alt="<?= $arBasketItem['NAME'] ?>">
								</a>
							</div>
							<div class="col col-sm-9">
								<div class="row align-items-center">
									<div class="col-md col-lg-7 col-xxl-6 mb-4 mb-md-0">
										<div class="item-basket__body">
											<a href="<?= $arBasketItem['DETAIL_PAGE_URL'] ?>" class="item-basket__name">
												<?= $arBasketItem['NAME'] ?>
											</a>
											<div class="item-basket__text">
												<?= CurrencyFormat($arBasketItem['PRICE'], 'RUB') ?>
											</div>
										</div>
									</div>
									<div class="col-md-auto col-lg-5 col-xxl-6">
										<div class="row align-items-center g-3 g-sm-4">
											<div class="col col-lg">
												<div class="item-quantity">
													<div class="item-quantity__minus js-quantity-button">
														<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
														     xmlns="http://www.w3.org/2000/svg">
															<path d="M14.5 10L5.5 10" stroke="#3D3D3D" stroke-linecap="round"/>
														</svg>
													</div>
													<input type="text" class="item-quantity__value js-quantity-input"
													       value="<?= $arBasketItem['QUANTITY'] ?>" readonly>
													<div class="item-quantity__plus js-quantity-button">
														<svg width="20" height="20" viewBox="0 0 20 20" fill="none"
														     xmlns="http://www.w3.org/2000/svg">
															<path d="M10 5.5V14.5" stroke="#3D3D3D" stroke-linecap="round"/>
															<path d="M14.5 10L5.5 10" stroke="#3D3D3D" stroke-linecap="round"/>
														</svg>
													</div>
												</div>
											</div>
											<div class="col-auto">
												<div class="item-basket__delete js-basket-item-remove">
													<svg width="24" height="24" viewBox="0 0 24 24" fill="none"
													     xmlns="http://www.w3.org/2000/svg">
														<path
															d="M7.72266 9.44443C7.72266 9.16829 7.4988 8.94443 7.22266 8.94443C6.94651 8.94443 6.72266 9.16829 6.72266 9.44443H7.72266ZM16.2782 9.44443C16.2782 9.16829 16.0544 8.94443 15.7782 8.94443C15.5021 8.94443 15.2782 9.16829 15.2782 9.44443H16.2782ZM6.72266 9.44443V16.6111H7.72266V9.44443H6.72266ZM9.22266 19.1111H13.7782V18.1111H9.22266V19.1111ZM16.2782 16.6111V9.44443H15.2782V16.6111H16.2782ZM13.7782 19.1111C15.1589 19.1111 16.2782 17.9918 16.2782 16.6111H15.2782C15.2782 17.4395 14.6066 18.1111 13.7782 18.1111V19.1111ZM6.72266 16.6111C6.72266 17.9918 7.84194 19.1111 9.22266 19.1111V18.1111C8.39423 18.1111 7.72266 17.4395 7.72266 16.6111H6.72266Z"
															fill="#3D3D3D"/>
														<path d="M6 8.83331H17" stroke="#3D3D3D" stroke-linejoin="bevel"/>
														<path d="M13.0273 11.8889V15.5555" stroke="#3D3D3D" stroke-linejoin="bevel"/>
														<path d="M9.97266 11.8889V15.5555" stroke="#3D3D3D" stroke-linejoin="bevel"/>
														<path
															d="M8.52148 8.35802C8.52148 8.63417 8.74534 8.85802 9.02148 8.85802C9.29763 8.85802 9.52148 8.63417 9.52148 8.35802H8.52148ZM13.2746 8.35802C13.2746 8.63417 13.4984 8.85802 13.7746 8.85802C14.0507 8.85802 14.2746 8.63417 14.2746 8.35802H13.2746ZM9.52148 8.35802V8H8.52148V8.35802H9.52148ZM10.0215 7.5H12.7746V6.5H10.0215V7.5ZM13.2746 8V8.35802H14.2746V8H13.2746ZM12.7746 7.5C13.0507 7.5 13.2746 7.72386 13.2746 8H14.2746C14.2746 7.17157 13.603 6.5 12.7746 6.5V7.5ZM9.52148 8C9.52148 7.72386 9.74534 7.5 10.0215 7.5V6.5C9.19306 6.5 8.52148 7.17157 8.52148 8H9.52148Z"
															fill="#3D3D3D"/>
													</svg>
													Удалить
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<? endforeach; ?>
				<div class="basket__btn d-md-none">
					<a href="#order-basket" data-fancybox class="btn btn-primary">Оформить заказ</a>
				</div>
			</div>

			<div class="col-lg-4">
				<form action="" method="post" class="js-ajax-block">
					<input type="hidden" name="save" value="n">
					<? foreach ($arResult['PROPERTIES'] as $i => $arItem): ?>
						<div class="form-group mb-4">
							<span class="text-danger js-danger"></span>
							<? if ($arItem['TYPE'] == 'ENUM'): ?>
								<select name="<?= $arItem['FORM_NAME'] ?>" class="js-select">
									<option value=" " selected disabled><?= $arItem['NAME'] ?></option>
									<? foreach ($arItem['OPTIONS'] as $i2 => $arOption): ?>
										<option
											value="<?= $i2 ?>" <?= $arItem['VALUE'] == $i2 ? 'selected' : '' ?> ><?= $arOption ?></option>
									<? endforeach; ?>
								</select>
							<?php else: ?>
								<input type="<?= $arItem['HTML_TYPE'] ?>" class="form-control" name="<?= $arItem['FORM_NAME'] ?>"
								       placeholder="<?= $arItem['NAME'] ?>"
								       value="<?= $arItem['VALUE'] ?>" <?= $arItem['REQUIRED'] == 'Y' ? 'required' : '' ?> >
							<? endif; ?>
						</div>
					<? endforeach; ?>
					<div class="form-group mb-4">
						<textarea name="USER_DESCRIPTION" class="form-control" placeholder="Сообщение"></textarea>
					</div>
					<div class="form-group mb-4">
						<div class="main-checkbox">
							<label>
								<input type="checkbox" name="confirm" required="" data-orig-tabindex="null" tabindex="-1" checked>
								<span>
          <svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M9.70718 1.70712L3.50008 7.91423L0.292969 4.70712L1.70718 3.29291L3.50008 5.0858L8.29297 0.292908L9.70718 1.70712Z"
                  fill="#E52330"></path>
          </svg>
        </span>
							</label>
							<span>Я даю согласие с <a href="" tabindex="0">условиями обработки персональных данных</a></span>
						</div>
					</div>
					<button type="submit" class="btn btn-primary w-100 js-add-order">Отправить</button>
				</form>
			</div>

		</div>
		<div class="js-ajax-block-total"></div>
	</div>
<? endif; ?>
