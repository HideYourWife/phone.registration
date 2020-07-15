$(document).ready(function() {


    /* Добавление методов в валидации */

    $.validator.addMethod("regexpName", function(value, element) {
        return /^\s*[A-Za-zА-Яа-я]+\s+[A-Za-zА-Яа-я]+/.test(value);
    }, "Это поле должно содержать Имя и Фамилию!");


    $.validator.addMethod("regexpPassword", function(value, element) {
        return /[A-Za-zА-Яа-я]+/.test(value);
    }, "Пароль должен содержать минимум 6 символов и 1 букву!");



    /* Валидация формы входа */

    if ($("#form_login").length){
        var formLogin = $("#form_login");
        formLogin.validate({
            rules: {
                phone: {
                    minlength: 18,
                },
                password: {
                    //minlength: 6
                    //regexpPassword: true

                }
            },
            messages: {
                phone: {
                    required: "Введите телефон!",
                    minlength: "Введите телефон!"
                },
                password: {
                    required: "Введите пароль!"
                }
            },
            submitHandler: function(form) {
                BX.ajax.runComponentAction('grin:tq_registration',
                    'login', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $(form).serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    })
                    .then(function (response) {
                        if (response.data.STATUS === 'SUCCESS') {
                            $('#form_login').find('.tq_error').hide();
                            window.location.href = '/personal/';
                        } else {
                            $('#form_login .tq_error').html(response.data.MESSAGE).show();
                        }
                    });
            }
        });
    }

//------------------------------------------------------
//                PASSWORD RECOVERY
//------------------------------------------------------

    /* Валидация формы восстановления пароля */

    if ($("#form_recovery_phone").length){
        var formRecoveryPhone = $("#form_recovery_phone");
        formRecoveryPhone.validate({
            rules: {
                phone: {
                    minlength: 18
                },
                code: {
                    minlength: 4
                }
            },
            messages: {
                phone: {
                    required: "Введите телефон!",
                    minlength: "Введите телефон!"
                },
                code: {
                    required: "Введите четырёхзначный код!",
                    minlength: "Введите четырёхзначный код!"
                }
            },
            submitHandler: function(form) {

                BX.ajax.runComponentAction('grin:tq_registration',
                    'restore_CheckCode', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $(form).serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    }).then(function (response) {
                    if (response.data.STATUS === 'SUCCESS') {
                        $('#form_recovery_phone .tq_error').hide();
                        var $enterNumber = $(".form-recovery__number"),
                            $textEnterNumber = $(".form-recovery__text--phone"),
                            $textEnterCode = $(".form-recovery__text--code"),
                            $enterCode = $(".form-recovery__code"),
                            $enterPassword = $(".form-recovery__password");

                        $enterNumber.hide();
                        $textEnterNumber.hide();
                        $textEnterCode.hide();
                        $enterCode.hide();
                        $enterPassword.show();

                    } else {
                        $('#form_recovery_phone .tq_error').html(response.data.MESSAGE).show();
                    }
                })
            }
        });

        var formRecoveryPassword = $("#form_recovery_password");
        formRecoveryPassword.validate({
            rules: {
                password: {
                    minlength: 6,
                    regexpPassword: true
                },
                repeat_password: {
                    equalTo : "#recovery_password"
                }
            },
            messages: {
                password: {
                    minlength: "Пароль должен содержать минимум 6 символов и 1 букву!",
                    regexpPassword: "Пароль должен содержать минимум 6 символов и 1 букву!"
                },
                repeat_password: {
                    equalTo: "Пароли не совпадают!"
                }
            },
            submitHandler: function(form) {
                BX.ajax.runComponentAction('grin:tq_registration',
                    'restore_UpdateUserPass', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $(form).serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    }).then(function (response) {
                    if (response.data.STATUS === 'SUCCESS') {
                        $('#form_recovery_password .tq_error').hide();
                        window.location.href = '/personal/';
                    } else {
                        $('#form_recovery_password .tq_error').html(response.data.MESSAGE).show();
                    }
                })
            }
        });

        /* Проверка пароля на сложность */
        $("#recovery_password").on("input change", function(){
            checkPassword($(this), $("#recovery_password_state"));
        });

        /* Добавление маски четырехзначному коду */
        $('#recovery_code').mask('0000');

        var recoveryTimer = null, // Таймер
            recoveryTimerSeconds = 60; // Количество секунд

        /* Отправляем телефон, получаем код */
        $(document).on("click", "#form_recovery_continue", function(){
            var $this = $(this),
                $textEnterNumber = $(".form-recovery__text--phone"),
                $textEnterCode = $(".form-recovery__text--code"),
                $enterCode = $(".form-recovery__code"),
                $codeResendText = $(".form-recovery__resend-text"),
                $codeResendButton = $(".form-recovery__resend-button"),
                $enterCodeTimer = $(".form-recovery__code-time");

            if (formRecoveryPhone.valid()){
                BX.ajax.runComponentAction('grin:tq_registration',
                    'restore_SendCode', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $('.js__phone').serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    }).then(function (response) {
                        if (response.data.STATUS === 'SUCCESS') {
                            startTimerResendRecovery(recoveryTimerSeconds, $enterCodeTimer, $codeResendText, $codeResendButton, 'секунду', 'секунды', 'секунд');

                            $(".form-recovery__number-button").hide();
                            $('#form_recovery_phone .tq_error').hide();
                            $codeResendButton.hide();
                            $textEnterNumber.hide();

                            $enterCode.show();
                            $textEnterCode.show();
                            $codeResendText.show();

                            $('#recovery_code').val('');
                        } else {
                            $('#form_recovery_phone .tq_error').html(response.data.MESSAGE).show();
                        }
                    })
                }
        });

        /* Вернуться к набору номера телефона */
        $(document).on("click", "#form_recovery_return", function(){
            var $textEnterNumber = $(".form-recovery__text--phone"),
                $textEnterCode = $(".form-recovery__text--code"),
                $enterCode = $(".form-recovery__code");

            $textEnterCode.hide();
            $enterCode.hide();

            $(".form-recovery__number-button").show();
            $textEnterNumber.show();

            $('#recovery_code').val('');
        });

        /* Выслать код повторно */
        $(document).on("click", "#form_recovery_resend", function(){
            var $codeResendText = $(".form-recovery__resend-text"),
                $codeResendButton = $(".form-recovery__resend-button"),
                $enterCodeTimer = $(".form-recovery__code-time");

            $codeResendText.show();
            $codeResendButton.hide();

            startTimerResendRecovery(recoveryTimerSeconds, $enterCodeTimer, $codeResendText, $codeResendButton, 'секунду', 'секунды', 'секунд');
        });


        /* Функция запуска таймера повторной отправки кода в форме восстановления пароля
            /---------------------------/
                time - время (в секундах)
                element - html элемент вывода времени
                resendText - html элемент сообщения повторной отправки
                resendBtn - html элемент кнопки для повторной отправки
                word1, word2, word3 - наименования числительных для вывода чисел в правильном окончании
            /---------------------------/
        */
        function startTimerResendRecovery(time, element, resendText, resendBtn, word1, word2, word3){

            var localTime = time;

            if (recoveryTimer){
                clearInterval(recoveryTimer);
            }
            element.text(getWord(localTime, word1, word2, word3));
            recoveryTimer = setInterval(function () {

                localTime--;

                if (localTime < 0) {
                    clearInterval(recoveryTimer);
                    resendText.hide();
                    resendBtn.show();
                }
                else {
                    element.text(getWord(localTime, word1, word2, word3));
                }

            }, 1000);
        }
    }



//----------------------------------------------------
            /* Валидация формы регистрации */
//----------------------------------------------------

    if ($(".modal-register__form").length){


        var formRegistration1 = $("#form_register_1");

        formRegistration1.validate({

            rules: {
                name: {
                    regexpName: true
                },
                password: {
                    minlength: 6,
                    regexpPassword: true

                },
                repeat_password: {
                    equalTo : "#register_password"
                }
            },
            messages: {
                name: {
                    regexpName: "Это поле должно содержать Имя и Фамилию!"
                },
                password: {
                    minlength: "Пароль должен содержать минимум 6 символов и 1 букву!",
                    regexpPassword: "Пароль должен содержать минимум 6 символов и 1 букву!"
                },
                repeat_password: {
                    equalTo: "Пароли не совпадают!"
                }
            },
            submitHandler: function(form) {
                // Переход ко 2 форме
                BX.ajax.runComponentAction('grin:tq_registration',
                    'remember', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $(form).serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    })
                    .then(function (response) {
                        if (response.data.STATUS === 'SUCCESS') {
                            $('#form_register_1 .tq_error').hide();
                            $(".form-register__step--1").removeClass("active");
                            $(".form-register__step--2").addClass("active");
                        } else {
                            $('#form_register_1 .tq_error').html(response.data.MESSAGE).show();
                        }
                    });
            }
        });


        // проверка введеного кода
        var formRegistration2 = $("#form_register_2");

        formRegistration2.validate({

            rules: {
                phone: {
                    minlength: 18
                },
                code: {
                    minlength: 4
                }
            },
            messages: {
                phone: {
                    required: "Введите номер телефона!",
                    minlength: "Введите номер телефона!"
                },
                code: {
                    required: "Введите четырёхзначный код!",
                    minlength: "Введите четырёхзначный код!"
                }
            },
            submitHandler: function(form) {
                BX.ajax.runComponentAction('grin:tq_registration',
                    'checkCode', { // Вызывается без постфикса Action
                        mode: 'class',
                        data: {form: $(form).serializeArray()}, // ключи объекта data соответствуют параметрам метода
                    })
                    .then(function (response) {
                        if (response.data.STATUS === 'SUCCESS') {
                            $('#form_register_2 .tq_error').hide();

                            // Открытие модалки "Спасибо"
                            $("#modal-thanks").modal({
                                clickClose: false
                            });
                        } else {
                            $('.form-register__code .tq_error').html(response.data.MESSAGE).show();
                        }
                    });
            }
        });



        /* Добавление маски четырехзначному коду */
        $('#register_code').mask('0000');

        var timer = null, // Таймер
            timerSeconds = 120; // Количество секунд

        /* Получить код*/
        $(document).on("click", "#register_btn_code", function(){
            if (formRegistration2.valid()){
                sendPhone();
            }
        });


        /* send phone data */
        function sendPhone() {
            var $textEnterNumber = $(".form-register__text--phone"),
                $textEnterCode = $(".form-register__text--code"),
                $enterCode = $(".form-register__code"),
                $codeResendText = $(".form-register__code-text"),
                $codeResendButton = $(".form-register__code-resend"),
                $this = $("#register_btn_code");

            BX.ajax.runComponentAction('grin:tq_registration',
                'sendCode', { // Вызывается без постфикса Action
                    mode: 'class',
                    data: {form: $('#register_phone').serializeArray()}, // ключи объекта data соответствуют параметрам метода
                })
                .then(function (response) {
                    if (response.data.STATUS === 'SUCCESS') {
                        $('#form_register_2 .tq_error').hide();

                        var $enterCodeTimer = $(".form-register__code-time");

                        startTimer(timerSeconds, $enterCodeTimer);

                        $this.hide();
                        $textEnterNumber.removeClass("active");
                        $textEnterCode.addClass("active");
                        $enterCode.show();

                        $codeResendText.show();
                        $codeResendButton.hide();
                    } else {
                        $('#form_register_2 .tq_error').html(response.data.MESSAGE).show();
                    }
                });
        }


        /* Вернуться к набору номера телефона */
        $(document).on("click", "#register_btn_return", function(){
            var $textEnterNumber = $(".form-register__text--phone"),
                $textEnterCode = $(".form-register__text--code"),
                $enterCode = $(".form-register__code");

            $("#register_btn_code").show();
            $textEnterNumber.addClass("active");
            $textEnterCode.removeClass("active");
            $enterCode.hide();
        });


        /* Выслать код повторно */
        $(document).on("click", "#register_btn_resend", function(){
            sendPhone();
        });


//----------------------------------------------------
//                      UTILITY
//----------------------------------------------------


        /* Проверка на сложность пароля */
        $("#register_password").on("input change", function(){
            var passwordValue = $(this).val(),
                $passwordState = $("#register_password_state"),
                $passwordStateText = $passwordState.find(".state-password__text");

            $passwordState.removeClass("state-password--simple state-password--good state-password--perfect")

            if ((passwordValue.length >= 6) && (/^.*[A-Za-zА-Яа-я]+.*$/.test(passwordValue))){
                $passwordState.show();
            }
            else{
                $passwordState.hide();
            }

            if ((passwordValue.length >= 6) && (/[A-Za-zА-Яа-я]{1,}/.test(passwordValue))){
                if (passwordValue.length >= 8 && (/[A-Za-zА-Яа-я]{3,}/)){
                    if (/[!@#$%^&*()_+\-=~`\\;:'",.\/?]{1,}/.test(passwordValue)){
                        $passwordState.addClass("state-password--perfect");
                        $passwordStateText.text("Идеальный");
                    }
                    else{
                        $passwordState.addClass("state-password--good");
                        $passwordStateText.text("Хороший");
                    }
                }
                else{
                    $passwordState.addClass("state-password--simple");
                    $passwordStateText.text("Слабый");
                }
            }
        });



        /* Функция запуска таймера */
        function startTimer(time, element){

            var localTime = time;

            if (timer){
                clearInterval(timer);
            }
            element.text(getWord(localTime, 'секунду', 'секунды', 'секунд'));
            timer = setInterval(function () {

                localTime--;

                if (localTime < 0) {
                    clearInterval(timer);
                    $(".form-register__code-text").hide();
                    $(".form-register__code-resend").show();
                }
                else {
                    element.text(getWord(localTime, 'секунду', 'секунды', 'секунд'));
                }

            }, 1000);
        }

    }



    /* Функция правильных окончаний для чисел */
    function getWord(count, ending1, ending2, ending3){
        var thisResult = '';
        var countLast = parseInt(count.toString().substr(-1));
        if (countLast == 1){
            thisResult = ending1;
        }
        else if ((countLast >= 2) && (countLast <= 4)){
            thisResult = ending2;
        }
        else{
            thisResult = ending3;
        }
        if ((count >= 11) && (count <= 14)){
            thisResult = ending3;
        }
        return count + ' ' + thisResult;
    }

});