function ajax(handler, params, callback, isAsync = true) {
  $.ajax({
    url: handler,
    type: 'POST',
    data: JSON.stringify(params),
    contentType: 'application/json; charset=utf-8',
    dataType: 'json',
    success: callback,
    async: isAsync,
  });
}

/* Функции корзины */
function addToCart(self) {

  var item_id = $(self).prev()[0].defaultValue;

  if ( userIsAuthorized() ) { // Если пользователь авторизован
    var params = {
      form_name: 'add_to_cart',
      item_id: item_id,
      count: 1,
    };

    ajax('/ajax/ajax_cart.php', params, function(data) {
      if (!data.status) showMessageBox('Произошла ошибка при добавлении товара в корзину!', 1);
      updateMiniCartStatus(data.total_sum, data.count);
    });

  } else { // Если пользователь не авторизован то данные о его корзине лежат в куках
    var nodb_cart = $.cookie('bw-nodb-cart'), count = 0, regex = new RegExp(item_id + ':' + '[0-9]+');
    var cookies_info = {path: '/', expires: 1};

    if (nodb_cart == undefined) { // Если корзина пуста
      nodb_cart = item_id + ':1\n';
    } else if ( regex.test(nodb_cart) ) { // Если товар уже есть в корзине

      var row = regex.exec(nodb_cart)[0]; // Получение строки с информацией о товаре
      count = Number( row.substr(row.indexOf(':') + 1) ) + 1; // Увеличение количества на 1
      nodb_cart = nodb_cart.replace(row, row.substr(0, row.indexOf(':') + 1) + count); // Замена текущей строки на новую

    } else { // Если товара еще нет в корзине
      nodb_cart += item_id + ':1\n';
    }

    $.cookie('bw-nodb-cart', nodb_cart, cookies_info);
    updateCartStatus();
  }

  // Анимация полета книги в корзину
  var photo = $(self).parent().parent().parent().find('#product-photo img'),
      photo_clone_css_props = {'position': 'absolute', 'top': photo.offset().top, 'left':photo.offset().left},
      photo_clone = photo.clone().css(photo_clone_css_props).appendTo(photo.parent()),
      cart_icon_position = $('#basket #cart-icon').offset();

  animate_styles = {'top':cart_icon_position.top + 5,
                    'left':cart_icon_position.left + 20,
                    'width':'15px',
                    'height':'20px'};

  photo_clone.animate(animate_styles, 1000, function() { photo_clone.remove(); });
}

function removeFromCart(self) {
  var item_id = $(self).prev().val(),
      product = $(self).parent().parent();
  var params = {form_name: 'remove_from_cart', item_id: item_id};

  ajax('/ajax/ajax_cart.php', params, function(data) {
    if (data.status) {
      product.slideUp(500, function(){ product.remove(); });
      updateCartStatus();
    } else showMessageBox('Возникла ошибка при удалении товара!', 1);
  });
}

function removeFromNoDBCart(self) {
  var product_id = $(self).prev().prev().val(),
      product = $(self).parent().parent(),
      regex = new RegExp(product_id + ':' + '[0-9]+'),
      row = '',
      nodb_cart = $.cookie('bw-nodb-cart'),
      cart_status = null;

  row = regex.exec(nodb_cart)[0];
  nodb_cart = nodb_cart.replace(row + '\n', '');
  $.cookie('bw-nodb-cart', nodb_cart, {path: '/', expires: 1});

  product.slideUp(500, function(){ product.remove(); });

  // Определение нового состояния корзины
  updateCartStatus();
}

function clearCart() {
  var params = {form_name: 'clear_cart'};
  ajax('/ajax/ajax_cart.php', params, function(data) {
    if (data.status) {
      $('.product').remove();
      $('#sum-total h2:first-child span').html('0');
      $('#sum-total h2:last-child span').html('0 BYR');
    } else showMessageBox('Возникла ошибка при очистке корзины!', 1);
  });
}

function clearNoDBCart() {
  $('.product').remove();
  $('#sum-total h2:first-child span').html('0');
  $('#sum-total h2:last-child span').html('0 BYR');
  $.removeCookie('bw-nodb-cart', {path: '/'});
}

function minus(self) {
  var i_count = $(self).next().next().children(),
      id = $(self).parent().parent().parent().parent().next().children('input[name=book_id]').val(),
      price = $(self).parent().parent().prev().prev().prev().text(), // Необработанная цена
      cart_status = null;

  if ( isCorrectNumber(i_count.val()) ) {
    if (Number(i_count.val()) > 1)
      i_count.val( Number(i_count.val()) - 1 );
  } else i_count.val('1');


  if (userIsAuthorized()) {
    var params = {
      form_name: 'add_to_cart',
      item_id: id,
      count: -1,
    };

    ajax('/ajax/ajax_cart.php', params, function(data) {
      if (!data.status) showMessageBox('Произошла ошибка при уменьшении количества товаров!', 1);
    });
  } else setProductsCountNoDB(id, i_count.val());

  // Пересчет итоговой стоимости товара
  price = (new RegExp('[0-9]+')).exec(price)[0];
  $(self).parent().parent().next().html('<strong>Итого:</strong> ' + (Number(price) * Number(i_count.val())) + ' BYR');

  // Определение нового состояния корзины
  updateCartStatus();
}

function plus(self) {
  var i_count = $(self).next().children(),
      id = $(self).parent().parent().parent().parent().next().children('input[name=book_id]').val(),
      price = $(self).parent().parent().prev().prev().prev().text(), // Необработанная цена
      cart_status = null;

  if ( isCorrectNumber(i_count.val()) ) {
    i_count.val( Number(i_count.val()) + 1 )
  } else i_count.val('1');

  if (userIsAuthorized()) {
    var params = {
      form_name: 'add_to_cart',
      item_id: id,
      count: 1,
    };

    ajax('/ajax/ajax_cart.php', params, function(data) {
      if (!data.status) showMessageBox('Произошла ошибка при увеличении количества товаров!', 1);
    });
  } else setProductsCountNoDB(id, i_count.val());

  // Пересчет итоговой стоимости товара
  price = (new RegExp('[0-9]+')).exec(price)[0];
  $(self).parent().parent().next().html('<strong>Итого:</strong> ' + (Number(price) * Number(i_count.val())) + ' BYR');

  updateCartStatus();
}

function setProductsCountNoDB(id, count) {
  var nodb_cart = $.cookie('bw-nodb-cart'),
      pattern = id + ':' + '[0-9]+',
      row = '';

  row = (new RegExp(pattern)).exec(nodb_cart);
  if (row != null) {
    nodb_cart = nodb_cart.replace(row[0], id + ':' + count);
    $.cookie('bw-nodb-cart', nodb_cart, {path: '/', expires: 1});
  }
}

function setProductsCount(id, title) {
  // Выполнятся запрос через ajax
}

function isCorrectNumber(value) { // На случай, если пользватель введет в input недопустимые символы
  if ( (new RegExp('^[0-9]+$')).test(value) ) return true;
  return false;
}

function getCartStatusFromHTML() { // Получает количество товаров и итоговую стоимость из HTML-кода страницы "Корзина"
  var total_sum = 0, count = 0;
  var prices_list = $('div.product div#product-description ul li:last-child'),
      nums_list = $('div.product div#product-description ul li div#product-count div#count input');

  for (var i = 0; i < prices_list.length; i++) {
    total_sum += Number( (new RegExp('[0-9]+')).exec(prices_list[i].innerText)[0] );
    count += Number( nums_list[i].value );
  }
  return {total_sum: total_sum, count: count};
}

/* Для того, чтобы сделать метод асинхронным можно сформировать объект содержащий объекты с полями: id и количество
   Затем сделать множественную выборку цен для каждого из ID, после чего вернуть их клиенту таким же объектом (ID, цена).
   В свою очередь клиент вычислит итоговую сумму. Возможно множественная выборка сделает метод более быстрым.
   За счет однократного обращения к БД, вместо множества однотипных запросов. */
function getCartStatusFromCookies() { // В будущем можно попробовать переписать этот метод сделав полностью асинхронным
  var nodb_cart = $.cookie('bw-nodb-cart'),
      cart_status = {total_sum: 0, count: 0},
      i = 0, current_price = 0, match, regexp = /[0-9]+/ig;

  if (nodb_cart != undefined && nodb_cart.length != 0) { // т.е. корзина не пуста
    while( match = regexp.exec(nodb_cart) ) {

      if (( (i + 1) % 2 ) != 0) { // Если текущее число является ID товара.
        /* Получение информации о товаре из БД
           Вынужденное использование синхронного запроса,
           так как асинхронный просто не успевает получить нужные данные вовремя */
        ajax('/ajax/ajax_cart.php', {form_name: 'get_book_price', item_id: match[0]}, function(data) {
          if (data.status) current_price = Number(data.price);
        }, false);
      } else {
        current_count = Number(match[0]);
        cart_status.count += current_count;
        cart_status.total_sum += current_count * current_price;
      }
      i++;
    }
  }
  return cart_status;
}

function updateCartStatus(total_sum, count) { // Метод работает только в случае, если пользователь находится на странице "Корзина"

  if ($('#inside-content-body #basket').length != 0) { // Если текущая страница "Корзина"
    var cart_status = getCartStatusFromHTML();
    $('#sum-total h2:last-child span').html(cart_status.total_sum + ' BYR');
    $('#sum-total h2:first-child span').html(cart_status.count);
    total_sum = cart_status.total_sum; count = cart_status.count;

  } else if (total_sum == undefined && count == undefined) { // Текущая страница не корзина и пользователь не аавторизован. Достаем информацию о корзине из кук
    var cart_status = getCartStatusFromCookies();
    //console.log(cart_status);

    total_sum = cart_status.total_sum; count = cart_status.count;
  }

  updateMiniCartStatus(total_sum, count);
}

function updateMiniCartStatus(total_sum, count) {
  $('#basket #inside-basket p').html(total_sum + ' BYR');
  $('#basket #count-of-goods').html(count);
}

function userIsAuthorized() { // Определяет авторизован ли пользователь
  return $('div#user-profil').length != 0 ? true : false ;
}

function proceedToCheckout() { // Можно заменить эту кнопку ссылкой
  location.href = '/add-order';
}


/* Выпадающее меню */
window.onload = function() {
  var a = $('div#topmenu ul li div.drop-down-menu').prev(),
      dd_menu = $('div#topmenu ul li div.drop-down-menu');

  a.bind({
    'click': function(e) {
      e.preventDefault();
    },
    'mouseover': function(e) {
      var self = $(e.target);

      self.toggleClass('active');
      self.next().toggleClass('hidden');
    },
    'mouseout': function(e) {
      var self = $(e.target);

      if (!$(e.relatedTarget).hasClass('drop-down-menu') && !$(e.relatedTarget).hasClass('dd-ul')
          && !$(e.relatedTarget).hasClass('dd-li') && !$(e.relatedTarget.parentNode).hasClass('dd-li')) {
        self.toggleClass('active');
        self.next().toggleClass('hidden');
      }
    }
  });

  dd_menu.bind('mouseleave', function(e) {
    var self = $(e.target),
        current_tag = self.prop('tagName');

    if (current_tag == 'DIV') {
      self.toggleClass('hidden');
      self.prev().toggleClass('active');
    } else if (current_tag == 'A') {
      self = self.parent().parent().parent();
      self.toggleClass('hidden');
      self.prev().toggleClass('active');
    }
  });
}

/* Поиск по сайту */
var tmr1 = null;

function getResults() {
  if (tmr1 != null) clearTimeout(tmr1);
  tmr1 = setTimeout(animateDropResults, 1000);
}

function animateDropResults() {
  // Сюда стоит добавить проверку какие клавиши были нажаты, чтобы реагировать только на буквы, цифры, символы, ctr+v.
  var value = $('#content-head #search input[type=text]').val();
      drop_list = $('#search-results');

  if (value.length != 0) {

    ajax('/ajax/ajax_search.php', {search_string: value}, function(data) {

      if (data.status) {
        $('#content-head #search-results #result-lines').html(data.html);
        $('#content-head #search-results #more-results a').attr('href', '/search/' + value + "/");
        if (drop_list.css('display') == 'none')
          drop_list.slideDown(500);
      } else if (drop_list.css('dislplay') != 'none') {
        drop_list.css('margin-top', '0');
        drop_list.slideUp(500);
      }
    });

  } else {
    drop_list.css('margin-top', '0');
    drop_list.slideUp(500);
  }
}

function goToLink(self) {
  self = $(self);
  window.location.href = self.children('.link').text();
}

/* Авторизация/логаут */
function goLogin() {
  var login = $('#inside-mini-profil input[name=login]').val(),
      pass = $('#inside-mini-profil input[name=pass]').val();

  if (login === undefined || pass === undefined) {

    showMessageBox('Ошибка! Поля не найдены! Попробуйте\nобновить страницу и авторизоваться заново!', 1);

  } else if (login.length != 0 && pass.length != 0) {

    ajax('/ajax/login.php', {login: login, password: pass, remember: 1}, function(data) {
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
  ajax('/ajax/logout.php', {logout: true}, function(data) {
    if (data.code != 0) showMessageBox(data.message, 1);
    else {
      $('#left-block').html(data.html.left_block);
      $('#mini-profil').html(data.html.mp);
      $('#topmenu ul li:last-child').html("<li><a href=\"/cart/\">Корзина</a></li><li><a href=\"/registration\">Регистрация</li>");
    }
  });
}

/* Выплывающие сообщения */
function showMessageBox(text, code = 0) {
  var self = $('#message-box'),
      title = "ИНФОРМАЦИЯ";

  if (code == 1) {
    self.addClass('error');
    title = "ОШИБКА";
  } else self.removeClass('error');

  self.find('#message-title').html(title);
  self.children('#message-body').html(text);
  self.removeClass('hidden');
  self.animate({'bottom':'7%', 'opacity': '1'}, 300);
}

function hideMessageBox() {
  var self = $('#message-box'),
      styles = {'bottom': 0 - self.outerHeight() + 'px', 'opacity': '0'};
  self.animate(styles, 300, function() { self.addClass('hidden'); });
}

/* Формирование заказа */
/* Для страницы: оформление заказа */
function setDiliveryDate(self) { // Обработчик выбора даты доставки
  self = $(self);
  self.parent().find('li.active').toggleClass('active');
  self.toggleClass('active');
}

function setFormat(self) {
  self = $(self);
  var parent = self.parent().parent().parent();

  if (!self.hasClass('active')) { // Если выбран активный элемент, делать что либо бессмысленно
    // Поиск блока с описанием прежнего формата для его скрытия
    var fd = parent.find('.format-description.fd-' + self.parent().find('.active').val());
    self.parent().find('.active').toggleClass('active'); // Убираем активность у ранее выбранного элемента
    var fd_heading = fd.prev(); // Для того, чтобы убрать заголовок описания
    if (fd_heading.length != 0 && fd_heading.hasClass('fd-heading')) fd_heading.toggleClass('hidden');
    if (fd.length != 0) fd.toggleClass('hidden'); // Если описание для данного формата существует
    self.toggleClass('active');
    fd = parent.find('.format-description.fd-' + self.val());
    fd_heading = fd.prev();
    if (fd_heading.length != 0 && fd_heading.hasClass('fd-heading')) fd_heading.toggleClass('hidden');
    if (fd.length != 0) fd.toggleClass('hidden'); // Показ текущего блока с описанием
  }
}

function setOnlinePaymentFormat(self) {
  self = $(self);

  if (!self.hasClass('active')) {
    self.parent().find('.active').toggleClass('active');
    self.toggleClass('active');
  }
}

/* Для страницы: информация о заказе */
function addBooksFromOrderToCart(self) {
  self = $(self);
  var params = {
    mode: 'add_books_from_order_to_cart',
    order_id: self.parent().find('input[name=item_id]').val(),
  };

  ajax('/ajax/ajax_order.php', params, function(data) {
    if (data.status) {
      showMessageBox('Товары успешно добавлены в корзину!');
      updateMiniCartStatus(data.total_sum, data.count);
    }
    else alert(data.message);
    // $('body').html(data);
  });
}

function cacncelOrder(self) {
  self = $(self);
  var params = {
    mode: 'cancel_order',
    order_id: self.parent().find('input[name=item_id]').val(),
  };

  ajax('/ajax/ajax_order.php', params, function(data) {
    if (data.status) {
      var order_status = self.parent().parent().prev().find('li:last-child');
      order_status.html( order_status.html().substr(0, order_status.html().indexOf('</strong>') + 9) + ' отменен.' );

      self.parent().find('input[name=edit]').toggleClass('hidden');
      self.parent().find('input[name=delete]').toggleClass('hidden');

      var current_order = self.parent().parent().parent();
      var class_for_current_order = current_order.attr('class');
      class_for_current_order = class_for_current_order.replace(new RegExp(/o-status-[0-9]/), 'o-status-5');
      current_order.attr('class', class_for_current_order);
    } else alert(data.message);
  });
}

/* Комментарии */
function setRating(self) {
  var current_star = $(self),
      rating = current_star.val();

  // Если пользотватель два раза нажмет на первую звездочку, то оценка приравнивается к 0
  if (rating == 1 && getRating(current_star.parent()) == 1)
    resetRating(self);
  else {
    resetRating(self);
    for (var i = rating; i > 0; i--) {
      current_star.toggleClass('active');
      current_star = current_star.prev();
    }
  }
}

function getRating(rating_ul) {
  return rating_ul.children('li.active').length;
}

function resetRating(self) {
  // Сброс рейтинга
  $(self).parent().children('li').removeClass('active');
}

function resetReview() {
  var my_review = $('#reviews #review.my');

  my_review.find('#review-head #review-info ul li').removeClass('active');
  my_review.find('#review-body textarea').val('');
}

function addReviewToHTML(id, rating, text) {
  var new_el = $('#reviews #review.my').clone(),
      user = {
        firstname: $('#user-profil #user-name p:first-child').text(),
        lastname: $('#user-profil #user-name p:last-child').text(),
      };

  new_el.removeClass('my');
  var el_controls = new_el.find('#review-head #review-control');

  el_controls.find('input[type=button]#ok').toggleClass('hidden');
  el_controls.find('input[type=button]#edit').removeClass('hidden');
  new_el.find('#review-head #review-info p').html(user.firstname + "<br />" + user.lastname);
  new_el.find('#review-head #review-info ul li').attr('onclick', '');
  new_el.find('#review-body textarea').attr('readonly', true);
  new_el.find('#review-head #review-info input[name=item_id]').val(id);
  resetReview();

  $('#reviews #review.my').after(new_el);
}

function expandTextarea() {
  var new_el = $('#reviews #review.my #review-body textarea[name=review-text]'),
      rows_count = Math.ceil(new_el.val().length / 87);

  if (rows_count == 0) rows_count = 1;
  if (rows_count != Number(new_el.attr('rows'))) new_el.attr('rows', rows_count);
}

function editReview(self) { // Делает отзыв доступным для редактирования пользователю
  var el = $(self).parent().parent().parent().parent(),  // Получение текущего блока Review. От корня удобнее работать.
      el_controls = el.find('#review-head #review-control');

  el.toggleClass('can-be-edited');
  el.find('#review-body textarea[name=review-text]').removeAttr('readonly');
  el.find('#review-head #review-info ul li').attr('onclick', 'setRating(this);');
  el_controls.find('input[type=button]#ok').toggleClass('hidden');//.attr('onclick', 'sendReview(this);'); // Этот обработчик выставлен по умолчанию
  el_controls.find('input[type=button]#edit').toggleClass('hidden');
  el_controls.find('input[type=button]#cancel').attr('onclick', 'blockReviewForEditing(this);');
}

function removeReview(self) {
  var el = $(self).parent().parent().parent().parent(),
      review = {
        mode: 'remove_review',
        id: el.find('#review-head #review-info input[name=item_id]').val(),
      };

  ajax('/ajax/validator.php', review, function(data) {
    if (data.status) {
      el.remove();
    } else alert(data.message);
    //$('body').html(data); // Для отладки
  });
}

function blockReviewForEditing(self) {
  var el = $(self),
      el_controls = el.find('#review-head #review-control');

  el.removeClass('can-be-edited');
  console.log(el);
  el.find('#review-body textarea[name=review-text]').attr('readonly', true);
  el.find('#review-head #review-info ul li').attr('onclick', '');
  el_controls.find('input[type=button]#ok').toggleClass('hidden');
  el_controls.find('input[type=button]#edit').toggleClass('hidden');
  el_controls.find('input[type=button]#cancel').attr('onclick','removeReview(this);');
}

/* Отправка форм через AJAX */
function sendBookInfo(isEdit) {
  var book_info;

  book_info = {
    id: $('#personal-data input[name=item_id]').val(),
    title: $('#personal-data input[name=title]').val(),
    author: $('#personal-data input[name=author]').val(),
    genre_id: Number($('#personal-data select[name=genre_code] option:selected').val()),
    language: $('#personal-data input[name=language]').val(),
    keywords: $('#personal-data input[name=keywords]').val(),
    series: $('#personal-data input[name=series]').val(),
    rightholder: $('#personal-data input[name=rightholder]').val(),
    age_restrictions: $('#personal-data input[name=age_restrictions]').val(),
    isbn: $('#personal-data input[name=isbn]').val(),
    price: $('#personal-data input[name=price]').val(),
    count: $('#personal-data input[name=count]').val(),
    annotation: $('#personal-data textarea[name=annotation]').val(),
  };

  if (isEdit) { // Если информация редактируется
    book_info.mode = 'book_edit';
    ajax('/ajax/validator.php', book_info, function(data) {
      if (data.status) {
        showMessageBox('Информация о товаре обновлена!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  } else {
    book_info.mode = "book_new";
    ajax('/ajax/validator.php', book_info, function(data) {
      if (data.status) {
        showMessageBox('Товар успешно добавлен!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  }
}

function sendInfoBlock(isEdit) {
  var info_block;

  info_block = {
    id: $('#personal-data input[name=item_id]').val(),
    title: $('#personal-data input[name=title]').val(),
    access_level: $('#personal-data input[name=access_level]').val(),
    content: $('#personal-data textarea[name=info_block_content]').val(),
  }

  if (isEdit) {
    info_block.mode = "i_block_edit"
    ajax('/ajax/validator.php', info_block, function(data) {
      if (data.status) {
        showMessageBox('Информационный блок изменен!\nДля отображения изменений перезагрузите страницу!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  } else {
    info_block.mode = "i_block_new"
    ajax('/ajax/validator.php', info_block, function(data) {
      if (data.status) {
        showMessageBox('Информационный блок успешно добавлен!\nДля отображения перезагрузите страницу!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  }
}

function sendMyContacts() {
  var user_info = {
    mode: 'my_contacts_edit',
    firstname: $('#personal-data input[name=firstname]').val(),
    lastname: $('#personal-data input[name=lastname]').val(),
    email: $('#personal-data input[name=email]').val(),
    phone_number: $('#personal-data input[name=phone]').val(),
    password: $('#personal-data input[name=password]').val(),
    repassword: $('#personal-data input[name=repassword]').val(),
    gender: $('#personal-data input#male').prop('checked') ? $('#personal-data input#male').val() : $('#personal-data input#female').val(),
    state: $('#delivery-data input[name=state]').val(),
    city: $('#delivery-data input[name=city]').val(),
    address: $('#delivery-data input[name=address]').val(),
    zip_code: $('#delivery-data input[name=zip_code]').val(),
  };
  // Если атрибут value у элемента есть и он не определен, то получается значение undefined - это fix
  if (user_info.state === undefined) user_info.state = '';
  if (user_info.city === undefined) user_info.city = '';
  if (user_info.address === undefined) user_info.address = '';
  if (user_info.zip_code === undefined) user_info.zip_code = '';

  ajax('/ajax/validator.php', user_info, function(data) {
    if (data.status) {
      showMessageBox('Информация успешно обновлена!');
    } else showMessageBox(data.message, 1);
    //$('body').html(data); // Для отладки
  });
}

function sendRegisterInfo() {
  var user_info = {
    mode: 'register',
    firstname: $('#personal-data input[name=firstname]').val(),
    lastname: $('#personal-data input[name=lastname]').val(),
    email: $('#personal-data input[name=email]').val(),
    phone_number: $('#personal-data input[name=phone]').val(),
    password: $('#personal-data input[name=password]').val(),
    repassword: $('#personal-data input[name=repassword]').val(),
    gender: $('#personal-data input#male').prop('checked') ? $('#personal-data input#male').val() : $('#personal-data input#female').val(),
  }

  if ($('#personal-data input[name=i-agree]').prop('checked')) {
    ajax('/ajax/validator.php', user_info, function(data) {
      if (data.status) {
        showMessageBox('Вы успешно зарегистрированы!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  } else showMessageBox('Вначале нужно подтвердить свое согласие с правилами проекта!', 1);
}

function sendReview(self) { // this нужно получать по той причине, что нужно узнать значение поля рейтинг
  var el = $(self).parent().parent().parent().parent();
  var review = {
    id: el.find('#review-head #review-info input[name=item_id]').val(),
    book_id: Number($('#general-information #product-information ul li[name=item_id]').text()),
    rating: getRating( el.find('#review-info ul') ),
    text: el.find('#review-body textarea[name=review-text]').val(),
  }

  if (review.text.length == 0) showMessageBox('Для начала введите то, что думаете о книге!', 1);
  else if (el.hasClass('my')) { // Если элемент не содержит класса My, значит он редактируется.
    review.mode = 'add_review';
    ajax('/ajax/validator.php', review, function(data) {
      if (data.status) {
        addReviewToHTML(data.item_id, review.rating, review.text);
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });

  } else {
    review.mode = 'edit_review';
    ajax('/ajax/validator.php', review, function(data) {
      if (data.status) {
        blockReviewForEditing(el);
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  }
}

function sendOrder() {
  var order = {
    mode: 'order_add',
    dilivery_method: $('#add-order #dilivery-format .format-selection ul li.active').val(),
    payment_method: $('#add-order #payment-format .format-selection ul li.active').val(),
    payment_system: null,
    date_of_dilivery: $('#add-order #dilivery-date ul li.active').val(),
    time_of_dilivery: $('#add-order #dilivery-time select option:selected').val(),
    user_info: null,
  };

  if ($('#add-order #payment-format .format-description.fd-' + order.payment_method).length != 0)
    order.payment_system = $('#add-order #payment-format .format-description.fd-' + order.payment_method + ' ul li.active').val();

  // Сбор информации о неавторизованном пользователе
  var user_is_authorized = userIsAuthorized();
  if (!user_is_authorized) {
    order.mode = 'add_order_for_nodb_user';
    var personal_data = $('#add-order #personal-data');
    order.user_info = {
      firstname: personal_data.find('input[name=firstname]').val(),
      lastname: personal_data.find('input[name=lastname]').val(),
      email: personal_data.find('input[name=e-mail]').val(),
      phone: personal_data.find('input[name=phone]').val(),
      state: personal_data.find('input[name=region]').val(),
      city: personal_data.find('input[name=city]').val(),
      address: personal_data.find('input[name=address]').val(),
      zip_code: personal_data.find('input[name=postcode]').val(),
    };
  }

  if (!user_is_authorized && (order.user_info.firstname == '' || order.user_info.lastname == '' ||
      order.user_info.email == '' || order.user_info.phone == '' ||
      order.user_info.state == '' || order.user_info.city == '' ||
      order.user_info.address == '' || order.user_info.zip_code == '')) {
    showMessageBox('Ошибка! Не все поля заполнены!', 1);

  } else {
    ajax('/ajax/ajax_order.php', order, function(data) {
      if (data.status) {
        showMessageBox('Ваш заказ добавлен в очередь!');
      } else showMessageBox(data.message, 1);
      //$('body').html(data); // Для отладки
    });
  }
}

function sendUser(isEdit) {
  var user_data = {
    mode: isEdit ? 'edit_user' : 'add_user',
    id: $('#personal-data input[name=item_id]').val(),
    firstname: $('#personal-data input[name=firstname]').val(),
    lastname: $('#personal-data input[name=lastname]').val(),
    email: $('#personal-data input[name=email]').val(),
    phone_number: $('#personal-data input[name=phone]').val(),
    level: $('#personal-data select[name=users_group] option:selected').val(), // select
    password: $('#personal-data input[name=password]').val(),
    repassword: $('#personal-data input[name=repassword]').val(),
    gender: $('#personal-data input[name=gender]:checked').val(), // radio
    state: $('#delivery-data input[name=state]').val(),
    city: $('#delivery-data input[name=city]').val(),
    address: $('#delivery-data input[name=address]').val(),
    zip_code: $('#delivery-data input[name=zip_code]').val(),
  };

  // Если в эти поля попадают пустые PHP-переменные, то они принимают значение undefined
  if (user_data.state == undefined) user_data.state = "";
  if (user_data.city == undefined) user_data.city = "";
  if (user_data.address == undefined) user_data.address = "";
  if (user_data.zip_code == undefined) user_data.zip_code = "";

  if (user_data.password != user_data.repassword)
    showMessageBox('Введенные пароли не совпадают!', 1);
  // Если хотя бы одно поле с данными о доставке заполнено - нужно заполнять все.
  else if ((user_data.state.length > 0 || user_data.city.length > 0 ||
            user_data.address.length > 0 || user_data.zip_code.length > 0) &&
           (user_data.state.length == 0 || user_data.city.length == 0 ||
            user_data.address.length == 0 || user_data.zip_code == 0))
    showMessageBox('Заполните все данные для доставки или пропустите вовсе!', 1);
  else {

    ajax("/ajax/validator.php", user_data, function(data) {

      if (data.status && !isEdit)
        showMessageBox('Пользователь успешно добавлен!');
      else if (data.status && isEdit)
        showMessageBox('Информация о пользователе успешно обновлена!');
      else
        showMessageBox(data.message, 1);

    });

  }
}
