var toggleDd = 0;
function showDropdown() {
	if(toggleDd == 0){
		$("#myDropdown").css('display','block');
		toggleDd = 1;
	}
	else{
		$("#myDropdown").css('display','none');
		toggleDd = 0;
	}
}

var toggleDdMobile = 0;
function showDropdownMobile() {
	if(toggleDdMobile == 0){
		$("#myDropdownMobile").css('display','block');
		toggleDdMobile = 1;
	}
	else{
		$("#myDropdownMobile").css('display','none');
		toggleDdMobile = 0;
	}
}

window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
  	$("#myDropdown").css('display','none');
		toggleDd = 0;
  }
}