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

  ajax('/ajax_order.php', params, function(data) {
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

  ajax('/ajax_order.php', params, function(data) {
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
