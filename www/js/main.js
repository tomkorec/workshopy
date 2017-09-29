$('#mainForm').click(function(){
    if ($("#mainForm input:checkbox:checked").length > 0){
        $('#sendForm').removeAttr('disabled');
    } else {
        $('#sendForm').attr("disabled","disabled");   
    }
});