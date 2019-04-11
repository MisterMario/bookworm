function goLogin() {
  var login = $('#inside-mini-profil input[name=login]').val(),
      pass = $('#inside-mini-profil input[name=pass]').val();

  if (login === undefined || pass === undefined) {

    showMessageBox('Ошибка! Поля не найдены! Попробуйте\nобновить страницу и авторизоваться заново!', 1);

  } else if (login.length != 0 && pass.length != 0) {

    ajax('/login.php', {login: login, password: pass, remember: 1}, function(data) {
      if (data.code != 0) showMessageBox(data.message, 1);
      else {
        $('#left-block').html(data.html.left_block);
        $('#mini-profil').html(data.html.mp);
        $('#topmenu ul li:last-child').prev().html('');
        $('#topmenu ul li:last-child').html("<li><a href=\"/pc/my-contacts\">Личный кабинет</li>");
        clearNoDBCart(); // Очистка корзины для неавторизованных пользователей - по какой-то причине не срабаытывает
      }
    });

  } else showMessageBox('Ошибка! Не все поля заполнены!', 1);
}

function goLogout() {
  ajax('/logout.php', {logout: true}, function(data) {
    if (data.code != 0) showMessageBox(data.message, 1);
    else {
      $('#left-block').html(data.html.left_block);
      $('#mini-profil').html(data.html.mp);
      $('#topmenu ul li:last-child').html("<li><a href=\"/cart/\">Корзина</a></li><li><a href=\"/registration\">Регистрация</li>");
    }
  });
}
