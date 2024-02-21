<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>tgsender</title>
    <link rel="stylesheet" href="resources/style.css?v1"  media="all" />
</head>
<body>
<?php
// =========================================================================
include('includes/common.php');

// наличие файла хранится в сессии для упрощения реализации
// оригинальное имя файла которое было сохранено 
$uploadedFile = (isset($_SESSION['uploadedFile_original'])) ? $_SESSION['uploadedFile_original'] : '';

// машинное имя, позволяет безопасно хранить файл
$uploadedFile_hashname = (isset($_SESSION['uploadedFile_hashname'])) ? $_SESSION['uploadedFile_hashname'] : '';

// если физически файла не существует, то сессия не засчитывается
if ($uploadedFile && !file_exists($uploadDir . $uploadedFile_hashname)) {
    $uploadedFile = $uploadedFile_hashname = '';
}

// количество подписчиков показывается всегда
$recipients_count = recipients_get_count();

// =========================================================================
?>

<h1>Менеджер рассылки</h1>

<div id='message'  class="alert warning">
Здесь загружается pdf файл, настраивается список получателей.
<br />
Далее производится отправка через TelegramBot или smtp.mail.ru в зависимости от указанного канала у получателя.
<br />
В данный момент рассылка отключена.
</div>

<br /><hr />

<div id='loader'>

    <h3>Загрузить новый файл</h3>
    <input type="file" id="pdfFile" accept="application/pdf">
    <button onclick="uploadFile()">Загрузить файл</button>

</div>

<br /><hr />

<div id='filecontrol'>
        
        <div style="<?=($uploadedFile == '')? 'display: none;':'display: block;';?>">
            <h3>Хранилище PDF</h3>
            <div id=filename>Загружен файл: <b><?= str_replace($uploadDir, '', $uploadedFile); ?></b> <button id='deleteBtn'>Удалить</button> </div>    
            <div id=filename_hash><?= $uploadedFile_hashname; ?></b></div>   
            
        </div>

        <div style="<?=($uploadedFile == '')? 'display: block;':'display: none;';?>">
            PDF файл не загружен.
        </div> 

</div>

<br /><hr />

<h3>Получатели</h3>

<div id='recipients_addnew'>

        <form id="recipients_addnew_form">
            <b>Новый получатель:</b>
            &nbsp;&nbsp;&nbsp;
            <label for="username_new">ФИО:</label>
            <input type="text" id="username_new" name="username_new" required>
            &nbsp;&nbsp;&nbsp;
            <label for="email_tg">Email/ID Telegram:</label>
            <input type="text" id="email_tg_new" name="email_tg" required>
            &nbsp;&nbsp;&nbsp;
            <input type="submit" value="Сохранить" onclick="recipients_addnew_submit(); return false;">
            <button onclick="recipient_add_hide(); return false;">Отменить</button>
        </form>

</div>

<div id='recipients'>
        В списке: <?=$recipients_count;?> &nbsp; <button id='loadBtn' onclick='userlist_get()'>Показать</button>
</div>

<br /><hr />

<?php if ($uploadedFile) { ?>
    <h3>Документ:</h3>
    <div id="sendBtn">
        <button onclick="if (!confirm('Разослать документ получателям?')) return false; recipient_send(); ">ОТПРАВИТЬ</button>
    </div>    
    <div id="sendProgress"></div>
    
    <div id='currentFile'>
        <iframe src="<?= htmlspecialchars($uploadDir . $uploadedFile_hashname) ?>" width="100%" height="500px"></iframe>
    </div>
<? } ?>    


<script src="resources/main.js?v1"></script> 

<div style='padding: 20px; background-color: #eee;'>
<b>Задание:</b> 
<p>
Разработайте веб страницу, используя язык PHP, на которой будет реализован следующий функционал:
<p>
Возможность загрузки pdf документа по кнопке и последующее его отображение в стандартном фрейме.
<p>
Переход по кнопке на страницу для редактирования и сохранения изменений базы данных, которая содержит как минимум два поля: ФИО получателя и email/ID_telegram получателя.
<p>
Кнопка для отправки загруженного pdf файла по адресатам из базы данных выше.
<p>
После отправки файл должен удаляться с сервера.
<p>
В результате также должен быть реализован телеграм бот, в котором адресаты будут получать отправляемый им файл. Также этот файл должен приходить на email, если в бд указан он вместо id тг. Также обработайте возможные ошибки, которые могут возникнут во время работы реального пользователя. По F5 страница должна обновляться в первоначальное состояние без запроса подтверждения повторной отправки данных и, соответственно, не должна их отправлять повторно.
</div>

<<<<<<< HEAD

=======
>>>>>>> 0ad0baed8961d8226fc5e480b2c1e481689fdb85
<div style='padding: 20px; background-color: #000; color: #fff; '>
@mlatyshov <a href='https://github.com/mlatyshov/tgsender/tree/master' style='color: #fff'> Исходный код на github</a>
</div>
</body>
</html>

