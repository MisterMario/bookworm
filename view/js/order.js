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

/* Для страницы: редактор заказа */
function removeProductFromHTML(self) { // Удаляет товар из разметки на текущей странице. Не затрагивает БД
  var product = $(self).parent().parent();
  product.slideUp(500, function() { product.remove(); updateOrderStatus(); });
}

function updateOrderStatus() {
  var total_price = 0, books_num = 0,
      product_list_html = $('#products-in-order .product');

  for (var i = 0; i < product_list_html.length; i++) {
    var info_list = $(product_list_html[i]).find('ul li'), regex = new RegExp('[0-9]+');
    books_num += Number(regex.exec(info_list[2].innerHTML));
    total_price += Number(regex.exec(info_list[3].innerHTML));
  }
  $('.order-info span#books-num').html(books_num);
  $('.order-info span#total-price').html(total_price);
}

function setOrderStatus(status_code) {
  var order = $('.order'),
      status_name_html = order.find('span#status-name'),
      current_status = (new RegExp('o-status-[0-9]', 'g')).exec(order.attr('class'))[0];

  order.removeClass(current_status);
  order.addClass('o-status-' + status_code);
  $('#hidden-information input[name=order_status]').val(status_code);

  switch (status_code) {
    case 1:
      status_name_html.html('ожидает подтверждения');
      break;
    case 2:
      status_name_html.html('укомплектован');
      break;
    case 3:
      status_name_html.html('находится в пути');
      break;
    case 4:
      status_name_html.html('выполнен');
      break;
    case 5:
      status_name_html.html('отменен');
      break;
  }
}

function goToPageByOrderStatus() {
  var selected_status =  $('select[name=select-order-status] option:selected').val(),
      location = '/control/orders/';

  if (selected_status != 0)
    location += 'status/' + selected_status + '/';

  window.location = location;
}
