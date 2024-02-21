**Задача**

Разработайте веб страницу, используя язык PHP, на которой будет реализован следующий функционал:

Возможность загрузки pdf документа по кнопке и последующее его отображение в стандартном фрейме.

Переход по кнопке на страницу для редактирования и сохранения изменений базы данных, которая содержит как минимум два поля: ФИО получателя и email/ID_telegram получателя.

Кнопка для отправки загруженного pdf файла по адресатам из базы данных выше.

После отправки файл должен удаляться с сервера.

В результате также должен быть реализован телеграм бот, в котором адресаты будут получать отправляемый им файл. Также этот файл должен приходить на email, если в бд указан он вместо id тг. Также обработайте возможные ошибки, которые могут возникнут во время работы реального пользователя. По F5 страница должна обновляться в первоначальное состояние без запроса подтверждения повторной отправки данных и, соответственно, не должна их отправлять повторно.

**Результат**

Пример работы по адресу https://latyshov.ru/tgs/

Включена работа с файлом и базой данных. 

Отправка сообщений в примере запрещена во избежании спам-активности!

При разворачивании клона необходимо изменить два файла:


_includes/common.php_

$TGSenderEnabled = false; => true // разрешить отправку документа в telegram

$EMSenderEnabled = false; => true // разрешить отправку документа по email


_includes/setup-default.php_

Переименовать includes/setup-default.php в includes/setup.php

В setup.php заполнить данные для mysql, smtp.mail.ru и telegrambot


