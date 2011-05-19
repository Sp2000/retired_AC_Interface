switchLSIDSelector = function(el) {
	var classes = el.className.split(" ");
	var newClass = classes[0] + " ";
	el.className = classes[1] == "collapsed" ? newClass + "expanded" : newClass
			+ "collapsed";
}
navigateToSelected = function(baseUrl, select, op) {
	switch (op) {
	case 'next':
		location.href = baseUrl
				+ select.options[(select.selectedIndex + 1)].value;
		break;
	case 'previous':
		location.href = baseUrl
				+ select.options[(select.selectedIndex - 1)].value;
		break;
	default:
		location.href = baseUrl + select.options[select.selectedIndex].value;
		break;
	}
}
scrollToEl = function(elId) {
	var el = dojo.byId(elId);
	if (el != null) {
		var x = 0;
		var y = 0;
		while (el != null) {
			x += el.offsetLeft;
			y += el.offsetTop;
			el = el.offsetParent;
		}
		window.scrollTo(x, y - window.screen.availHeight / 10);
	}
}
var formInputElements = null;
getFormInputElements = function() {
	if (formInputElements == null) {
		formInputElements = dojo.query('form .dijitInputField input');
	}
	return formInputElements;
}
showLoader = function(elId) {
	var buttonDisabledClass = ' dijitButtonDisabled dijitDisabled';
	dojo.byId(elId).className += buttonDisabledClass;
}
removeKey = function() {
	dojo._destroyElement(dojo.byId('key'));
}
submitMultiSearchForm = function() {
	if (isFormValid()) {
		showLoader('search');
	}
	removeKey();
}
submitSearchForm = function() {
	if (isFormValid()) {
		showLoader('search');
	}
}
isFormValid = function() {
	var elements = getFormInputElements();
	if (elements.length > 0) {
		var val = '';
		dojo.forEach(elements, function(inputEl, index, array) {
			val += inputEl.value;
		});
		return dojo.trim(val).length > 0 ? true : false;
	}
	return dojo.trim(dojo.byId('key').value).length > 1 ? true : false;
}
startList = function() {
	if (document.all && document.getElementById) {
		navRoot = document.getElementById("nav");
		for (i = 0; i < navRoot.childNodes.length; i++) {
			node = navRoot.childNodes[i];
			if (node.nodeName == "LI") {
				node.onmouseover = function() {
					this.className += " over";
				}
				node.onmouseout = function() {
					this.className = this.className.replace(" over", "");
				}
				ulNodes = node.getElementsByTagName("UL");
				for (j = 0; j < ulNodes[0].childNodes.length; j++) {
					ulLiNode = ulNodes[0].childNodes[j];
					if (ulLiNode.nodeName == "LI") {
						ulLiNode.onmouseover = function() {
							this.className += " over";
						}
						ulLiNode.onmouseout = function() {
							this.className = this.className
									.replace(" over", "");
						}
					}
				}
			}
		}
	}
}
moveMenu = function() {
	var menuLayer = document.getElementById('menu');
	var theScroll = 0;
	if (window.pageYOffset) {
		theScroll = window.pageYOffset;
	} else if (window.document.documentElement
			&& window.document.documentElement.scrollTop) {
		// IE6
		theScroll = window.document.documentElement.scrollTop;
	} else if (window.document.body) {
		// Firefox
		theScroll = window.document.body.scrollTop;
	}
	var newY = theScroll + "px";
	if (menuLayer) {
		menuLayer.style.top = newY;
		setTimeout("moveMenu()", 500);
	}
}
init = function() {
	startList();
	moveMenu();
}
window.onload = init;
window.onscroll = moveMenu;

function collapseAll(className) {
	dijit.popup.close(dialog);
	var spans = dojo.query('.' + className);
	var span;
	var value = 0;
	for (i = 0; i < spans.length; i++) {
		span = spans[i];
		if (span.style.visibility == "visible") {
			span.style.visibility = "hidden";
			span.style.position = "fixed";
			value = 0;
		} else {
			span.style.visibility = "visible"
			span.style.position = "relative";
			value = 1;
		}
	}
	setCookie(className, value, cookieExpiration);
}

function setCookie(c_name, value, exdays) {
	if (typeof exdays == 'undefined') {
		exdays = 14;
	}
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value = escape(value)
			+ ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
	document.cookie = c_name + "=" + c_value + ";path=/";
}

function changeLanguage(language) {
	setCookie('aci_language', language, cookieExpiration);
	window.location.reload();
}

function translate(str) {
	if (translations && translations.constructor === Array && typeof translations[str] != 'undefined') {
		return translations[str];
	}
	return str;
}