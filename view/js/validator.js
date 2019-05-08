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
