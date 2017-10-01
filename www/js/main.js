$('#mainForm').click(function(){
    if ($("#mainForm input:checkbox:checked").length > 0){
        $('#sendForm').removeAttr('disabled');
    } else {
        $('#sendForm').attr("disabled","disabled");   
    }
    
    
});

$('document').on('load',function(){
   if($('input[type=checkbox]').prop('disabled',true)){
        $(this).parent('.checkbox').css('visibility','hidden');
    } 
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