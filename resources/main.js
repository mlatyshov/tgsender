
document.getElementById('deleteBtn').addEventListener('click', function() {
    if (!confirm('Удалить PDF файл?')) 
        return false;
    
    const formData = {
        filename: document.getElementById('filename_hash').textContent,
        action: 'delete'
    };
    sendServer(formData);
});

function userlist_get() {
    
    sendServer({action: 'userlist_get'});
};

function userlist_hide() {
    
    document.getElementById('recipients').innerHTML = "<button id='loadBtn' onclick='userlist_get()'>Показать</button>";
    
};

function recipient_send() {
    document.getElementById('sendBtn').style.display = 'none';
    document.getElementById('sendProgress').textContent = 'Отправка документа адресатам...';
    sendServer({action: 'userlist_send'});

};
function recipient_send_final(message) {
    
    document.getElementById('sendProgress').innerHTML = message + " <button onclick='location.href=location.href'>Далее</button>";
}
    
function recipients_addnew_submit() {

    recipient_add_hide();
    const username = document.getElementById('username_new').value;
    const email_tg = document.getElementById('email_tg_new').value;
    sendServer({ action: "userlist_add", username: username, email_tg: email_tg});
    return false;
    
 }

 function recipient_add_show() {
    document.getElementById('recipients_addnew').style.display = 'block';
    //document.getElementById('recipients').innerHTML = '';
    
 }
 function recipient_add_hide() {
    document.getElementById('recipients_addnew').style.display = 'none';
    //document.getElementById('recipients').innerHTML = "<button onclick='userlist_get();'>Загрузить список</button>";
 }

 function row_save(user_id) {
    const username = document.getElementById('username_' + user_id).value;
    const email_tg = document.getElementById('email_tg_' + user_id).value;
    sendServer({ action: "userlist_save", user_id: user_id, username: username, email_tg: email_tg});
 }


// Обмен с сервером 
function sendServer(formData) {


    // Преобразуем данные в формат JSON
    const jsonData = JSON.stringify(formData);
    console.log(jsonData)

    fetch('ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json', // Указываем тип контента
        },
        body: jsonData, // Тело запроса
    }).then(response => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('Ошибка запроса');
    }).then(data => {
        
        if (data['status'] == 'reload') {

            location.href=location.href

        } 
        else if (data['status'] == 'recipient_send_final') {
            // рассылка завершена
            recipient_send_final(data['message']);
        } 
        else if (data['zone'] != '') {
            const elzone = document.getElementById(data['zone'])
            elzone.innerHTML = data['content'];

        } else {
            document.getElementById('message').innerHTML = 'Ошибка: ' + data['message'];
        }


    }).catch(error => {
        console.error('Ошибка:', error);
    });

}

// загрузка файла

function uploadFile() {
    const input = document.getElementById('pdfFile');
    const file = input.files[0]; 

    if (!file) {
        alert('Пожалуйста, выберите файл.');
        return;
    }

    const formData = new FormData();
    formData.append('file', file); // Добавляем файл в объект FormData
    formData.append('action', 'loadfile'); 

    fetch('ajax.php', {
        method: 'POST',
        body: formData, // Тело запроса
    }).then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        } 
        return response.json();

    }).then(data => {

        if (data['status'] == 'reload' && data['filename'] != '') {
            location.href=location.href
        }
     

        console.log("data", data);

    }).catch(error => {
        console.error('Ошибка:', error);
    });

}
    
