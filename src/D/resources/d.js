/**
* JavaScript routines for Krumo
*
* @version $Id: d.js 22 2007-12-02 07:38:18Z Mrasnika $
* @link http://sourceforge.net/projects/d
*/

/////////////////////////////////////////////////////////////////////////////

/**
* Krumo JS Class
*/
function d() {
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
* Add a CSS class to an HTML element
*
* @param HtmlElement el
* @param string className
* @return void
*/
d.reclass = function(el, className) {
	if (el.className.indexOf(className) < 0) {
		el.className += (' ' + className);
		}
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
* Remove a CSS class to an HTML element
*
* @param HtmlElement el
* @param string className
* @return void
*/
d.unclass = function(el, className) {
	if (el.className.indexOf(className) > -1) {
		el.className = el.className.replace(className, '');
		}
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
* Toggle the nodes connected to an HTML element
*
* @param HtmlElement el
* @return void
*/
d.toggle = function(el) {
	var ul = el.parentNode.getElementsByTagName('ul');
	for (var i=0; i<ul.length; i++) {
		if (ul[i].parentNode.parentNode == el.parentNode) {
			ul[i].parentNode.style.display = (ul[i].parentNode.style.display == 'none')
				? 'block'
				: 'none';
			}
		}

	// toggle class
	//
	if (ul[0].parentNode.style.display == 'block') {
		d.reclass(el, 'd-opened');
		} else {
		d.unclass(el, 'd-opened');
		}
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
* Hover over an HTML element
*
* @param HtmlElement el
* @return void
*/
d.over = function(el) {
	d.reclass(el, 'd-hover');
	}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
* Hover out an HTML element
*
* @param HtmlElement el
* @return void
*/

d.out = function(el) {
	d.unclass(el, 'd-hover');
	}

/////////////////////////////////////////////////////////////////////////////

// Get element by ID
d.get_id = function(str) {
	var ret = document.getElementById(str);

	return ret;
}

// Get element by Class
d.get_class = function(str) {
	var elems = document.getElementsByClassName(str);
	var ret = new Array();

	// Just get the objects (not the extra stuff)
	for (var i in elems) {
		var elem = elems[i];
		if (typeof(elem) === 'object') {
			ret.push(elem);
		}
	}

	return ret;
}

// This is a poor mans querySelectorAll().
// querySelectorAll() isn't supported 100% until
// IE9, so we have to use this work around until
// we can stop supporting IE8
d.find = function(str) {
	if (!str) { return false; }

	var first  = str.substr(0,1);
	var remain = str.substr(1);

	if (first === ".") {
		return d.get_class(remain);
	} else if (first === "#") {
		return d.get_id(remain);
	} else {
		return false;
	}
}

function toggle_expand_all() {
	// Find all the expandable items
	var elems = d.find('.d-expand');
	if (elems.length === 0) { return false; }

	// Find the first expandable element and see what state it is in currently
	var action = 'expand';
	if (elems[0].nextSibling.style.display === 'block' || elems[0].nextSibling.style.display === '') {
		action = 'collapse';
	}

	// Expand each item
	for (var i in elems) {
		var item = elems[i];

		// The sibling is the hidden object
		var sib = item.nextSibling;

		if (action === 'expand') {
			sib.style.display = 'block';
			// Give the clicked item the d-opened class
			d.reclass(item, 'd-opened');
		} else {
			sib.style.display = 'none';
			// Remove the d-opened class
			d.unclass(item, 'd-opened');
		}
	}
}
