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

  ajax('/validator.php', review, function(data) {
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
