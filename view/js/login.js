function goLogin() {
  var login = $('#inside-mini-profil input[name=login]').val(),
      pass = $('#inside-mini-profil input[name=pass]').val();

  if (login === undefined || pass === undefined) {

    showMessageBox('Ошибка! Поля не найдены! Попробуйте\nобновить страницу и авторизоваться заново!', 1);

  } else if (login.length != 0 && pass.length != 0) {

    ajax('/ajax/login.php', {login: login, password: pass, remember: 1}, function(data) {
      if (data.code != 0) showMessageBox(data.message, 1);
      else {
        clearNoDBCart(); // Очистка корзины для неавторизованных пользователей - по какой-то причине не срабаытывает
        window.location.reload();
      }
    });

  } else showMessageBox('Ошибка! Не все поля заполнены!', 1);
}

function goLogout() {
  ajax('/ajax/logout.php', {logout: true}, function(data) {
    if (data.code != 0) showMessageBox(data.message, 1);
    else {
      window.location.reload();
    }
  });
}
