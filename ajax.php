<?php

namespace PdfSender;

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

include('includes/common.php');

$status = 'fail';
$zone = '';

// Это загрузка ФАЙЛА?
if (!empty($_FILES['file'])) {

    $file = $_FILES['file'];
    $action = 'loadfile';

    // Проверяем, не произошло ли ошибок при загрузке
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Путь, куда будет сохранен файл
        $destinationPath = $uploadDir . basename($file['name']);
        $hashname = md5(basename($file['name']) . time()).'.pdf';
        $destinationPath_hashname = $uploadDir . $hashname;

        // Перемещаем файл из временного места в конечную директорию
        if (move_uploaded_file($file['tmp_name'], $destinationPath_hashname)) {
            $message = "Файл успешно загружен.";
            // отображаем исходное имя файла 
            $_SESSION['uploadedFile_original'] = $filename = $destinationPath;
            // в файловую систему пускаем только безопасные имена 
            $_SESSION['uploadedFile_hashname'] = $hashname;
            $status = 'reload';
        } else {
            $message =  "Ошибка при перемещении загруженного файла.";
        }
    } else 
        $message =  "Ошибка загрузки файла: " . $file['error'];
    

} else {

    // --------------------------------------------------------

    // обработка Команд управления
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $action = $data->action ?? 'none';

    // Обработка данных
    $filename = (isset($data->filename))? $data->filename : 'none';
    $message =  "команда не обработана";

    // УДАЛЕНИЕ ФАЙЛА из файловой системы и сессии пользователя
    
    // --------------------------------------------------------

    if ($action == 'delete' && $filename != 'none') {

        if (deleteUploadedFile($filename)) {
            $message = 'удалено';
            $status = 'reload';
        }
        else 
            $message =  "не могу удалить файл";

    }
    // --------------------------------------------------------

    // СПИСОК получателей. Пагинация не реализована 
    else if ($action == 'userlist_get') {
        
        $status = 'ok';
        $zone = 'recipients';
        $content = userlist_get(); 

    }
    else if ($action == 'userlist_edit') {
        
        $status = 'ok';
        $user_id = (int) $data->user_id;
        $zone = "user_{$user_id}";    

        $sql = "SELECT user_id, username, email_tg FROM users where user_id = {$user_id}";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $content = tr_edit($row);
        }
        else 
            $content = '<td col=all>ошибка</td>';
    }
    //---------------------------------------------
    // userlist_save - сохранить данные существующего получателя 
    else if ($action == 'userlist_save') {
        
        $status = 'ok';
        $user_id = (int) $data->user_id;
        $username = $data->username;
        $email_tg = $data->email_tg;
        $zone = "user_{$user_id}";    

        // сохраняем
        $sql = "update users set username = ? , email_tg = ? where user_id = {$user_id}";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email_tg);
        $stmt->execute();

        // переходим в режим просмотра
        $sql = "SELECT user_id, username, email_tg FROM users where user_id = {$user_id}";
        $result = $conn->query($sql);
        if ($row = $result->fetch_assoc()) 
            $content = tr_view($row);
    }
    // row_reload - перезагрузить данные существующего получателя 
    else if ($action == 'row_reload') {
        
        $status = 'ok';
        $user_id = (int) $data->user_id;
        $zone = "user_{$user_id}";    


        // переходим в режим просмотра
        $sql = "SELECT user_id, username, email_tg FROM users where user_id = {$user_id}";
        $result = $conn->query($sql);
        if ($row = $result->fetch_assoc()) 
            $content = tr_view($row);
    }
    // --------------------------------------------------------
    // userlist_add - сохранить данные НОВОГО получателя 
    else if ($action == 'userlist_add') {
        
        
        $user_id = (int) $data->user_id;
        $username = $data->username;
        $email_tg = $data->email_tg;
        
        // сохраняем
        $sql = "insert into users (username , email_tg) values (?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email_tg);
        $stmt->execute();
        $status = 'ok';
        $message = "отладка";
        $zone = 'recipients';

        $content = userlist_get();

    }
    // --------------------------------------------------------
    // userlist_add - сохранить данные НОВОГО получателя 
    else if ($action == 'userlist_delete') {
        
        
        $user_id = (int) $data->user_id;
        
        // сохраняем
        $sql = "delete from users where user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $status = 'ok';
        $message = "отладка";
        $zone = 'recipients';

        $content = userlist_get();

    }
    // userlist_send - отправить документ получателям
    else if ($action == 'userlist_send') {
        
        
        $uploadedFile_hashname = (isset($_SESSION['uploadedFile_hashname'])) ? $_SESSION['uploadedFile_hashname'] : '';
        $uploadedFile_original = (isset($_SESSION['uploadedFile_original'])) ? $_SESSION['uploadedFile_original'] : '';

        // если физически файла не существует, то сессия не засчитывается
        if ($uploadedFile_hashname && file_exists($uploadDir . $uploadedFile_hashname)) {

            include('includes/TGSender.php');
            $tgSender = new TGSender();

            include('includes/EMSender.php'); 
            $emSender = new EMSender();

            $tgSender->prepare_send_file($uploadDir . $uploadedFile_hashname);
            $allCount = $tgCount = $emailCount = 0;
            $tgErrors = [];

            // по списку получателей

            foreach (userlist_array() as $key => $row) {
                
                $allCount++;
                // проверяем на формат и выбираем канал отправки

                // TELEGRAM

                if (ctype_digit($row['email_tg']) && $TGSenderEnabled) { 
                    
                    $tgsend = $tgSender->send($row['email_tg']);
                    if ($tgsend['result'])  // успешно
                        $tgCount++;
                    else   
                        $tgErrors[] = $tgsend['response'];
                }
                // EMAIL 
                else if (strpos($row['email_tg'], '@') !== false  && $EMSenderEnabled) {
                       
                    $emailSend = $emSender->sendFile(
                                            $row['email_tg'],
                                            $subjectPDF, 
                                            'файл во вложении', 
                                            $uploadDir . $uploadedFile_hashname, 
                                            $uploadedFile_original
                                        );
                    if ($emailSend['result']) 
                        $emailCount++;

                }
            }
            $errors = $allCount - ($tgCount + $emailCount);
            $message = ' Файл отправлен по списку. ';
            if (deleteUploadedFile($uploadedFile_hashname))
                $message .= ' Файл удалён.. '; 
            
            $EMSenderEnabledState = ($EMSenderEnabled)? 'разрешено' : 'отключено' ;
            $TGSenderEnabledState = ($TGSenderEnabled)? 'разрешено' : 'отключено' ;
            $message .= '<br /> LOG>
                                    Email: '    . $EMSenderEnabledState . ' и отправлено  ' . $tgCount . '; 
                                    TG:    '    . $TGSenderEnabledState . ' и отправлено ' . $emailCount. ';
                                    не доставлено '.$errors . ';
                                     <br />';

            

        }

        

        $status = 'recipient_send_final';

    }
    // --------------------------------------------------------

}

$content = (isset($content))? $content : '';
// Отправляем ответ клиенту
$response = ['status' => $status, 'message' => $message, 'filename' => htmlspecialchars($filename), 'action' => $action, 'content' => $content, 'zone' => $zone];
header('Content-Type: application/json');
echo json_encode($response);


// ===============================================


/**
 * Удаление файла из хранилища
 */
function deleteUploadedFile($filename) {
    global $uploadDir;

    $pattern = '/^[a-f0-9]{32}\.pdf$/i'; // имя файла без сюрпризов
    $result = false;
    if (preg_match($pattern, $filename)) {

        unlink($uploadDir . $filename);
        if (!file_exists($uploadDir . $filename)) {
            $_SESSION['uploadedFile_original'] = '';
            $_SESSION['uploadedFile_hashname'] = '';
            $result = true;
        }

    }

    return $result;
}

    
/**
 * Список получателей Массив
 */
function userlist_array() : array{
    global $conn;
    $sql = "SELECT user_id, username, email_tg FROM users ORDER BY user_id";
    $result = $conn->query($sql);
    $recipients = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $recipients[] =  $row;
        }
    }
    return $recipients;
}

/**
 * Список получателей HTML
 */
function userlist_get() {
    global $conn;
    $content = "
    <button onclick='userlist_hide();'>Скрыть</button>
    &nbsp;&nbsp;
    <button onclick='userlist_get();'>Обновить</button>
    &nbsp;&nbsp;
    <button onclick='recipient_add_show();'>Добавить</button>
    ";

    $sql = "SELECT user_id, username, email_tg FROM users ORDER BY user_id DESC ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $content .=  "
        <table class='table'>
            <tr>
            <th style='width: 50px;'>ID</th>
            <th>Получатель</th>
            <th><span class='td_email'>email</span> &bull; <span class='td_tg'>tg</span></th>
            <th>Управление</th>
            </tr>";
        // Вывод данных каждой строки
        while($row = $result->fetch_assoc()) {
            $tr_view = tr_view($row);
            $content .=  "<tr id='user_{$row['user_id']}'>{$tr_view}</tr>";
        }
        $content .= "</table>";
    } 
    else 
        $content .= " <div>нет получателей..</div>";

    return    $content; 
}
/**
 * Ряд в режиме просмотра
 */
function tr_view($row) {
    $style = (strpos($row["email_tg"], '@') === false)? 'td_tg':'td_email';
    $out = "
        <td>" . $row["user_id"] . "</td>
        <td>" . htmlspecialchars($row["username"]). "</td>
        <td class='{$style}'>" . htmlspecialchars($row["email_tg"]). "</td>
        <td>
            <button onclick='sendServer({ action: \"userlist_edit\", user_id: \"{$row["user_id"]}\" });'>изменить</button>
            &nbsp;
            <button 
                onclick='if(!confirm(\"удалить ".htmlspecialchars($row["username"])."?\")) return false; sendServer({ action: \"userlist_delete\", user_id: \"{$row["user_id"]}\" });'
                style='color: red;'
            >x</button>
        </td>
    ";
    return $out;        
}

/**
 * Ряд в режиме редактирования
 */
function tr_edit($row) {
    
    $out = "
        <td>" . $row["user_id"] . "</td>
        <td><input type=text value='" . htmlspecialchars($row["username"]). "' id='username_{$row["user_id"]}'></td>
        <td><input type=text value='" . htmlspecialchars($row["email_tg"]). "' id='email_tg_{$row["user_id"]}'></td>
        <td>
            <button onclick='row_save({$row['user_id']});' style='font-weight: bold;'>сохранить</button>
            <button  onclick='sendServer({ action: \"row_reload\", user_id: \"{$row["user_id"]}\" });' style='font-weight: bold;'>отменить</button>
        </td>
    ";
    return $out;     
    
}
    
    
