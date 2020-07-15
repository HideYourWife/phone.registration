<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Application,
    Bitrix\Main\Loader,
    Bitrix\Main\Engine\ActionFilter\Authentication,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Engine\Contract\Controllerable;
use SmsRu\SmsRuApi;

CJSCore::Init(array("fx", "ajax"));



class simple_registration extends \CBitrixComponent implements Controllerable
{
    private  $componentPage = '';
    private $errors = '';

    public function configureActions()
    {
        // Сбрасываем фильтры по-умолчанию (ActionFilter\Authentication и ActionFilter\HttpMethod)
        // Предустановленные фильтры находятся в папке /bitrix/modules/main/lib/engine/actionfilter/
        return [
            'register' => [ // Ajax-метод
                'prefilters' => [],
            ],
            'sendCode' => [
                'prefilters' => [],
            ],
            'login' => [
                'prefilters' => [],
            ],
            'remember'=> [
                'prefilters' => [],
            ],
            'checkCode'=> [
                'prefilters' => [],
            ],
            'restore_SendCode'=> [
                'prefilters' => [],
            ],
            'restore_CheckCode'=> [
                'prefilters' => [],
            ],
            'restore_UpdateUserPass'=> [
                'prefilters' => [],
            ],
            'userLogout'=> [
                'prefilters' => [],
            ],
        ];
    }

//----------------------------------------------------
//                  REGISTRATION
//----------------------------------------------------

    /**
     * check registration data and put it to session
     * @param array $form - received form data
     * @return array
     */
    public function rememberAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        if (empty($arProps)) return (['STATUS' => 'ERROR', 'MESSAGE' => 'Пустая форма']);

        unset($arProps['repeat_password']);

        // check email correctness
        if (!filter_var($arProps['email'], FILTER_VALIDATE_EMAIL)) {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Неверный формат email']);
        }

        // check password correctness
        $password_check = $this->password_is_secure($arProps['password']);
        if (!empty($password_check)) {
            return $password_check;
        }

        //check name
        if (empty($arProps['name'])) {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Поле имя не должно быть пустым']);
        }

        $_SESSION['REGISTRATION_DATA'] = $arProps;
        return (['STATUS' => 'SUCCESS']);
    }


    /**
     * check phone unique and send checkup code
     * @param array $form - received phone
     * @return array
     */
    public function sendCodeAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        if (empty($arProps))
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Пустая форма']);

        if (!empty($_SESSION['REGISTRATION_DATA']['TIME']) && (time() - $_SESSION['REGISTRATION_DATA']['TIME']) < 90)
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'СМС можно посылать не чаще чем раз в 90 сек.']);

        $check_phone = $this->checkUserPhoneExist($arProps['phone']);

        if ($check_phone) {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Данный телефон уже зарегестрирован в системе!']);
        } else {
            $code = rand(1001, 9999);
            $cured_phone = $this->curePhoneNumber($arProps['phone']);
            $sms_ru = new SmsRuApi($cured_phone);
            $response = $sms_ru->send($code);

            $_SESSION['REGISTRATION_DATA']['phone'] = '+'.$cured_phone;
            $_SESSION['REGISTRATION_DATA']['CODE'] = $code;
            $_SESSION['REGISTRATION_DATA']['TIME'] = time();

            return $response;
        }
    }


    /**
     * check introduced code and register new user if code is correct
     * @param array $form - requested code
     * @return array
     */
    public function checkCodeAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        if ($arProps['code'] == $_SESSION['REGISTRATION_DATA']['CODE']) {
            $user_create_result = $this->newUserRegistration();
            return $user_create_result;
        } else {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Вы ввели неверный код']);
        }
    }



    /**
     * action method for checking and authorize users
     * data come from html form
     * @param array $form form data
     * @return array
    */
    public function loginAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        $phone = $this->curePhoneNumber($arProps['phone'], true);
        $is_correct = $this->checkUserPsw($phone, $arProps['password']);
        if ($is_correct['TYPE'] == 'ERROR') {
            return (['STATUS' => 'ERROR', 'MESSAGE' => $is_correct['MESSAGE']]);
        } else
            return (['STATUS' => 'SUCCESS']);
    }


//----------------------------------------------------
//                  RESTORE PASSWORD
//----------------------------------------------------

    /**
     * restore password start
     * check phone if exist and send checkup code
     * @param array $form - received phone
     * @return array
     */
    public function restore_SendCodeAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        if (empty($arProps))
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Пустая форма']);

        if (!empty($_SESSION['REGISTRATION_DATA']['TIME']) && (time() - $_SESSION['REGISTRATION_DATA']['TIME']) < 90)
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'СМС можно посылать не чаще чем раз в 90 сек.']);

        $user_data = $this->checkUserPhoneExist($arProps['phone']);

        if (!$user_data) {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Данный телефон не зарегестрирован в системе!']);
        } else {
            $code = rand(1001, 9999);

            $cured_phone = $this->curePhoneNumber($arProps['phone']);
            $sms_ru = new SmsRuApi($cured_phone);
            $response = $sms_ru->send($code);

            $_SESSION['REGISTRATION_DATA']['USER'] = $user_data;
            $_SESSION['REGISTRATION_DATA']['CODE'] = $code;
            $_SESSION['REGISTRATION_DATA']['TIME'] = time();

            return $response;
        }
    }


    /**
     * check introduced code
     * @param array $form - requested code
     * @return array
     */
    public function restore_CheckCodeAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }

        if ($arProps['code'] == $_SESSION['REGISTRATION_DATA']['CODE']) {
            $_SESSION['REGISTRATION_DATA']['CODE_VALID'] = true;
            return (['STATUS' => 'SUCCESS']);
        } else {
            $_SESSION['REGISTRATION_DATA']['CODE_VALID'] = false;
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Вы ввели неверный код']);
        }
    }


    /**
     * update user password and authorize them
     * @param array $form - new password
     * @return array
     */
    public function restore_UpdateUserPassAction($form)
    {
        foreach ($form as $item){
            $arProps[$item['name']] = $item['value'];
        }
        global $USER;

        $fields = array(
            "PASSWORD"=>$arProps['password'],
            "CONFIRM_PASSWORD"=>$arProps['password']
        );
        $result = $USER->Update($_SESSION['REGISTRATION_DATA']['USER']['ID'], $fields);

        if ($result) {
            $USER->Authorize($_SESSION['REGISTRATION_DATA']['USER']['ID']);
            unset($_SESSION['REGISTRATION_DATA']);
            return (['STATUS' => 'SUCCESS']);
        } else {
            return (['STATUS' => 'ERROR', 'MESSAGE' => 'Возникла ошибка во время обновления пароля']);
        }
    }

//----------------------------------------------------
//                  UTILITY METHODS
//----------------------------------------------------


    public function userLogoutAction()
    {
        global $USER;
        $USER->Logout();
    }

    /**
     * cure string from unnecessary symbols
     * @param string $number phone number
     * @return string
     */
    private function curePhoneNumber($number, $half=false)
    {
        $phone = preg_replace('/\s+/', '', $number);
        if (!$half)
            $phone = preg_replace("/[^0-9]/", "", $phone );

        return $phone;
    }


    /**
     * check if user can authorize with this password, and authorize them
     * @param string $phone user login
     * @param string $psw password
     * @return boolean
    */
    private function checkUserPsw($phone, $psw)
    {
        $user_data = $this->checkUserPhoneExist($phone);
        $user = new CUser;
        return $user->Login($user_data['LOGIN'], $psw, 'N', 'Y'); //проверяем верный ли пароль
    }


    /**
     * @param string $email user email
     * @return boolean
     */
    private function checkUserEmailExist($email)
    {
        $filter = Array("EMAIL" => $email);
        $by = "NAME";
        $order = "desc";
        $rsUsers = CUser::GetList($by, $order, $filter);
        $arUser = $rsUsers->Fetch();
        if($arUser){
            return true;
        }else{
            return  false;
        }
    }


    /**
     * @param string $login user login
     * @return boolean
     */
    private function checkUserLoginExist($login)
    {
        $rsUser = CUser::GetByLogin($login);
        if ($arUser = $rsUser->Fetch())
        {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param string $phone user phone
     * @return array user data if exist, false if not
     */
    private function checkUserPhoneExist($phone)
    {
        $phone = '+'.$this->curePhoneNumber($phone);
        $filter = Array("PERSONAL_PHONE" => $phone);
        $by = "NAME";
        $order = "desc";
        $rsUsers = CUser::GetList($by, $order, $filter);
        $arUser = $rsUsers->Fetch();
        if($arUser){
            return $arUser;
        }else{
            return  false;
        }
    }


    /**
     * register new user
     * @return array
     */
    private function newUserRegistration()
    {
        global $USER;
        $user = new CUser;
        if (COption::GetOptionString("main","captcha_registration") == 'Y'){
            COption::SetOptionString("main","captcha_registration","N");
        }
        if (COption::GetOptionString("main","new_user_registration_email_confirmation") == 'Y'){
            COption::SetOptionString("main","new_user_registration_email_confirmation","N");
        }
        $fio = htmlspecialchars($_SESSION['REGISTRATION_DATA']['name']) ?? 'noname';
        $email = htmlspecialchars($_SESSION['REGISTRATION_DATA']['email']);
        $phone = htmlspecialchars($_SESSION['REGISTRATION_DATA']['phone']);
        if (!empty($email) && !empty($phone)) {
            $arFields = Array(
                "NAME" => $fio,
                "LAST_NAME" => "",
                "EMAIL" => $email,
                "LOGIN" => $email,
                "PERSONAL_PHONE" => $phone,
                "PHONE_NUMBER" => $phone,
                "LID" => "ru",
                "ACTIVE" => "Y",
                "GROUP_ID" => array(3, 4, 5),
                "PASSWORD" => $_SESSION['REGISTRATION_DATA']['password'],
                "CONFIRM_PASSWORD" => $_SESSION['REGISTRATION_DATA']['password'],
            );
            $USER_ID = $user->Add($arFields);
            if (intval($USER_ID) > 0) {
                unset($_SESSION['REGISTRATION_DATA']);
                $USER->Authorize($USER_ID);
                return (['STATUS' => 'SUCCESS', 'USER_ID' => $USER_ID]);
            } else {
                return (['STATUS' => 'ERROR', 'MESSAGE' => $user->LAST_ERROR]);
            }

        }
        return (['STATUS' => 'ERROR', 'MESSAGE' => 'Потерян телефон либо email пользователя']);
    }

    /**
     * check password
     * @return array
     */
    static function password_is_secure($password) {
        // quick obvious test
        if (is_numeric($password)) {
            $result['STATUS'] = 'ERROR';
            $result['MESSAGE'] = 'Пароль должен содержать хотя бы одну букву';
        } elseif (strlen(count_chars($password, 3)) < 4) {
            $result['STATUS'] = 'ERROR';
            $result['MESSAGE'] = 'Пароль должен быть разнообразен';
        } elseif (strlen($password)<6){
            $result['STATUS'] = 'ERROR';
            $result['MESSAGE'] = 'Пароль должен содержать минимум 6 символов';
        }

        return $result;
    }


    public function executeComponent()
    {
        $this->includeComponentTemplate( $this->componentPage);

    }
}
