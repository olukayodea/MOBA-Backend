
function displayCardType(val) {
	var logo = GetCardType(val);
	if (logo == "Visa") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-visa fa-2x"></i>';
	} else if (logo == "Mastercard") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-mastercard fa-2x"></i>';
	} else if (logo == "AMEX") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-amex fa-2x"></i>';
	} else if (logo == "Discover") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-discover fa-2x"></i>';
	} else if (logo == "Diners") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-diners-club fa-2x"></i>';
	} else if (logo == "Diners - Carte Blanche") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-diners-club fa-2x"></i>';
	} else if (logo == "JCB") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-jcb fa-2x"></i>';
	} else if (logo == "Visa Electron") {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-visa fa-2x"></i>';
	} else {
		document.getElementById("cardLogo").innerHTML = '<i class="fab fa-cc-credit-card fa-2x"></i>';
	}
}

function GetCardType(number) {
    // visa
	var re = new RegExp("^4");
    if (number.match(re) != null)
        return "Visa";

    // Mastercard 
	// Updated for Mastercard 2017 BINs expansion
    re = new RegExp("^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$");
    if (number.match(re) != null)
        return "Mastercard";

    // AMEX
    re = new RegExp("^3[47]");
    if (number.match(re) != null)
        return "AMEX";

    // Discover
    re = new RegExp("^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)");
    if (number.match(re) != null)
        return "Discover";

    // Diners
    re = new RegExp("^36");
    if (number.match(re) != null)
        return "Diners";

    // Diners - Carte Blanche
    re = new RegExp("^30[0-5]");
    if (number.match(re) != null)
        return "Diners - Carte Blanche";

    // JCB
    re = new RegExp("^35(2[89]|[3-8][0-9])");
    if (number.match(re) != null)
        return "JCB";

    // Visa Electron
    re = new RegExp("^(4026|417500|4508|4844|491(3|7))");
    if (number.match(re) != null)
        return "Visa Electron";

    return "";
}

function monthCheck() {
	var len = document.getElementById('mm').value;
	var lenght = len.length;

	if (lenght >= 2) {
		document.getElementById('yy').focus();
	}
}

function yearCheck() {
	var len = document.getElementById('yy').value;
	var lenght = len.length;

	if (lenght < 1) {
		document.getElementById('mm').focus();
    }
    
	if (lenght >= 2) {
		document.getElementById('cvv').focus();
	}
}

$("#cardno").on("keydown", function(e) {
    var cursor = this.selectionStart;
    if (this.selectionEnd != cursor) return;
    if (e.which == 46) {
        if (this.value[cursor] == " ") this.selectionStart++;
    } else if (e.which == 8) {
        if (cursor && this.value[cursor - 1] == " ") this.selectionEnd--;
    }
}).on("input", function() {
    var value = this.value;
    var cursor = this.selectionStart;
    var matches = value.substring(0, cursor).match(/[^0-9]/g);
    if (matches) cursor -= matches.length;
    value = value.replace(/[^0-9]/g, "").substring(0, 16);
    var formatted = "";
    for (var i=0, n=value.length; i<n; i++) {
        if (i && i % 4 == 0) {
            if (formatted.length <= cursor) cursor++;
            formatted += " ";
        }
        formatted += value[i];
    }
    if (formatted == this.value) return;
    this.value = formatted;
    this.selectionEnd = cursor;
});