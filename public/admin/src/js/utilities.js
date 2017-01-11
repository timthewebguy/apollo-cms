
function $(sel, ctx) {
	var c = ctx || document;
	var query = c.querySelectorAll(sel);
	if(query.length == 1) {
		return query.item(0);
	} else {
		return query;
	}
}

Node.prototype.find = function(selector) {
	if (/(^\s*|,\s*)>/.test(selector)) {
		if (!this.id) {
			this.id = 'ID_' + new Date().getTime();
			var removeId = true;
		}
		selector = selector.replace(/(^\s*|,\s*)>/g, '$1#' + this.id + ' >');
		var result = document.querySelectorAll(selector);
		if (removeId) {
			this.id = null;
		}
		return result;
	} else {
		return this.querySelectorAll(selector);
	}
};

Element.prototype.nodeNumber = function() {
	var el = this, node=0;
	while( (el = el.previousElementSibling) != null) {
		node++;
	}
	return node;
};

Element.prototype.isBefore = function(el) {
	if(this.parentNode != el.parentNode) {
		console.log('not the same parent');
		return false;
	}
	if(this.nodeNumber() > el.nodeNumber()) {
		return false;
	}
	return true;
};

Node.prototype.nextElement = function() {
	var n = this.nextSibling;
	if(n == null) { return false; }

	if(n.nodeType != 1) {
		return n.nextElement();
	} else {
		return n;
	}
};

Node.prototype.previousElement = function() {
	var n = this.previousSibling;
	if(n == null) { return false; }

	if(n.nodeType != 1) {
		return n.previousElement();
	} else {
		return n;
	}
};

Element.prototype.addClass = function(_class) {
	if(this.classList) {
		this.classList.add(_class);
		return this;
	} else {
		var classes = this.className.split(' ');
		if(classes.indexOf(classToAdd) === -1) {
			this.className = this.className + (classes.length > 0 ? ' ' : '') + classToAdd;
		}
		return this;
	}
};

Element.prototype.removeClass = function(_class) {
	if(this.classList) {
		this.classList.remove(_class);
		return this;
	} else {
		var finalClassName = '';
		this.className.split(' ').forEach(function(cl) {
			if(cl != _class) { finalClassName += cl + ' ' }
		});
		this.className = finalClassName.replace(/[ /t]+$/, '');
		return this;
	}
};

Element.prototype.hasClass = function(_class) {
	if(this.classList) {
		return this.classList.contains(_class);
	} else {
		return this.className.split(' ').indexOf(_class) != -1;
	}
};

Element.prototype.loop = function(func) {
	func(this);
};

NodeList.prototype.addEventListener = function(event, callback, capture) {
	this.loop(function (n) {
		n.addEventListener(event, callback, capture || false);
	});
};
NodeList.prototype.removeEventListener = function(event, callback, capture) {
	this.loop(function (n) {
		n.removeEventListener(event, callback, capture || false);
	});
};
NodeList.prototype.loop = function(func) {
	for(var i = 0; i < this.length; i++) {
		func(this.item(i));
	}
};

Element.prototype.ancestor = function(query) {
	var elem = this.parentElement;

	while(!elem.matches(query) && elem !== document.body) {
		elem = elem.parentElement;
	}

	return (elem === document.body) ? null : elem;
};

Element.prototype.fire = function(eventName) {
    if (document.createEventObject) {
        // dispatch for IE
        var evt = document.createEventObject();
        return this.fireEvent('on'+eventName,evt)
    } else {
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(eventName, true, true ); // event type,bubbling,cancelable
        return !this.dispatchEvent(evt);
    }
};
