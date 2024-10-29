function notPaidWarn(url) {
  var r = confirm("There is no payment method for order. Invoice should be created AFTER payment. Are you sure you want to create invoice?");
  if (r == true) {
    window.location.replace(url);
  }
};

function updateDevices(el, data) {
  var str = '';

  for (var device in data[el.value]) {
    str += `<option value=${device}>${device}</option>`;
  }

  document.getElementById("apollo-device-id").innerHTML = str;
}

function aliasInput(e){
  var reg = /^[a-z\-0-9]*$/;
  var c = String.fromCharCode(e.which);
  if(reg.test(c))
  {
    return true;
  }
  else {
    return false;
  }
}