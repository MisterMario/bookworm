function testAddOrder(user_is_authorized) {
  var user_data = {
    firstname: 'Андрей',
    lastname: 'Сосновский',
    email: 'andrysosna@gmail.com',
    phone: '375297752396',
    state: 'Минская',
    city: 'Солигорск',
    address: 'ул. Боярышника, д. 34, кв. 76',
    zip_code: '823142',
  };

  if (!user_is_authorized) {
    var personal_data = $('#add-order #personal-data');
    personal_data.find('input[name=firstname]').val(user_data.firstname);
    personal_data.find('input[name=lastname]').val(user_data.lastname);
    personal_data.find('input[name=e-mail]').val(user_data.email);
    personal_data.find('input[name=phone]').val(user_data.phone);
    personal_data.find('input[name=region]').val(user_data.state);
    personal_data.find('input[name=city]').val(user_data.city);
    personal_data.find('input[name=address]').val(user_data.address);
    personal_data.find('input[name=postcode]').val(user_data.zip_code);
  }
}
