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

      product.slideUp(500, function() {
        product.remove();
        updateCartStatus();
      });

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

  // Если куки, отвечающие за корзину опустошились - можно их стирать, а не хранить пустыми.
  // Сейчас это необходимо делать. Потом код содержащийся в else лучше удалить, оставить только перезапись кук
  // и убрать условие (if)
  if (nodb_cart.length != 0)
    $.cookie('bw-nodb-cart', nodb_cart, {path: '/', expires: 1});
  else
    $.removeCookie('bw-nodb-cart', {path: '/'});

  product.slideUp(500, function() {
    product.remove();
    updateCartStatus(); // Определение нового состояния корзины
  });
}

function clearCart() {
  var params = {form_name: 'clear_cart'};
  ajax('/ajax/ajax_cart.php', params, function(data) {
    if (data.status) {
      $('.product').remove();
      updateCartStatus();
    } else showMessageBox('Возникла ошибка при очистке корзины!', 1);
  });
}

function clearNoDBCart() {
  $('.product').remove();
  $.removeCookie('bw-nodb-cart', {path: '/'});
  updateCartStatus();
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

function updateCartStatus() { // Метод работает только в случае, если пользователь находится на странице "Корзина"
  var total_sum, count;

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
