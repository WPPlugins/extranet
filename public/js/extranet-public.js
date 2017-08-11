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
            var b, d, dd, c, i, s, a;
        
            d = document.createElement('div');
            d.className = 'pure-g line line' + parseInt(ix%2);
            d.id = el.path;
            p.appendChild(d);
            
            
            c = document.createElement('div');
            i = document.createElement('i');
            c.className = 'pure-u-2-24';
            i.className = type == 'folder' ? 'dashicons dashicons-category' : 'dashicons dashicons-media-default';
            c.appendChild(i);
            d.appendChild(c);

            c = document.createElement('div');
            c.className = 'pure-u-21-24 pure-u-md-18-24';

            if (type == 'folder') {
                a = document.createElement('a');
                a.href = el.url;
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
            c.className = 'pure-u-12-24 pure-u-md-2-24 small';
            if (type == 'file' && config.permissions.download) {
                i = document.createElement('i');
                i.className = 'download-el dashicons dashicons-download';
                c.appendChild(i);
                i.addEventListener('click',function(e){
                    download(el);
                });
            }
            d.appendChild(c);


            c = document.createElement('div');
            c.className = 'pure-u-12-24 pure-u-md-2-24 small';
            i = document.createElement('i');
            i.className = 'more-el dashicons dashicons-admin-generic';
            c.appendChild(i);
            i.addEventListener('click', function(e){
                showActions(el);
            });
            
            dd = document.createElement('div');
            dd.id = 'more-el-' + el.path;
            dd.className = 'more-actions hidden';
            
            var showmore = false;

            if (type == 'file' && config.extensions.indexOf(el.ext) != -1) {
                s = document.createElement('span');
                s.innerHTML = config.language.view;
                dd.appendChild(s);
                s.addEventListener('click', function(){
                   preview(el);
                });
                showmore = true;
            }

            if (type == 'file') {
                s = document.createElement('span');
                s.innerHTML = config.language.bookmark;
                dd.appendChild(s);
                s.addEventListener('click', function(){
                    var e = el; e.name = encodeURIComponent(el.name);
                    favorite(e);
                });
                showmore = true;
            }

            if (type == 'file' && config.permissions.share && config.permissions.download) {
                s = document.createElement('span');
                s.innerHTML = config.language.share;
                dd.appendChild(s);
                s.addEventListener('click', function(){
                    prompt(config.language.copylink, el.share);
                });
                showmore = true;
            }

            if ((type =='folder' && config.permissions.rmdir) || (type == 'file' && config.permissions.delete)) {
                s = document.createElement('span');
                s.innerHTML = config.language.delete;
                dd.appendChild(s);
                s.addEventListener('click', function(){
                    fdelete(el);
                });
                showmore = true;
            }

            showmore && c.appendChild(dd);
            d.appendChild(c);

            ix++;
        });
    };

    this.refreshItems = function(root, folders, files) {
        document.getElementById(root).innerHTML = '';
        this.renderItems(root, folders, 'folder');
        this.renderItems(root, files, 'file');
    };

    this.showError = function(err, t) {
        if (!t) t = 4000;
        var ea = document.getElementById('error-area');
        ea.innerHTML = err;
        ea.classList.toggle('hidden');
        window.setTimeout(function(){ea.innerHTML = '';ea.classList.toggle('hidden');}, t);
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
}