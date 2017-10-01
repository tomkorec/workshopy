$('#mainForm').click(function(){
    if ($("#mainForm input:checkbox:checked").length > 0){
        $('#sendForm').removeAttr('disabled');
    } else {
        $('#sendForm').attr("disabled","disabled");   
    }    
});

function getUrlParameter() {
    var thisUrl = window.location.href;
    var myRegexp = /[^&?]*?=([^&?]*)/g;
    var res = myRegexp.exec(thisUrl);
    $('#registrationCode').val(res[1]);
    if(res){
        setTimeout(submitActivation(),1000);
    }
}
function submitActivation(){
    $('#submitActiv').click();
}

$(document).ready(function(){
    getUrlParameter();
});


var totalPrice = 0;
$('input[type=checkbox]').change(function(){
    
    if($(this).is(":checked")){
        $(this).each(function(){
            totalPrice = totalPrice + parseInt($(this).siblings('.price').attr('value'));
        });
        $('.totalPrice').text("Celková cena vybraných workshopů: "+totalPrice+",- Kč");
        $(this).parent().addClass("checkedBg");

        
    }else{
        $(this).each(function(){
            totalPrice = totalPrice - parseInt($(this).siblings('.price').attr('value'));
        });
        $('.totalPrice').text("Celková cena vybraných workshopů: "+totalPrice+",- Kč");
        $(this).parent().removeClass("checkedBg");
    }
});