$('#mainForm').click(function(){
    if ($("#mainForm input:checkbox:checked").length > 0){
        $('#sendForm').removeAttr('disabled');
    } else {
        $('#sendForm').attr("disabled","disabled");   
    }
});
var totalPrice = 0;
$('input[type=checkbox]').change(function(){
    
    if($(this).is(":checked")){
        $(this).each(function(){
            totalPrice = totalPrice + parseInt($(this).siblings('.price').attr('value'));
        });
        $('.totalPrice').text(totalPrice);
        $(this).parent().addClass("checkedBg");

        
    }else{
        $(this).each(function(){
            totalPrice = totalPrice - parseInt($(this).siblings('.price').attr('value'));
        });
        $('.totalPrice').text(totalPrice);
        $(this).parent().removeClass("checkedBg");
    }
});