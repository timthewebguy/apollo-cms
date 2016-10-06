
function $(sel) {
	var query = document.querySelectorAll(sel);
	if(query.length == 1) {
		return query.item(0);
	} else {
		return query;
	}
}

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

NodeList.prototype.addEventListener = function(event, callback, capture) {
	this.forEach(function (n) {
		n.addEventListener(event, callback, capture || false);
	});
};
NodeList.prototype.removeEventListener = function(event, callback, capture) {
	this.forEach(function (n) {
		n.removeEventListener(event, callback, capture || false);
	});
};
