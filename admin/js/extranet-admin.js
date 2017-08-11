var he = this.he;

var Extranet = function(config) {
	
	var self = this;
	var ix = 0;

  	this.getAjax = function() {
        if (window.XMLHttpRequest) {
            return new window.XMLHttpRequest;
        }
        else {
            try {
                return new ActiveXObject("MSXML2.XMLHTTP.3.0");
            }
            catch(ex) {
                return null;
            }
        }
    };

    this.makeAjaxCall = function(url,method,params,handler) {
        
        var oReq = self.getAjax();
        if (oReq != null) {
            oReq.open(method, url, true);
            oReq.onreadystatechange=function() {
                if (oReq.readyState==4 && oReq.status==200 && handler) {
                    handler(oReq.responseText);
                }
            }
            if (method == 'POST') {
                 oReq.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            }
            oReq.send(params);
        }
    };


    this.renderItems = function(root, data, type) {

    	var items = JSON.parse(data);

    	if (!items) {
    		return;
    	}
    
    	items.forEach(function(el){
    	
    		var p = document.getElementById(root);
    		var b, d, c, i, s, a;
		
			d = document.createElement('div');
			d.className = 'pure-g line line' + parseInt(ix%2);
			
			d.dataset.path = el.path;
			d.dataset.type = type;
			d.id = el.path;
			p.appendChild(d);
    		
			
			c = document.createElement('div');
			i = document.createElement('i');
			c.className = 'pure-u-3-24';
			i.className = type == 'folder' ? 'dashicons dashicons-category' : 'dashicons dashicons-media-default';
			c.appendChild(i);
			d.appendChild(c);

			c = document.createElement('div');
			c.className = 'pure-u-21-24 pure-u-md-12-24';

			if (type == 'folder') {
				a = document.createElement('a');
				a.href = config.url + '&path=' + el.path;
				a.innerHTML = el.name;
				a.className = 'title';
				c.appendChild(a);
			} else {
				s = document.createElement('span');
				s.innerHTML = el.name;
				s.className = 'title';
				c.appendChild(s);
			}
			d.appendChild(c);


			c = document.createElement('div');
			c.className = 'pure-u-12-24 pure-u-md-3-24 small';
			if (type == 'folder'){
				b = document.createElement('button');
				b.type = 'button';
				b.innerHTML = '<i class="dashicons dashicons-post-status"></i> ' + config.language.permissions;
				c.appendChild(b);
				b.addEventListener('click', function(e) {
					location.href = config.url + '&layout=permissions&path=' + el.path;
				});
			}
			d.appendChild(c);


			c = document.createElement('div');
			b = document.createElement('button');
			c.className = 'pure-u-12-24 pure-u-md-3-24 small';
			b.innerHTML = '<i class="dashicons dashicons-edit"></i> ' + config.language.rename;
			b.className = 'edit-title';
			b.type = 'button';
			b.dataset.parent = el.path;
			c.appendChild(b);
			d.appendChild(c);
			b.addEventListener('click',function(e){
				toggleRename(el.path, type);
			});


			c = document.createElement('div');
			b = document.createElement('button');
			c.className = 'pure-u-12-24 pure-u-md-3-24 small';
			b.innerHTML = '<i class="dashicons dashicons-trash"></i>' + config.language.delete;
			b.className = 'delete-item';
			b.dataset.target = el.path;
			b.type = 'button';
			c.appendChild(b);
			d.appendChild(c);
			b.addEventListener('click',function(e){
				deleteItem(el.path);
			});

			ix++;
    	});
    };

	this.refreshItems = function(root, folders, files) {
		document.getElementById(root).innerHTML = '';
		this.renderItems(root, folders, 'folder');
		this.renderItems(root, files, 'file');
	};

	this.showError = function(err) {
		document.getElementById('error-area').innerHTML = err;
		window.setTimeout(function(){document.getElementById('error-area').innerHTML = '';}, 2000);
	};


	this.renderPermissions = function(root, data) {
	
		var items = JSON.parse(data);
		var r, b, d, dd, c, cc, i, s, a, cb;

    	if (!items) {
    		return;
    	}

    	r = document.getElementById(root);
		d = document.createElement('div');
		d.className = 'pure-g header';
		r.appendChild(d);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-4-24';
		s.innerHTML = '<strong>' + config.language.fullname + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-4-24';
		s.innerHTML = '<strong>' + config.language.username + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-10-24';
		s.innerHTML = '<strong>' + config.language.permissions + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.aggregate + '</strong>';
		c.appendChild(s);
		d.appendChild(c);


		items.forEach(function(el){
	    	
			d = document.createElement('div');
			d.className = 'pure-g line line' + parseInt(ix%2);
			r.appendChild(d);

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-4-24';
			s.innerHTML = el.username;
			c.appendChild(s);
			d.appendChild(c);

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-4-24';
			s.innerHTML = el.nick;
			s.innerHTML += el.allowed ? ' <span class="small allowed">('+ config.language.allowed +')</span>' : ' <span class="small blocked">('+ config.language.blocked + ')</span>';
			c.appendChild(s);
			d.appendChild(c);

			c = document.createElement('div');
			c.className = 'pure-u-10-24';
			cc = document.createElement('div');
			cc.className = 'pure-g';
			c.appendChild(cc);

			var it = [
				{lang:config.language.list,			value:'00000001'},
				{lang:config.language.view,			value:'00000010'},
				{lang:config.language.download,		value:'00000100'},
				{lang:config.language.upload,		value:'00001000'},
				{lang:config.language.delete,		value:'00010000'},
				{lang:config.language.mkdir,		value:'00100000'},
				{lang:config.language.rmdir,		value:'01000000'},
				{lang:config.language.recursive,	value:'10000000'}
			];

			it.forEach(function(i, k){
				dd = document.createElement('div');
				dd.className = 'pure-u-1 pure-u-md-6-24';
				s = document.createElement('span');
				cb = document.createElement('input');
				s.innerHTML = i.lang;
				cb.type = 'checkbox';
				cb.value = i.value;
				if (el.individual[7-k] == 1) { cb.checked = true; }
				dd.appendChild(cb);
				cb.addEventListener('click', function(e){
					el = togglePermission(e, el);
					document.getElementById('user'+el.id).innerHTML = self.translatePermissions(el.aggregate);
				});
				dd.appendChild(s);
				cc.appendChild(dd);
			});

			d.appendChild(c);


			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-6-24';
			s.id = 'user' + el.id;
			s.innerHTML = self.translatePermissions(el.aggregate);
			c.appendChild(s);
			d.appendChild(c);

			ix++;
		});
	};


	this.translatePermissions = function(p) {
		var t = '';
		t += (p[0] == 1) ? config.language.recursive + ',': '';
		t += (p[1] == 1) ? config.language.rmdir  + ',': '';
		t += (p[2] == 1) ? config.language.mkdir  + ',': '';
		t += (p[3] == 1) ? config.language.delete  + ',': '';
		t += (p[4] == 1) ? config.language.upload  + ',': '';
		t += (p[5] == 1) ? config.language.download  + ',': '';
		t += (p[6] == 1) ? config.language.view  + ',': '';
		t += (p[7] == 1) ? config.language.list  + ',': '';

		return t;
	};


	this.renderUsers = function(root, data) {
		
		var items = JSON.parse(data);
    	if (!items) { return; }

		var r, d, a, c, s, b;

    	r = document.getElementById(root);
		d = document.createElement('div');
		d.className = 'pure-g header';
		r.appendChild(d);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.username + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.name + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.email + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		d.appendChild(c);

		items.forEach(function(el){

			d = document.createElement('div');
			d.className = 'pure-g line line' + parseInt(ix%2);
			r.appendChild(d);

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-6-24';
			s.innerHTML = el.username;
			c.appendChild(s);
			d.appendChild(c);

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-6-24';
			s.innerHTML = el.name;
			c.appendChild(s);
			d.appendChild(c);

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-6-24';
			s.innerHTML = el.email;
			c.appendChild(s);
			d.appendChild(c);


			c = document.createElement('div');
			b = document.createElement('button');
			c.className = 'pure-u-3-24 small';
			b.innerHTML = '<i class="dashicons dashicons-edit"></i> ' + config.language.edit;
			b.className = 'edit-title';
			b.type = 'button';
			c.appendChild(b);
			d.appendChild(c);
			b.addEventListener('click',function(e){
				location.href = config.url + '&layout=user&id=' + el.id;
			});

			c = document.createElement('div');
			s = document.createElement('span');
			c.className = 'pure-u-3-24';
			s.innerHTML = el.allowed ? config.language.allowed : config.language.blocked;
			s.className = el.allowed ? 'allowed' : 'blocked';
			c.appendChild(s);
			d.appendChild(c);

			ix++;
		});
	};


	this.bindEnter = function(xs) {

		xs.forEach(function(x){
			document.getElementById(x.id).addEventListener("keydown", function(e) {
			    if (!e) { var e = window.event; }
			    if (e.keyCode == 13) { 
			    	e.preventDefault();
			    	if (x.cback) {
			    		x.cback()
			    	}
			    }
			});
		});
	};
	

	this.renderUserPermissions = function(root, data) {
	
    	if (!data) {
    		return;
    	}

		var r, b, d, dd, c, cc, i, s, a, cb;

		r = document.getElementById(root);
		d = document.createElement('div');
		d.className = 'pure-g header pure-settings-group';
		r.appendChild(d);

		c = document.createElement('div');
		c.className = 'pure-u-12-24';
		a = document.createElement('a');
		a.innerHTML = '<i class="dashicons dashicons-editor-break"></i> ' + config.language.back;
		c.appendChild(a);
		d.appendChild(c);
		a.addEventListener('click', function(){
			showPermissions(data.previous);
		});

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.permissions + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		c = document.createElement('div');
		s = document.createElement('span');
		c.className = 'pure-u-6-24';
		s.innerHTML = '<strong>' + config.language.aggregate + '</strong>';
		c.appendChild(s);
		d.appendChild(c);

		data.folders.forEach(function(el){

			d = document.createElement('div');
			d.className = 'pure-g line line' + parseInt(ix%2);
			r.appendChild(d);

			c = document.createElement('div');
			c.className = 'pure-u-8-24 pure-u-md-12-24';
			s = document.createElement('span');
			a = document.createElement('a');
			a.innerHTML = el.name;
			s.innerHTML = '<i class="dashicons dashicons-category"></i> ';
			s.appendChild(a);
			c.appendChild(s);
			d.appendChild(c);
			a.addEventListener('click', function(){
				showPermissions(el.path)
			});


			c = document.createElement('div');
			c.className = 'pure-u-8-24 pure-u-md-6-24';
			cc = document.createElement('div');
			cc.className = 'pure-g';
			c.appendChild(cc);

			var it = [
				{lang:config.language.list,			value:'00000001'},
				{lang:config.language.view,			value:'00000010'},
				{lang:config.language.download,		value:'00000100'},
				{lang:config.language.upload,		value:'00001000'},
				{lang:config.language.delete,		value:'00010000'},
				{lang:config.language.mkdir,		value:'00100000'},
				{lang:config.language.rmdir,		value:'01000000'},
				{lang:config.language.recursive,	value:'10000000'}
			];

			it.forEach(function(i, k){
				dd = document.createElement('div');
				dd.className = 'pure-u-1 pure-u-md-12-24';
				s = document.createElement('span');
				cb = document.createElement('input');
				s.innerHTML = i.lang;
				cb.type = 'checkbox';
				cb.value = i.value;
				if (el.individual[7-k] == 1) { cb.checked = true; }
				dd.appendChild(cb);
				cb.addEventListener('click', function(e){
					el = togglePermission(e, el, config.user);
					document.getElementById(el.path).innerHTML = self.translatePermissions(el.aggregate);
				});
				dd.appendChild(s);
				cc.appendChild(dd);
			});

			d.appendChild(c);


			c = document.createElement('div');
			s = document.createElement('span');
			c.id = el.path;
			c.className = 'pure-u-8-24 pure-u-md-6-24';
			s.innerHTML = self.translatePermissions(el.aggregate);
			c.appendChild(s);
			d.appendChild(c);

			ix++;
		});
	};



};