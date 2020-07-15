<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Page\Asset;
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var array $arResult */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

?>


<!-- Модальное окно авторизации -->
<div id="modal-auth" class="modal modal-auth">
    <div class="modal__title">
        Вход в личный&nbsp;кабинет
    </div>
    <div class="modal__form modal-auth__form">
        <form action="" id="form_login">
            <span class="tq_error"></span>
            <div class="form-group">
                <div class="form-box">
                    <input type="text" name="phone" class="form-control form-box__input mask-phone" id="login_phone" required>
                    <label for="login_phone" class="form-box__label"><span>Номер телефона</span></label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-box">
                    <input type="password" name="password" class="form-control form-box__input" id="login_password" required>
                    <label for="login_password" class="form-box__label"><span>Пароль</span></label>
                </div>
            </div>
            <div class="form-group form-button">
                <button type="submit" class="btn btn--full btn-submit">Войти на сайт</button>
            </div>
            <div class="form-group form-links">
                <a href="#modal-register" data-modal>Зарегистрироваться</a>
                <a href="#modal-password-recovery" data-modal>Забыли пароль?</a>
            </div>
        </form>
    </div>
</div>


<!-- Модальное окно регистарции -->
<div id="modal-register" class="modal modal-register">
    <div class="modal__title">
        Регистрация
    </div>
    <div class="modal__form form-register modal-register__form">
        <div class="form-register__step form-register__step--1 active">
            <form action="" id="form_register_1">
                <span class="tq_error"></span>
                <div class="form-group">
                    <div class="form-box">
                        <input type="text" name="name" class="form-control form-box__input" id="register_name" required>
                        <label for="register_name" class="form-box__label"><span>Имя и фамилия</span></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-box">
                        <input type="email" name="email" class="form-control form-box__input" id="register_email" required>
                        <label for="register_email" class="form-box__label"><span>Электронная почта</span></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-box">
                        <input type="password" name="password" class="form-control form-box__input" id="register_password" required>
                        <label for="register_password" class="form-box__label"><span>Введите пароль</span></label>
                        <div class="state-password" id="register_password_state">
                            <div class="state-password__line"></div>
                            <div class="state-password__text">Слабый</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-box">
                        <input type="password" name="repeat_password" class="form-control form-box__input" id="register_repeat_password" required>
                        <label for="register_repeat_password" class="form-box__label"><span>Повторите пароль</span></label>
                    </div>
                </div>
                <div class="form-group form-button">
                    <button type="submit" class="btn btn--full" id="register_btn_next">Зарегистрироваться</button>
                </div>
                <div class="form-group form-agreement">
                    Нажимая на кнопку «Зарегистрироваться», вы даете
                    согласие на обработку личных данных в соответствии c
                    политикой конфиденциальности,
                </div>
            </form>
        </div>
        <div class="form-register__step form-register__step--2">

            <form action="" id="form_register_2">
                <span class="tq_error"></span>
                <div class="form-register__text form-register__text--phone active">
                    Введите номер телефона и мы пришлем вам код подтверждения
                </div>
                <div class="form-register__text form-register__text--code">
                    Введите четырехзначный код подтверждения, отправленный по&nbsp;указанному номеру телефона
                </div>
                <div class="form-group">
                    <div class="form-box">
                        <input type="text" name="phone" class="form-control form-box__input mask-phone" id="register_phone" required>
                        <label for="register_phone" class="form-box__label"><span>Номер телефона</span></label>
                    </div>
                </div>
                <div class="form-group form-button form-register__button-code">
                    <button type="button" class="btn btn--full" id="register_btn_code">Получить код</button>
                </div>
                <div class="form-register__code">
                    <span class="tq_error"></span>
                    <div class="form-group">
                        <div class="form-box">
                            <input type="text" name="code" class="form-control form-box__input mask-code" id="register_code" required>
                            <label for="register_code" class="form-box__label"><span>Четырёхзначный код</span></label>
                        </div>
                    </div>
                    <div class="form-group form-register__code-text">
                        Выслать повторно можно через <span class="form-register__code-time"></span>
                    </div>
                    <div class="form-group form-register__code-resend" style="display: none">
                        <a href="javascript:void(0)" id="register_btn_resend">Выслать код повторно</a>
                    </div>
                    <div class="form-group form-button form-register__button-confirm">
                        <button type="submit" class="btn btn--full" id="register_btn_confirm">Подтвердить</button>
                    </div>
                    <div class="form-group form-links">
                        <a href="javascript:void(0)" class="tooltip" data-tooltip-html='<div class="tooltip-title">Возможные причины</div>
                    <div class="tooltip-text">
                      <p>1. Вы не представили в банк новый номер мобильного телефона и СМС было отправлено на старый.</p>
                      <p>2. Проблемы могут быть со стороны сотового оператора. Обратитесь в службу поддержки</p></div>'  title="1. Вы не представили в банк новый номер мобильного телефона и СМС было отправлено на старый.">Не приходит СМС</a>
                        <a href="javascript:void(0)" class="form-register__return-link" id="register_btn_return">Неправильный номер телефона</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно восстановления пароля -->
<div id="modal-password-recovery" class="modal modal-password-recovery">
    <div class="modal__title">
        Восстановление пароля
    </div>
    <div class="modal__form modal-auth__form form-recovery">

        <div class="form-recovery__text form-recovery__text--phone">
            Введите номер телефона и мы пришлем вам код подтверждения
        </div>
        <div class="form-recovery__text form-recovery__text--code" style="display:none;">
            Введите четырехзначный код подтверждения, отправленный по&nbsp;указанному номеру телефона
        </div>
        <form action="#" id="form_recovery_phone">
            <span class="tq_error"></span>
            <div class="form-recovery__number">
                <div class="form-group ">
                    <div class="form-box">
                        <input type="text" name="phone" class="form-control form-box__input mask-phone js__phone" id="recovery_phone" required>
                        <label for="recovery_phone" class="form-box__label"><span>Номер телефона</span></label>
                    </div>
                </div>
                <div class="form-group form-button form-recovery__number-button">
                    <button type="button" class="btn btn--full btn-submit" id="form_recovery_continue">Получить код</button>
                </div>
            </div>
            <div class="form-recovery__code" style="display: none">
                <div class="form-group">
                    <div class="form-box">
                        <input type="text" name="code" class="form-control form-box__input mask-code" id="recovery_code" required>
                        <label for="recovery_code" class="form-box__label"><span>Четырёхзначный код</span></label>
                    </div>
                </div>
                <div class="form-group form-recovery__resend">
                    <div class="form-recovery__resend-text">
                        Выслать повторно можно через <span class="form-recovery__code-time"></span>
                    </div>
                    <div class="form-group form-recovery__resend-button" style="display: none">
                        <a href="javascript:void(0)" id="form_recovery_resend">Выслать код повторно</a>
                    </div>
                </div>
                <div class="form-group form-button form-recovery__confirm">
                    <button type="submit" class="btn btn--full" id="form_recovery_confirm">Подтвердить</button>
                </div>
                <div class="form-group form-links">
                    <a href="javascript:void(0)" class="tooltip" data-tooltip-html='<div class="tooltip-title">Возможные причины</div>
                    <div class="tooltip-text">
                      <p>1. Вы не представили в банк новый номер мобильного телефона и СМС было отправлено на старый.</p>
                      <p>2. Проблемы могут быть со стороны сотового оператора. Обратитесь в службу поддержки</p></div>'  title="1. Вы не представили в банк новый номер мобильного телефона и СМС было отправлено на старый.">Не приходит СМС</a>
                    <a href="javascript:void(0)" class="form-recovery__return" id="form_recovery_return">Неправильный номер телефона</a>
                </div>
            </div>
        </form>

        <div class="form-recovery__password" style="display: none">
            <form action="#" id="form_recovery_password">
                <span class="tq_error"></span>
                <div class="form-group">
                    <div class="form-box">
                        <input type="password" name="password" class="form-control form-box__input" id="recovery_password" required>
                        <label for="recovery_password" class="form-box__label"><span>Введите новый пароль</span></label>
                        <div class="state-password" id="recovery_password_state">
                            <div class="state-password__row">
                                <div class="state-password__line"></div>
                                <div class="state-password__text">Слабый</div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-box">
                        <input type="password" name="repeat_password" class="form-control form-box__input" id="recovery_repeat_password" required>
                        <label for="recovery_repeat_password" class="form-box__label"><span>Повторите пароль</span></label>
                    </div>
                </div>
                <div class="form-group form-button">
                    <button type="submit" class="btn btn--full btn-submit" id="form_recovery_submit">Восстановить пароль</button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Модальное окно Спасибо -->
<div id="modal-thanks" class="modal modal-thanks">
    <div class="modal__icon">
        <svg class="icon icon-check"><use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons.svg#icon-check"/></svg>
    </div>
    <div class="modal__title">
        Спасибо за регистрацию
    </div>
    <div class="modal__text">
        Ваши данные высланы на указанную почту
    </div>
    <div class="modal__button">
        <a href="/personal/" class="btn">Перейти в личный кабинет</a>
    </div>
</div>