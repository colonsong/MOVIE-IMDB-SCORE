<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Bootstrap 101 Template</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
   <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  <body>
    <div id="container">
  <div>
    <input type="file" id="file_input" webkitdirectory directory />
    <ul id="dir-tree"></ul>
  </div>
  <div class="center" id="thumbnails"></div>
</div>
<div id="imdb">
<script type="text/html" id="thumbnail_template">
  <div class="thumbnail">
    <div class="image shadow">
      <img src="<%= file.src %>" alt="<%= file.name %>" title="<%= file.name %>" onload="revokeFileURL()" />
      <div class="title"><%= file.name %></div>
      <div class="details"><%= file.type %> @ <%= Math.round(file.fileSize / 1024) %> KB</div>
    </div>
  </div>
</script>
<style>
.imdb
{
	color:red;
}
</style>
<script src="http://html5-demos.appspot.com/static/html5storage/demos/upload_directory/jquery.tree.js" type="text/javascript"></script>
<script>
  // Simple JavaScript Templating
  // John Resig - http://ejohn.org/ - MIT Licensed
  (function(){
    var cache = {};

    this.tmpl = function tmpl(str, data) {
      // Figure out if we're getting a template, or if we need to
      // load the template - and be sure to cache the result.
      var fn = !/\W/.test(str) ?
        cache[str] = cache[str] ||
          tmpl(document.getElementById(str).innerHTML) :

        // Generate a reusable function that will serve as a template
        // generator (and which will be cached).
        new Function("obj",
          "var p=[],print=function(){p.push.apply(p,arguments);};" +

          // Introduce the data as local variables using with(){}
          "with(obj){p.push('" +

          // Convert the template into pure JavaScript
          str
            .replace(/[\r\t\n]/g, " ")
            .split("<%").join("\t")
            .replace(/((^|%>)[^\t]*)'/g, "$1\r")
            .replace(/\t=(.*?)%>/g, "',$1,'")
            .split("\t").join("');")
            .split("%>").join("p.push('")
            .split("\r").join("\\'")
        + "');}return p.join('');");

      // Provide some basic currying to the user
      return data ? fn( data ) : fn;
    };
  })();

  window.URL = window.URL ? window.URL :
               window.webkitURL ? window.webkitURL : window;

  function Tree(selector) {
    this.$el = $(selector);
    this.fileList = [];
    var html_ = [];
    var tree_ = {};
    var pathList_ = [];
    var self = this;

    this.imdb = function(folder,html_object,object_,self_){
    	//console.log(html_object);
    	$.ajax({
			  method: "POST",
			  url: "imdb.php",
			  dataType:"json",
			  data: { q: folder}
			})
			  .done(function( msg ) {

			    folder = folder + '' + '<span class="imdb">' + msg + '</span></br/>';
			    //alert(folder);
			    $('#imdb').html($('#imdb').html() + folder);
			   // html_object.push('<li><a href="#">', msg, '</a>');
			  })
			  .fail(function( jqXHR, textStatus ) {
			  	//alert(folder);
			    $('#imdb').html($('#imdb').html() + folder + '</br/>');
			 // html_object.push('<li><a href="#">', folder, '</a>');
			})
			  .always(function() {
			   //self.render(object[folder]);
			  });

	};

    this.render = function(object) {
      if (object) {
        for (var folder in object) {
          console.log(object[folder]);
          if (!object[folder]) { // file's will have a null value
          	//console.log(folder);
            //html_.push('<li><a href="#" data-type="file">', folder, '</a></li>');
          } else {
            //html_.push('<li><a href="#">', folder, '</a>');
            //在第一層深度下找尋影片名稱
            for(var sub_folder in object[folder])
            {
              //html_.push('<li><a href="#">', sub_folder, '</a>');
              self.imdb(sub_folder,html_object,object_,self_);
            }
            var html_object = html_;
            var object_ = object;
            var self_ = self;
            //self.imdb(folder,html_object,object_,self_);

            //html_.push('<ul>');
            //console.log(folder);
            //self.render(object[folder]);

            //html_.push('</ul>');
          }
          //深度限制一層
          break;
        }
      }
    };

    this.buildFromPathList = function(paths) {
      for (var i = 0, path; path = paths[i]; ++i) {
        var pathParts = path.split('/');
        var subObj = tree_;
        for (var j = 0, folderName; folderName = pathParts[j]; ++j) {
          if (!subObj[folderName]) {
            subObj[folderName] = j < pathParts.length - 1 ? {} : null;

          }
          subObj = subObj[folderName];
        }
      }
      return tree_;
    }

    this.init = function(e) {
      // Reset
      html_ = [];
      tree_ = {};
      pathList_ = [];
      self.fileList = e.target.files;


      // TODO: optimize this so we're not going through the file list twice
      // (here and in buildFromPathList).
      //console.log(self.fileList[0]);
      for (var i = 0, file; file = self.fileList[i]; ++i) {

        pathList_.push(file.webkitRelativePath);

      }

      self.render(self.buildFromPathList(pathList_));

      self.$el.html(html_.join('')).tree({
        expanded: 'li:first'
      });

      // Add full file path to each DOM element.
     var fileNodes = self.$el.get(0).querySelectorAll("[data-type='file']");
      for (var i = 0, fileNode; fileNode = fileNodes[i]; ++i) {
        fileNode.dataset['index'] = i;
      }
    }
  };

  var tree = new Tree('#dir-tree');

  $('#file_input').change(tree.init);

  // Initial resize to force scrollbar in when file loads
  $('#container div:first-of-type').css('height', (document.height - 20) + 'px');
  window.addEventListener('resize', function(e) {
    $('#container div:first-of-type').css('height', (e.target.innerHeight - 20) + 'px');
  });

  function revokeFileURL(e) {
    var thumb = document.querySelector('.thumbnail');
    if (thumb) {
      thumb.style.opacity = 1;
    }
    window.URL.revokeObjectURL(this.src);
  };

  tree.$el.click(function(e) {
    if (e.target.nodeName == 'A' && e.target.dataset['type'] == 'file') {
      var file = tree.fileList[e.target.dataset['index']];

      var thumbnails = document.querySelector('#thumbnails');

      if (!file.type.match(/image.*/)) {
        thumbnails.innerHTML = '<h3>Please select an image!</h3>';
        return;
      }

      thumbnails.innerHTML = '<h3>Loading...</h3>';

      var thumb = document.querySelector('.thumbnail');
      if (thumb) {
        thumb.style.opacity = 0;
      }

      var data = {
        'file': {
          'name': file.name,
          'src': window.URL.createObjectURL(file),
          'fileSize': file.fileSize,
          'type': file.type,
        }
      };

      // Render thumbnail template with the file info (data object).
      //thumbnails.insertAdjacentHTML('afterBegin', tmpl('thumbnail_template', data));
      thumbnails.innerHTML = tmpl('thumbnail_template', data);
    }
  });
</script>


  </body>
</html>
