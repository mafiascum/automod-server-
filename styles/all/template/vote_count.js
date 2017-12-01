function getUrlParam(name) {
	if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)'))
			.exec(location.search))
		return decodeURIComponent(name[1]);
}

function ajaxCall() {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			result = JSON.parse(this.responseText);

			document.getElementById('message').value = result.vc;
		}
	};
	url = "app.php/game/actions/" + getUrlParam("t") + "/votes";
	xhttp.open("GET", url, true);
	xhttp.send();
}

if (getUrlParam('genvc')) {
	ajaxCall();
}
