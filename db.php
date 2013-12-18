<?php

date_default_timezone_set('Asia/Novosibirsk');

        $salt = 'mysalt';
// login => md5(login . salt . password)
        $userPasswords = array(
            'user'  => 'myhashedpassword',
        );

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="dev con"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Cancelled';
            exit;
        } else {
            $fatal = function($message){
                header('WWW-Authenticate: Basic realm="db.php"');
                header('HTTP/1.0 401 Unauthorized');
                die($message);
            };

            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $userPasswords)) {
                $fatal('Unknown user');
            } elseif ($userPasswords[$_SERVER['PHP_AUTH_USER']]
                != md5($_SERVER['PHP_AUTH_USER'] . $salt . $_SERVER['PHP_AUTH_PW'])) {
                $fatal('Bad password');
            }

            if (isset($_GET['logout'])) {
                $fatal('Logout');
            }
        }



// config section


    $instances = array(
		'default' => 'mysql://user:password@localhost/db_name',
        'instance2' => 'mysql://user2:password@some.whe.re/db_name2',
    );

    if (isset($_REQUEST['custom'])) {
        $instances['custom'] = $_REQUEST['custom'];
    }

    $root_path = $_SERVER['DOCUMENT_ROOT'];
    define('BASEPATH', '');



	$dev_sign = '';
    $ip_white_list = array(
		'*',
        '127.0.0.1', // local dev
    );


    // eo config



// check auth
    $forbidden = false;
    if ($dev_sign && strpos($_SERVER['HTTP_USER_AGENT'], $dev_sign) === false && empty($_REQUEST[$dev_sign])) {
        $forbidden = true;
    }
    else {
        $ip_ok = false;
        foreach ($ip_white_list as $ip) {
            if (
                ($_SERVER['REMOTE_ADDR'] == $ip) ||
                ('*' == substr($ip,-1) && substr($_SERVER['REMOTE_ADDR'],0,strlen($ip)-1) == substr($ip,0,-1))
                ) {
                $ip_ok = true;
                break;
            }
        }
        if (!$ip_ok) {
            $forbidden = true;
        }
    }

    if ($forbidden) {
        header('HTTP/1.1 403 Forbidden');
        die($_SERVER['REMOTE_ADDR'].' );');
    }


//http://account.forex4you.vea.dev/db.php?eval=get_table_contents(%27partner_old%27,array(%27where%27=%3E%27limit%2010,10%27,%27notextarea%27=%3E1));&skip_preout=1

set_time_limit(0);
header('Content-type: text/html; charset=UTF-8');

if (!isset($_POST['title'])) {
	$_POST['title'] = 'con';
}

ob_start();
?>
<html>
<head>
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<title><?=$_POST['title']?></title>

<style type="text/css">
.multiline {
	width:49%;
	height:250px;
}

.eval {
	background:url('data:image/gif;base64,R0lGODlheAB4AIAAAPLy8v///yH5BAAAAAAALAAAAAB4AHgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNf2jef6zvf+DwwKh8Si8YhMKpfMpvMJjUpzgKr1iq0esBUuwyu5rrJk8dbaRTfAEXOqDEewIW4FeVI/iQEBvr8PKJfnMCdYuKb2loi3+DBo2KcV1mjy6EiJKLmwh7nZOWJJGGqoaYc2Sjr2KVqayZdpgHrWWrnq2vbpJhtJW7JransYm7j722GcgDqqazvcS4Kcyvrq2SobrYHt/MzLnXfd/KG9fVs+S22Osgf49/eVC9+rHO4Bd/eOKdxdjc6vaq/v0bJ8wegdAzhQXrx+0vAx9GXQX7JQFFeNu3CRXEOJwxw76onosSLCgCCzlZxYKuHIhf8eUgCjcqXLfSoyaqTpkZ9Lmy9PoqRmMZwlnoy4FX2lEtczopOMHsUJbObPh0yVSn0a1OnGcy0PZpn2NGrXduzKXiKplRTDdWTdeZVJRx9Uq1FXvrV7NutVsVNH3vUbl2WaRjJ9TjmMOLHixYwbO34MObLkyZQrW76MObPmzZw7e/4MOrTo0aRLmz6NOrXq1axbu34NO7bs2bRr276NO7fu3bx7+/4NPLjw4cSLG79cAAA7');
}

.query {
	background:url('data:image/gif;base64,R0lGODlheAB4AIAAAPLy8v///yH5BAAAAAAALAAAAAB4AHgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNf2jef6zvf+DwwKh8Si8YhMKpfMpvMJjUp/gKq1erleKdug9ouNgL+SLnVsdqDHkDSPDAho22C5vK62nvWMeN6NQNYAiDOHYfiAqEB4wyjmuMgISTP5F/bIF3m5U9nXGUj4CSOaQHoQmsmZOqHICmja0oq56bp6R6sjm2hb6weKm4M3m/HKGyxsSWwLO8rmaTzra8DcjFwKTYdLLeP8C1zmtj0jeP09XC5tJLtdbC7Uyr6M7RXOvD5PT6ubrY0P1J4OHDBxNgAG3MWLYA1U/qZJathD174F+xSOg0ZukCOL/yUwQsqIzh1HEnBuVWITp9uzlHZYsqy2pmHMid5mujNh86AlNAJz6mRhzYJNhD6n1AxqtEPMpCOQMgUB8anUqVSrWr2KNavWrVy7ev0KNqzYsWTLmj2LNq3atWn13TyVTmbctwvd/vxVDlPef3b14s22d0/gP4M1zr3bqC/gvwgLv1HcmDFhyRHdCrx2GdS7gHTvRAL3OR9lw6Oflc4FeTJcxA4PV/ZpF3bInMdgH5Z9dGgh27eL5p75GrNfuJmJiz5d2N+qqDFS53HsCboqaXKpd07FvJr05HSXd77oevHq4a2PjxdfHv2t17hXt2/9vq7t2brd+048//dS/Wsqb0gXfh4d/+E3YHoBqmagD96xth5ylGX3woLkNRjZgcE5aKFyzj0WXoUJkoZhQZZNeN2IfBXoEIqehSiidQxSCCOCELJFY407FAAAOw==');
}
body {
    background: #eee
}

body, pre, textarea, input, select {
    font-family: monospace;
}

td, th {
    border: 1px solid #aaa;
    white-space: pre-wrap;
    background: #fff;
    font-weight: normal
}

td:hover {
    border: 1px solid #faa;
}

th {
    background: #afa
}

table {
    border-collapse: separate;
    border: 0
}

pre {
    background: #fff;
    border: 1px dashed #DDDDDD;
    padding: 5;
}

textarea {
    width: 100%;
}

</style>

<script type="text/javascript">
//<![CDATA[

var stIsIE = /*@cc_on!@*/false;

sorttable = {
  init: function() {
    // quit if this function has already been called
    if (arguments.callee.done) return;
    // flag this function so we don't do the same thing twice
    arguments.callee.done = true;
    // kill the timer
    if (_timer) clearInterval(_timer);

    if (!document.createElement || !document.getElementsByTagName) return;

    sorttable.DATE_RE = /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/;

    forEach(document.getElementsByTagName('table'), function(table) {
      if (table.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortable(table);
      }
    });

  },

  makeSortable: function(table) {
    if (table.getElementsByTagName('thead').length == 0) {
      // table doesn't have a tHead. Since it should have, create one and
      // put the first table row in it.
      the = document.createElement('thead');
      the.appendChild(table.rows[0]);
      table.insertBefore(the,table.firstChild);
    }
    // Safari doesn't support table.tHead, sigh
    if (table.tHead == null) table.tHead = table.getElementsByTagName('thead')[0];

    if (table.tHead.rows.length != 1) return; // can't cope with two header rows

    // Sorttable v1 put rows with a class of "sortbottom" at the bottom (as
    // "total" rows, for example). This is B&R, since what you're supposed
    // to do is put them in a tfoot. So, if there are sortbottom rows,
    // for backwards compatibility, move them to tfoot (creating it if needed).
    sortbottomrows = [];
    for (var i=0; i<table.rows.length; i++) {
      if (table.rows[i].className.search(/\bsortbottom\b/) != -1) {
        sortbottomrows[sortbottomrows.length] = table.rows[i];
      }
    }
    if (sortbottomrows) {
      if (table.tFoot == null) {
        // table doesn't have a tfoot. Create one.
        tfo = document.createElement('tfoot');
        table.appendChild(tfo);
      }
      for (var i=0; i<sortbottomrows.length; i++) {
        tfo.appendChild(sortbottomrows[i]);
      }
      delete sortbottomrows;
    }

    // work through each column and calculate its type
    headrow = table.tHead.rows[0].cells;
    for (var i=0; i<headrow.length; i++) {
      // manually override the type with a sorttable_type attribute
      if (!headrow[i].className.match(/\bsorttable_nosort\b/)) { // skip this col
        mtch = headrow[i].className.match(/\bsorttable_([a-z0-9]+)\b/);
        if (mtch) { override = mtch[1]; }
	      if (mtch && typeof sorttable["sort_"+override] == 'function') {
	        headrow[i].sorttable_sortfunction = sorttable["sort_"+override];
	      } else {
	        headrow[i].sorttable_sortfunction = sorttable.guessType(table,i);
	      }
	      // make it clickable to sort
	      headrow[i].sorttable_columnindex = i;
	      headrow[i].sorttable_tbody = table.tBodies[0];
	      dean_addEvent(headrow[i],"click", sorttable.innerSortFunction = function(e) {

          if (this.className.search(/\bsorttable_sorted\b/) != -1) {
            // if we're already sorted by this column, just
            // reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted',
                                                    'sorttable_sorted_reverse');
            this.removeChild(document.getElementById('sorttable_sortfwdind'));
            sortrevind = document.createElement('span');
            sortrevind.id = "sorttable_sortrevind";
            sortrevind.innerHTML = stIsIE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
            this.appendChild(sortrevind);
            return;
          }
          if (this.className.search(/\bsorttable_sorted_reverse\b/) != -1) {
            // if we're already sorted by this column in reverse, just
            // re-reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted_reverse',
                                                    'sorttable_sorted');
            this.removeChild(document.getElementById('sorttable_sortrevind'));
            sortfwdind = document.createElement('span');
            sortfwdind.id = "sorttable_sortfwdind";
            sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
            this.appendChild(sortfwdind);
            return;
          }

          // remove sorttable_sorted classes
          theadrow = this.parentNode;
          forEach(theadrow.childNodes, function(cell) {
            if (cell.nodeType == 1) { // an element
              cell.className = cell.className.replace('sorttable_sorted_reverse','');
              cell.className = cell.className.replace('sorttable_sorted','');
            }
          });
          sortfwdind = document.getElementById('sorttable_sortfwdind');
          if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
          sortrevind = document.getElementById('sorttable_sortrevind');
          if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }

          this.className += ' sorttable_sorted';
          sortfwdind = document.createElement('span');
          sortfwdind.id = "sorttable_sortfwdind";
          sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
          this.appendChild(sortfwdind);

	        // build an array to sort. This is a Schwartzian transform thing,
	        // i.e., we "decorate" each row with the actual sort key,
	        // sort based on the sort keys, and then put the rows back in order
	        // which is a lot faster because you only do getInnerText once per row
	        row_array = [];
	        col = this.sorttable_columnindex;
	        rows = this.sorttable_tbody.rows;
	        for (var j=0; j<rows.length; j++) {
	          row_array[row_array.length] = [sorttable.getInnerText(rows[j].cells[col]), rows[j]];
	        }
	        /* If you want a stable sort, uncomment the following line */
	        //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
	        /* and comment out this one */
	        row_array.sort(this.sorttable_sortfunction);

	        tb = this.sorttable_tbody;
	        for (var j=0; j<row_array.length; j++) {
	          tb.appendChild(row_array[j][1]);
	        }

	        delete row_array;
	      });
	    }
    }
  },

  guessType: function(table, column) {
    // guess the type of a column based on its first non-blank row
    sortfn = sorttable.sort_alpha;
    for (var i=0; i<table.tBodies[0].rows.length; i++) {
      text = sorttable.getInnerText(table.tBodies[0].rows[i].cells[column]);
      if (text != '') {
        if (text.match(/^-?[�$�]?[\d,.]+%?$/)) {
          return sorttable.sort_numeric;
        }
        // check for a date: dd/mm/yyyy or dd/mm/yy
        // can have / or . or - as separator
        // can be mm/dd as well
        possdate = text.match(sorttable.DATE_RE)
        if (possdate) {
          // looks like a date
          first = parseInt(possdate[1]);
          second = parseInt(possdate[2]);
          if (first > 12) {
            // definitely dd/mm
            return sorttable.sort_ddmm;
          } else if (second > 12) {
            return sorttable.sort_mmdd;
          } else {
            // looks like a date, but we can't tell which, so assume
            // that it's dd/mm (English imperialism!) and keep looking
            sortfn = sorttable.sort_ddmm;
          }
        }
      }
    }
    return sortfn;
  },

  getInnerText: function(node) {
    // gets the text we want to use for sorting for a cell.
    // strips leading and trailing whitespace.
    // this is *not* a generic getInnerText function; it's special to sorttable.
    // for example, you can override the cell text with a customkey attribute.
    // it also gets .value for <input> fields.

    if (!node) return "";

    hasInputs = (typeof node.getElementsByTagName == 'function') &&
                 node.getElementsByTagName('input').length;

    if (node.getAttribute("sorttable_customkey") != null) {
      return node.getAttribute("sorttable_customkey");
    }
    else if (typeof node.textContent != 'undefined' && !hasInputs) {
      return node.textContent.replace(/^\s+|\s+$/g, '');
    }
    else if (typeof node.innerText != 'undefined' && !hasInputs) {
      return node.innerText.replace(/^\s+|\s+$/g, '');
    }
    else if (typeof node.text != 'undefined' && !hasInputs) {
      return node.text.replace(/^\s+|\s+$/g, '');
    }
    else {
      switch (node.nodeType) {
        case 3:
          if (node.nodeName.toLowerCase() == 'input') {
            return node.value.replace(/^\s+|\s+$/g, '');
          }
        case 4:
          return node.nodeValue.replace(/^\s+|\s+$/g, '');
          break;
        case 1:
        case 11:
          var innerText = '';
          for (var i = 0; i < node.childNodes.length; i++) {
            innerText += sorttable.getInnerText(node.childNodes[i]);
          }
          return innerText.replace(/^\s+|\s+$/g, '');
          break;
        default:
          return '';
      }
    }
  },

  reverse: function(tbody) {
    // reverse the rows in a tbody
    newrows = [];
    for (var i=0; i<tbody.rows.length; i++) {
      newrows[newrows.length] = tbody.rows[i];
    }
    for (var i=newrows.length-1; i>=0; i--) {
       tbody.appendChild(newrows[i]);
    }
    delete newrows;
  },

  /* sort functions
     each sort function takes two parameters, a and b
     you are comparing a[0] and b[0] */
  sort_numeric: function(a,b) {
    aa = parseFloat(a[0].replace(/[^0-9.-]/g,''));
    if (isNaN(aa)) aa = 0;
    bb = parseFloat(b[0].replace(/[^0-9.-]/g,''));
    if (isNaN(bb)) bb = 0;
    return aa-bb;
  },
  sort_alpha: function(a,b) {
    if (a[0]==b[0]) return 0;
    if (a[0]<b[0]) return -1;
    return 1;
  },
  sort_ddmm: function(a,b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3]; m = mtch[2]; d = mtch[1];
    if (m.length == 1) m = '0'+m;
    if (d.length == 1) d = '0'+d;
    dt1 = y+m+d;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3]; m = mtch[2]; d = mtch[1];
    if (m.length == 1) m = '0'+m;
    if (d.length == 1) d = '0'+d;
    dt2 = y+m+d;
    if (dt1==dt2) return 0;
    if (dt1<dt2) return -1;
    return 1;
  },
  sort_mmdd: function(a,b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3]; d = mtch[2]; m = mtch[1];
    if (m.length == 1) m = '0'+m;
    if (d.length == 1) d = '0'+d;
    dt1 = y+m+d;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3]; d = mtch[2]; m = mtch[1];
    if (m.length == 1) m = '0'+m;
    if (d.length == 1) d = '0'+d;
    dt2 = y+m+d;
    if (dt1==dt2) return 0;
    if (dt1<dt2) return -1;
    return 1;
  },

  shaker_sort: function(list, comp_func) {
    // A stable sort function to allow multi-level sorting of data
    // see: http://en.wikipedia.org/wiki/Cocktail_sort
    // thanks to Joseph Nahmias
    var b = 0;
    var t = list.length - 1;
    var swap = true;

    while(swap) {
        swap = false;
        for(var i = b; i < t; ++i) {
            if ( comp_func(list[i], list[i+1]) > 0 ) {
                var q = list[i]; list[i] = list[i+1]; list[i+1] = q;
                swap = true;
            }
        } // for
        t--;

        if (!swap) break;

        for(var i = t; i > b; --i) {
            if ( comp_func(list[i], list[i-1]) < 0 ) {
                var q = list[i]; list[i] = list[i-1]; list[i-1] = q;
                swap = true;
            }
        } // for
        b++;

    } // while(swap)
  }
}

/* ******************************************************************
   Supporting functions: bundled here to avoid depending on a library
   ****************************************************************** */

// Dean Edwards/Matthias Miller/John Resig

/* for Mozilla/Opera9 */
if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", sorttable.init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
    document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            sorttable.init(); // call the onload handler
        }
    };
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
    var _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
            sorttable.init(); // call the onload handler
        }
    }, 10);
}

/* for other browsers */
window.onload = sorttable.init;

// written by Dean Edwards, 2005
// with input from Tino Zijdel, Matthias Miller, Diego Perini

// http://dean.edwards.name/weblog/2005/10/add-event/

function dean_addEvent(element, type, handler) {
	if (element.addEventListener) {
		element.addEventListener(type, handler, false);
	} else {
		// assign each event handler a unique ID
		if (!handler.$$guid) handler.$$guid = dean_addEvent.guid++;
		// create a hash table of event types for the element
		if (!element.events) element.events = {};
		// create a hash table of event handlers for each element/event pair
		var handlers = element.events[type];
		if (!handlers) {
			handlers = element.events[type] = {};
			// store the existing event handler (if there is one)
			if (element["on" + type]) {
				handlers[0] = element["on" + type];
			}
		}
		// store the event handler in the hash table
		handlers[handler.$$guid] = handler;
		// assign a global event handler to do all the work
		element["on" + type] = handleEvent;
	}
};
// a counter used to create unique IDs
dean_addEvent.guid = 1;

function removeEvent(element, type, handler) {
	if (element.removeEventListener) {
		element.removeEventListener(type, handler, false);
	} else {
		// delete the event handler from the hash table
		if (element.events && element.events[type]) {
			delete element.events[type][handler.$$guid];
		}
	}
};

function handleEvent(event) {
	var returnValue = true;
	// grab the event object (IE uses a global event object)
	event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
	// get a reference to the hash table of event handlers
	var handlers = this.events[event.type];
	// execute each event handler
	for (var i in handlers) {
		this.$$handleEvent = handlers[i];
		if (this.$$handleEvent(event) === false) {
			returnValue = false;
		}
	}
	return returnValue;
};

function fixEvent(event) {
	// add W3C standard event methods
	event.preventDefault = fixEvent.preventDefault;
	event.stopPropagation = fixEvent.stopPropagation;
	return event;
};
fixEvent.preventDefault = function() {
	this.returnValue = false;
};
fixEvent.stopPropagation = function() {
  this.cancelBubble = true;
}

// Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
	forEach, version 1.0
	Copyright 2006, Dean Edwards
	License: http://www.opensource.org/licenses/mit-license.php
*/

// array-like enumeration
if (!Array.forEach) { // mozilla already supports this
	Array.forEach = function(array, block, context) {
		for (var i = 0; i < array.length; i++) {
			block.call(context, array[i], i, array);
		}
	};
}

// generic enumeration
Function.prototype.forEach = function(object, block, context) {
	for (var key in object) {
		if (typeof this.prototype[key] == "undefined") {
			block.call(context, object[key], key, object);
		}
	}
};

// character enumeration
String.forEach = function(string, block, context) {
	Array.forEach(string.split(""), function(chr, index) {
		block.call(context, chr, index, string);
	});
};

// globally resolve forEach enumeration
var forEach = function(object, block, context) {
	if (object) {
		var resolve = Object; // default
		if (object instanceof Function) {
			// functions have a "length" property
			resolve = Function;
		} else if (object.forEach instanceof Function) {
			// the object implements a custom forEach method so use that
			object.forEach(block, context);
			return;
		} else if (typeof object == "string") {
			// the object is a string
			resolve = String;
		} else if (typeof object.length == "number") {
			// the object is array-like
			resolve = Array;
		}
		resolve.forEach(object, block, context);
	}
};

// ]]>
</script>



</head>
<body>
<?php


if (isset($_REQUEST['custom'])) {
	$_REQUEST['instance'] = 'custom';
}

if (empty($_REQUEST['instance'])) {
	$_REQUEST['instance'] = 'default';
}


?>
<form action="" method="post">
	<input name="title" style="display:block;width:99%;margin:0;padding:0;font-size:10px;"
	       value="<?=$_POST['title']?>"/>
	<select title="instance" name="instance" style="width:49%">
<?php foreach ($instances as $instance => $tmp) {
		$tmp = parse_url($tmp);
?>
		<option value="<?=$instance?>"<?=$instance == $_REQUEST['instance'] ? 'selected="selected"' : '' ?>>
			<?=$instance?> (<?=$tmp['user'].'@'.$tmp['host'].$tmp['path']?>)
		</option>
<?php } ?>
	</select>
	<input name="mq_query" style="width:39%" value="<?=isset($_POST['mq_query']) ? $_POST['mq_query'] : ''?>"/>
	<select name="mq_server"
	        style="width:10%"><?=empty($_POST['mq_server']) ? $mt4_options : str_replace('value="' . $_POST['mq_server'] . '"', 'value="' . $_POST['mq_server'] . '" selected="selected"', $mt4_options)?></select>
	<br/>
	<textarea name="query" class="multiline query"><?=isset($_POST['query']) ? $_POST['query'] : ''?></textarea>
	<textarea name="eval" class="multiline eval"><?=isset($_POST['eval']) ? $_POST['eval'] : ''?></textarea><br/>
	<input type="submit"/>

</form>

<?php

if (isset($_REQUEST['instance']) && isset($instances[$_REQUEST['instance']])) {
	$tmp = parse_url($instances[$_REQUEST['instance']]);
	$db['default'] = array('driver'=>$tmp['scheme'] ,'hostname'=>$tmp['host'],'username'=>$tmp['user'],'password'=>!empty($tmp['pass']) ? $tmp['pass'] : '','database'=>substr($tmp['path'],1));
}

if (!empty($_REQUEST['skip_preout'])) {
    ob_end_clean();
}

// SQL
if ($db = db($_REQUEST['instance'])) {
	if (isset($_POST['query']) && $_POST['query']) {
		if (strpos($_POST['query'], "###") !== false) {
			$queries = explode("###", $_POST['query']);
		}
		else {
			$queries = array(&$_POST['query']);
		}

		foreach ($queries as &$q) {
			$db->query($q)->show(empty($_POST['format']) ? 'html' : 'serialize');
		}
	}
}
else echo("Could not connect");


// EVAL
if (isset($_REQUEST['eval']) && $_REQUEST['eval']) {
	echo "<h4>Eval</h4>";
	eval($_REQUEST['eval']);
}


if (!empty($_POST['mq_query']) && isset($config['mt4'][$_POST['mq_server']])) {
	echo '<h4>MT Query result</h4><pre>';
	print_r(MQ_Query($_POST['mq_query'], $config['mt4'][$_POST['mq_server']]['host'], $config['mt4'][$_POST['mq_server']]['port']));
	echo '</pre>';
}

xlsReport::finalize();

if (!empty($_REQUEST['skip_preout'])) {
	exit();
}
?>
<a href="#" onclick="this.nextSibling.style.display='';this.style.display='none'">$_SERVER</a><pre style="display:none">
<?php print_r($_SERVER);?>
</pre>

</body>
</html>
<?php


/**
 * @param string $instance
 * @return bool | db_driver
 */
function db($instance = 'default') {
    global $instances;
    static $db = array();

    if (!isset($instances[$instance])) {
        $instances[$instance] = $instance;
    }

    $dsn = $instances[$instance];

    if (!isset($db[$instance])) {
        $pdsn = parse_url($dsn);
        $db_class = $pdsn['scheme'].'_driver';
        $db[$instance] = new $db_class($dsn);
    }
    return $db[$instance];
}

function &array_merge_recursive_distinct(array &$array1, &$array2 = null)
{
    $merged = $array1;

    if (is_array($array2))
        foreach ($array2 as $key => $val)
            if (is_array($array2[$key]))
                $merged[$key] = is_array($merged[$key]) ? array_merge_recursive_distinct($merged[$key], $array2[$key]) : $array2[$key];
            else
                $merged[$key] = $val;

    return $merged;
}

abstract class db_driver {
    protected $link = null;
    /**
     * @var null | resource
     */
    protected $res = null;
    public $lastError = false;
    public $lastQuery = '';
    public $lastTime = 0;
    protected $db_config = array();
    public $queryOptions = array();

    public function __construct($dsn) {
        $dsn = parse_url($dsn);
        $this->db_config = array(
            'username' => $dsn['user'],
            'password' => empty($dsn['pass']) ? '' : $dsn['pass'],
            'hostname' => $dsn['host'],
            'database' => trim($dsn['path'],'/'),
        );
        $this->_connect($this->db_config);
    }

    abstract protected function _connect($db);
    abstract protected function _query($q);


    /**
     * @param $q
     * @return db_driver
     */
    public function query($q) {
        $this->queryOptions = array();
        $this->lastError = '';

        if (substr($q, 0, 5) == '/* o:') {
            foreach (explode(',', substr($q, 5, strpos($q, '*/', 5) - 5)) as $o) {
                $o = trim($o);
                $this->queryOptions [$o]= true;
            }
            print_r($this->queryOptions);
        }

        $this->lastQuery = $q;
        if (!$this->link) {
            return $this;
        }
        //$this->lastError = false;
        $start = microtime(1);
        $this->_query($q);
        $this->lastTime = microtime(1) - $start;
        return $this;
    }


    public function status() {
        $rows = $this->_numRows();

        echo "<h4>".substr($this->lastQuery, 0, 200)."</h4>";
        echo "($rows) " . ($this->lastTime) . "sec <br />\n";
        if ($this->lastError) {
            echo "ERROR: ",$this->lastError,"<br />\n";
        }
    }


    public function showSerialized() {
        $result = array('data' => array(), 'keys' => array(), 'error' => '');
        $data = &$result['data'];

        if ($this->lastError) {
            $result['error'] = $this->lastError;
        }

        $desc = $this->_fetchAssoc();
        if (!$desc) {
            echo base64_encode(serialize($result));
            return;
        }

        $result['keys'] = array_keys($desc);
        $data []= array_values($desc);

        while ($r = $this->_fetchAssoc()) {
            $data []= array_values($r);
        }

        echo base64_encode(serialize($result));
    }

    /**
     * @param string $type
     * @param int $html_escape
     * @return bool|db_driver
     */
    public function show($type='html', $html_escape = 1) {
        if ('serialize' == $type) {
            return $this->showSerialized();
        }

        $rows = $this->_numRows();

        echo "<h4>$this->lastQuery</h4>";
        echo "($rows) " . ($this->lastTime) . "sec <br />\n";
        if ($this->lastError) {
            echo "ERROR: ",$this->lastError,"<br />\n";
        }

        if (!$rows) {
         //   return false;
        }

        // separators
        if ('jira' == $type) {
            $head = '<pre>';
            $tr_1 = '';
            $tr_2 = "\n";
            $th_1 = "||\t";
            $th_2 = '';
            $th_3 = "\t||";
            $td_1 = "|\t";
            $td_2 = '';
            $td_3 = "\t|";
            $tail = '</pre>';
        }
        else {
            $head = '<table class="sortable"><tbody>';
            $tr_1 = '<tr>';
            $tr_2 = '</tr>';
            $th_1 = '<th>';
            $th_2 = '</th>';
            $th_3 = '';
            $td_1 = '<td>' . (!empty($this->queryOptions['pre']) ? '<pre>' : '');
            $td_2 = (!empty($this->queryOptions['pre']) ? '</pre>' : '') . '</td>';
            $td_3 = '';
            $tail = '</tbody></table>';
        }

        echo $head;

        if (empty($this->queryOptions['rotate'])) {
            $desc = $this->_fetchAssoc();
            if (!$desc) {
                return $this;
            }
            $l = '';
            $h = '';
            foreach ($desc as $k => $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($html_escape) {
                    $d = str_replace('<', '&lt;', $d);
                }

                $h .= $th_1 . $k . $th_2;
                $l .= $td_1 . $d . $td_2;
            }
            $h .= $th_3;
            $l .= $td_3;
            echo $tr_1, $h, $tr_2, $tr_1, $l, $tr_2;

            while ($desc = $this->_fetchAssoc())
            {
                echo $tr_1;
                foreach ($desc as $d) {
                    if (is_null($d)) {
                        $d = 'NULL';
                    }
                    elseif ($html_escape) {
                        $d = str_replace('<', '&lt;', $d);
                    }
                    echo $td_1 . $d . $td_2;
                }
                echo $td_3, $tr_2;
            }
        }


        // rotated table
        else {
            $desc = $this->_fetchAssoc();
            if (!$desc) {
                return $this;
            }

            $rows = array();
            $i = 0;
            foreach ($desc as $k => $d) {
                if (is_null($d)) {
                    $d = 'NULL';
                }
                elseif ($html_escape) {
                    $d = str_replace('<', '&lt;', $d);
                }
                $rows[++$i] = $th_1 . $k . $th_2 . $td_1 . $d . $td_2;
            }

            while ($desc = $this->_fetchAssoc()) {
                $i = 0;
                foreach ($desc as $d) {
                    if (is_null($d)) {
                        $d = 'NULL';
                    }
                    elseif ($html_escape) {
                        $d = str_replace('<', '&lt;', $d);
                    }

                    $rows[++$i] .= $td_1 . $d . $td_2;
                }
            }

            foreach ($rows as $line) {
                echo $tr_1 . $line . $tr_2;
            }
        }



        echo $tail;
        return $this;
    }


    public static $increment = 1;
    public function draw($options) {
        $id = ++self::$increment;


        $desc = $this->_fetchAssoc();
        $keys = array_keys($desc);

        $key = $keys[0];
        array_shift($keys);

        $series = array();
        if (isset($desc['name']) && isset($desc['value'])) {
            do {
                if (!isset($series[$desc['name']])) {
                    $series[$desc['name']] = array(
                        'name' => $desc['name'],
                        'type' => 'spline',
                        'data' => array()
                    );
                }

                $series[$desc['name']]['data'] []= array(1 * $desc[$key], 1 * $desc['value']);

            } while ($desc = $this->_fetchAssoc());

        }
        else {
            foreach ($keys as $k) {
                $series [$k]= array(
                    'name' => $k,
                    'type' => 'spline',
                    'data' => array()
                );

            }

            do {
                foreach ($keys as $k) {
                    $series[$k]['data'] []= array(1 * $desc[$key], 1 * $desc[$k]);
                }
            } while ($desc = $this->_fetchAssoc());
        }


        $chartOptions = array(
            'title' => false,

            'chart' => array(
                'renderTo' => 'container-' . $id,
                'zoomType' => 'y',
                'resetZoomButton' => array(
                    'position' => array(
                        'align' => 'left', // by default
                        'verticalAlign' => 'bottom', // by default
                        'x' => 0,
                        'y' => -130,
                    )
                )
            ),

            'legend' => array(
                'enabled' => true,
                //'layout' => 'vertical',
                'verticalAlign' => 'top'
            ),

            'plotOptions' => array(
                'series' => array(
                    'marker' => array(
                        'enabled' => false
                    )
                )
            ),

            'tooltip' => array(
                'crosshairs' => array(true, true),
                'shared' => false,
            ),

            'credits' => array(
                'enabled' => false
            )

        );


        if ($options) {
            $chartOptions = array_merge_recursive_distinct($chartOptions, $options);
        }

        $chartOptions['series'] = array_values($series);




        ?>

        <script src="http://code.highcharts.com/stock/highstock.js" type="text/javascript"></script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <div id="container-<?=$id?>"></div>
        <script>

            (function(){
                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });

                $('#container-<?=$id?>').highcharts(
                    <?= json_encode($chartOptions); ?>
                );

            })();
        </script>
    <?php
    }

    /**
     * @param string $title
     * @return bool|db_driver
     */
    public function xls($title = 'default') {
        $rows = $this->_numRows();
        if (!$rows) return false;

        xlsReport::addSheet($title);
        while ($r = $this->_fetchAssoc()) {
            xlsReport::addRow($r);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function all() {
        $result = array();
        while ($r = $this->_fetchAssoc()) {
            $result []= $r;
        }
        return $result;
    }

    public function getAll() {
        $result = array();

        while ($r = $this->_fetchAssoc()) {
            $result []= $r;
        }
        return $result;
    }


    /**
     * @param $search
     * @return void
     */
    function fullSearch($search) {
        $tables = array();
        $res = mysql_query("show table status");
        while ($r = mysql_fetch_assoc($res)) $tables[$r['Name']] = $r['Rows'];

        foreach ($tables as $table => $count)	{
            echo "$table :: $count<br>";

            if (!$count || $count>10000) {
                continue;
            }


            $q = "SELECT * FROM $table WHERE ";
            $res = mysql_query("DESC $table");
            while ($r = mysql_fetch_assoc($res)) $q .= '`'.$r['Field'] . "` LIKE '%$search%' OR ";
            $q = substr($q, 0, -3);
            echo_query($q, 0);
            //  echo $q."<br />";
        }
    }


    protected function getSqlDumpQueryEcho($sql, $options) {
        if (!empty($options['copy_to'])) {
            db($options['copy_to'])->query($sql)->status();

        }
        else {
            echo $sql;
        }
        
    }

 /**
 * @param bool $options
 * @return bool
 */
function getSqlDump($options = false) {
    if (!$options) {
        return false;
    }

	if (empty($options['estimate'])) {

        if (empty($options['copy_to'])) {
            ob_end_clean();
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="dump_'.date('Y-m-d').'_'.
                     $this->db_config['database'].'_'.
                     $this->db_config['hostname'].'.sql"');
        }
		$this->query("SET NAMES UTF8");


		$this->getSqlDumpQueryEcho("SET NAMES UTF8;\n", $options);
        $this->getSqlDumpQueryEcho("SET FOREIGN_KEY_CHECKS=0;\n\n\n", $options);

	}
	else {
		echo '<pre>';
		$total_size = 0;
		$total_rows = 0;
	}

	if (!empty($options['tables_like'])) {
		if (!isset($options['tables'])) {
			$options['tables'] = array();
		}
		$res = $this->query("SHOW TABLES LIKE '$options[tables_like]'");
		while ($r = $this->fetchRow($res)) {
			$options['tables'][] = $r[0];
		}
		unset($options['tables_like']);
	}

	$this->query("SHOW TABLE STATUS");
    $table_status = array();
	while ($r = $this->_fetchAssoc()) {
        $table_status []= $r;
    }
	// if ($r['Engine']) {
	// }
    foreach ($table_status as $r) {
	  $this->query("SHOW CREATE TABLE $r[Name]");
	  if (($ct = $this->_fetchAssoc()) && !empty($ct['Create Table'])) {
		if (!empty($options['tables']) && !in_array($r['Name'],$options['tables'])) {
			if (!empty($options['estimate'])) {
				echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			}
			continue;
		}

		if (!empty($options['skip_tables']) && in_array($r['Name'],$options['skip_tables'])) {
			if (!empty($options['estimate'])) {
				echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			}
			continue;
		}

		if (empty($options['estimate'])) {
            $this->getSqlDumpQueryEcho("DROP TABLE IF EXISTS $r[Name];\n\n", $options);
            $this->getSqlDumpQueryEcho($ct['Create Table'].";\n\n", $options);
		}


		if (!empty($options['skip_content']) && in_array($r['Name'],$options['skip_content'])) {
			if (!empty($options['estimate'])) {
				echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			}
			continue;
		}

		if (!empty($options['skip_content_maxrows']) && $r['Rows']>=$options['skip_content_maxrows']) {
			if (!empty($options['estimate'])) {
				echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			}
			continue;
		}

		if (!empty($options['skip_content_maxdata']) && $r['Data_length']>=$options['skip_content_maxdata']) {
			if (!empty($options['estimate'])) {
				echo "skipping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			}
			continue;
		}

	    $op = array('notextarea'=>1,'splitsize'=>900000);
		if (!empty($options['limit'])) {
			$op['where'] = '';
			if (!empty($options['order'][$r['Name']])) {
				$op['where'] .= 'ORDER BY '.$options['order'][$r['Name']];
			}
			$op['where'] =' LIMIT '.$options['limit'];
		}

        if (!empty($options['copy_to'])) {
            $op['copy_to'] = $options['copy_to'];
        }

		if (!empty($options['estimate'])) {
			echo "dumping $r[Name]: $r[Rows] rows, $r[Data_length] total bytes\n";
			$total_rows += $r['Rows'];
			$total_size += $r['Data_length'];
		}
		else {
		    $this->getTableContents($r['Name'], $op);
		}
	  }

		if (empty($options['estimate'])) {
			echo "\n\n\n";
		}
	}

	if (!empty($options['estimate'])) {
		echo "Total: $total_rows rows, $total_size total bytes\n";
		echo '</pre>';
		return false;
	}

    $this->getSqlDumpQueryEcho("DELIMITER ###\n\n", $options);
	$this->query("SHOW TRIGGERS");
	while ($r = $this->_fetchAssoc()) {
        $this->getSqlDumpQueryEcho("DROP TRIGGER IF EXISTS `$r[Trigger]`;\n###\n", $options);
        $this->getSqlDumpQueryEcho("CREATE TRIGGER `$r[Trigger]` $r[Timing] $r[Event] ON `$r[Table]` FOR EACH ROW\n", $options);
        $this->getSqlDumpQueryEcho("$r[Statement];", $options);
        $this->getSqlDumpQueryEcho("###\n", $options);
	}
    $this->getSqlDumpQueryEcho("DELIMITER ;\n\n", $options);
	exit();
}

function getTableContents($table = '', $options = array()) {
    if (!$table) {
        return false;
    }

    if (!empty($options['file'])) {
        $options['notextarea'] = true;
        $options['splitsize'] = 900000;
        ob_end_clean();
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="dump_'.date('Y-m-d').'_'.
               $this->db_config['database'].'.'.
               $table.'_'.$this->db_config['hostname'].'.sql"');
        $this->query("SET NAMES UTF8");
        echo "SET NAMES UTF8;\n";
    }

    $select = "SELECT " . (isset($options['select']) ? $options['select'] : '*') . " FROM $table " . (isset($options['where']) ? $options['where'] : '');
    if (!empty($options['raw_select'])) {
        $select = $options['raw_select'];
    }

    $this->query($select);

    $qh = '';
    $q = '';
    while ($r = $this->_fetchAssoc())
    {
        if (!$qh) {
            $qh = "INSERT " . (empty($options['ignore']) ? '' : 'IGNORE ') . "INTO `$table` (`" . implode('`,`', array_keys($r)) . "`) VALUES \n";
            $q = $qh;
        }
        if (isset($options['skip'])) foreach ($options['skip'] as $f) if (isset($r[$f])) unset($r[$f]);
        if (isset($options['fields'])) foreach ($r as $f => $v) if (!in_array($f, $options['fields'])) unset($r[$f]);
        if (isset($options['change'])) foreach ($r as $f => $v) if (isset($options['change'][$f])) $r[$f] = $options['change'][$f];

        foreach ($r as $k => $v) {
            if (is_null($v)) $r[$k] = 'NULL';
            else $r[$k] = "'" . $this->_escape($v) . "'";
        }
        $dq = "(" . implode(",", $r) . "),\n";

        if (!empty($options['splitsize']) && (strlen($q) + strlen($dq) >= $options['splitsize'])) {
            $q = substr($q, 0, -2) . ';';
            if (empty($options['notextarea'])) {
                echo '<text', 'area style="width:100%;height:100px">';
            }

            if (!empty($options['copy_to'])) {
                $this->getSqlDumpQueryEcho($q, $options);
            }
            else {
				if (!empty($options['base64'])) {
					echo base64_encode($q);
				}
				else echo $q;
            }

            if (empty($options['notextarea'])) {
                echo '</text', 'area>';
            }
            $q = $qh . $dq;
        }
        else {
            $q .= $dq;
        }

    }

    $q = $q ? substr($q, 0, -2) . ';' : $q;
    if (empty($options['notextarea'])) {
        echo '<text', 'area style="width:100%;height:100px">';
    }

    if (!empty($options['copy_to'])) {
        $this->getSqlDumpQueryEcho($q, $options);
    }
    else {
				if (!empty($options['base64'])) {
					echo base64_encode($q);
				}
				else echo $q;
    }

    if (!empty($options['file'])) {
        exit();
    }

    if (empty($options['notextarea'])) {
        echo '</text', 'area>';
    }
    return true;
}


}

class mysql_driver extends db_driver {
    public function _numRows() {
        return mysql_affected_rows($this->link);
    }
    public function _query($q) {
        if (!$this->res = mysql_query($q, $this->link)) {
            $this->lastError = mysql_errno($this->link).' '.mysql_error($this->link);
        }
        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        return mysql_fetch_assoc($this->res);
    }
    public function _connect($db) {
        $this->link = mysql_connect($db['hostname'], $db['username'], $db['password']);
        if (!$this->link) {
            $this->lastError = mysql_errno().' '.mysql_error();
            return false;
        }
        $this->query("USE $db[database]");
        $this->query("SET NAMES UTF8");
    }
    public function _escape($s) {
        return mysql_real_escape_string($s, $this->link);
    }

}


class devCon_driver extends db_driver {
    private $user;
    private $pass;
    private $url;

    public function _numRows() {
        return count($this->res['data']);
    }
    public function _query($q) {

        $context = stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
                            . "Authorization: Basic " . base64_encode("$this->user:$this->pass"),
                'content' => http_build_query(
                    array(
                        'format' => 'serialize',
                        'skip_preout' => '1',
                        'query' => $q,
                    )
                )
            )
        ));
        $data = file_get_contents($this->url, false, $context);
        if (!$data) {
            $this->lastError = 'No data';
        }

        //echo str_replace('<','&lt;',$data);

        $this->res = unserialize(base64_decode($data));
        if ($this->res['error']) {
            $this->lastError = $this->res['error'];
        }

        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        if ($row = each($this->res['data'])) {
            $row = array_combine($this->res['keys'], $row['value']);

            return $row;
        }
        else {
            return null;
        }
    }
    public function _connect($db) {
        $this->user = $db['username'];
        $this->pass = $db['password'];

        $this->link = true;
        $this->url = 'http://' . $db['hostname'] . '/' . $db['database'];
    }
    public function _escape($s) {
        return mysql_escape_string($s);
    }

}




class pgsql_driver extends db_driver {
    public function _numRows() {
        if (!$this->res || !$this->link) {
            return false;
        }
        return pg_num_rows($this->res);
    }
    public function _query($q) {
        if (!$this->link) {
            return $this;
        }

        // mysql query hacks
        if (strlen($q) < 1000) {
            $tmp = strtoupper(trim($q));
            if ('SHOW TABLES' == $tmp) {
                $q = "SELECT tablename AS Tables_in_DB FROM pg_catalog.pg_tables WHERE schemaname='public'";
            }
            elseif ('SHOW PROCESSLIST' == $tmp) {
                $q = "SELECT * FROM pg_stat_activity";
            }
            elseif (substr($tmp, 0, 5) == 'DESC ') {
                $q = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".trim(substr($q, 5))."'";
            }
        }

        if (!$this->res = pg_query($this->link, $q)) {
            $this->lastError = pg_last_error($this->link);
        }
        return $this;
    }
    public function _fetchAssoc() {
        if (!$this->res) {
            return false;
        }
        return pg_fetch_assoc($this->res);
    }
    public function _connect($db) {
        if (!$this->link = pg_connect("host=$db[hostname] dbname=$db[database] user=$db[username] password=$db[password] ".
                                 "options='--client_encoding=UTF8'")) {
            $this->lastError = 'Connection error.';
        }
    }
    public function _escape($s) {
        return pg_escape_string($this->link, $s);
    }


}

class xlsReport {
    static private $book = null;
    static private $sheet = null;
    static private $sheet_headers = array();
    static private $sheet_line = 0;
    /**
     * Init xls output
     * @static
     * @return void
     */
    static private function init() {
        if (is_null(self::$book)) {
            // Loading library
            set_include_path(get_include_path() . PATH_SEPARATOR . './_system/application/libraries/PEAR/');
            require_once 'Spreadsheet/Excel/Writer.php';
            // Creating a workbook
            self::$book = new Spreadsheet_Excel_Writer();
            self::$book->setTempDir(sys_get_temp_dir());

            // sending HTTP headers
            self::$book->send('report.xls');
            self::$book->setVersion(8);

            ob_end_clean();
        }
    }
    /**
     * Add new sheet to xls output (fill with data optionally)
     * @static
     * @param string $title
     * @param null $data
     * @return void
     */
    static public function addSheet($title = 'default', $data = null) {
        self::init();
        self::$sheet =& self::$book->addWorksheet($title);
        // Encoding
        self::$sheet->setInputEncoding('UTF-8');
        self::$sheet_headers = array();
        self::$sheet_line = 0;

        if (is_array($data)) {
            foreach ($data as $r) {
                self::addRow($r);
            }
        }
    }
    /**
     * Add row to current sheet
     * @static
     * @param $r
     * @return void
     */
    static public function addRow($r) {
        if (!self::$sheet) {
            self::addSheet();
        }

        if (!self::$sheet_headers) {
            $i = 0;
            foreach ($r as $k => $v) {
                self::$sheet_headers[$k] = $i;
                self::$sheet->write(self::$sheet_line, $i++, $k);
                self::$sheet->setColumn(self::$sheet_line, $i, 15);
            }
            ++self::$sheet_line;
        }
        foreach (self::$sheet_headers as $k => $i) {
            self::$sheet->write(self::$sheet_line, $i, !isset($r[$k]) ? '' : $r[$k]);
        }
        ++self::$sheet_line;
    }
    /**
     * Finalizes xls output and exits in case of xls started
     * @static
     * @return void
     */
    static public function finalize() {
        if (self::$book) {
            // Let's send the file
            self::$book->close();
            exit();
        }
    }
}




/**
 * Query to MT4
 *
 * @param $query
 * @param $host
 * @param $port
 * @return string
 */
function MQ_Query($query, $host, $port) {

	$result = 'error';

	/* open socket */
	$ptr = @fsockopen($host, $port, $errno, $errstr, 10);

	/* check connection */
	if ($ptr) {
		/* send request */
		if (fwrite($ptr, "W$query\nQUIT\n") != FALSE) {
			$result = '';
			/* receive answer */
			while (!feof($ptr)) {
				$line = fgets($ptr, 128);
				if ($line == "end\r\n") {
					break;
				}
				$result .= $line;
			}
		}
		else {
			$result = $errstr . ' (' . $errno . ')';
		}
		fclose($ptr);
	}
	return $result;
}


// shortcuts

function help() {
	echo "<code><b>functions available: <br />";
    echo "<b>switch_db('replica');</b> switch current database to another instance,'mysql://root@localhost/test' could also be used<br />";
	echo "<b>echo_query('SELECT * FROM table');</b> perform mysql query and print result in html table<br />";
	echo "<b>jira_query('SELECT * FROM table');</b> perform mysql query and print result in jira table format<br />";
	echo "<b>xls_query('SELECT * FROM table','sheet_name');</b> perform mysql query and store result as xls file, each function call creates new worksheet in report<br />";
	echo "<b>get_sql_dump([options_array]);</b> export db content, run without parameters for help<br />";
	echo "<b>get_table_contents([options_array]);</b> export table content, run without parameters for help<br />";
	echo "<b>MQ_Query(query, host, port);</b> perform MT4 query<br />";
	//echo "<b>full_search(\$search);</b> perform LIKE '%\$search%' through all fields of all tables of current db, BEWARE O_O MAY BURN YOUR SOUL<br />";
	echo "</code>";
}

function fetchTable($table, $page_size = 1000, $db_url = 'https://account-trunk.forex4you.org/db.php') {
    ob_end_flush();
    echo "fetching $table (page = $page_size) from $db_url...<br />\n";
    flush();
    $offset = 0;
    $iteration = 0;
    $max_iterations = 500;
    while ($q = file_get_contents($url = $db_url . '?eval=get_table_contents%28%27' . $table . '%27,array%28%27where%27=%3E%27limit%20' . $offset . ',' . $page_size . '%27,%27notextarea%27=%3E1%29%29;&skip_preout=1&vea@dev')) {
        $iteration++;
        echo $url . "<br />\n";
        echo "performing query $iteration...<br />\n";
        flush();
        echo_query($q);
        if ($iteration > $max_iterations) {
            return false;
        }
        $offset += $page_size;
    }
}

function echo_query($q) {
    global $db;
    $db->query($q)->show();
}

function draw_query($q, $options = array()) {
    global $db;
    $db->query($q)->draw($options);
}


function jira_query($q) {
    global $db;
    $db->query($q)->show('jira');
}

function xls_query($q, $title) {
    global $db;
    $db->query($q)->xls($title);
}

function switch_db($instance) {
    global $db;
    $db = db($instance);
    return $db;
}

function phpdoc($table) {
	global $db;
    echo "<pre>/**\n";
	foreach ($db->query("DESC $table")->all() as $r) {
        echo ' * @property $' . $r['Field'] . "\n";
    }
    echo " **/\n</pre>";

}


function get_sql_dump($options = false) {
    if (false === $options) {
            echo "<code><b>get_sql_dump(\$options_array)</b><br />options may be: <br />";
            echo "<b>'tables'</b> array of tables to dump, if set other tables will be skipped, default not set<br />";
            echo "<b>'tables_like'</b> string to match table name (ex. label%), if set other tables will be skipped, default not set<br />";
            echo "<b>'skip_tables'</b> array of tables to skip, default not set<br />";
            echo "<b>'skip_content'</b> array of tables to skip content, CREATE and DROP will exist, default not set<br />";
            echo "<b>'skip_content_maxrows'</b> if is set and table has more or eq rows, content will be skipped, default not set<br />";
            echo "<b>'skip_content_maxdata'</b> if is set and table has more or eq data length, content will be skipped, default not set<br />";
            echo "<b>'limit'</b> max number of rows to dump from table, default not set<br />";
            echo "<b>'order'</b> key-value array of table:order_condition pairs for limit, default not set <br />";
            echo "<b>'estimate'</b> estimate size and row count only without dumping, default not set <br />";
            echo "<b>'copy_to'</b> execute dump in another database (ex. 'mysql://user:pass@copyhost/copybase'), default not set <br />";
            echo "<br />example: get_sql_dump(array('limit'=>20000,'skip_content_maxrows'=>50000));</code>";
        return false;
    }

    global $db;
    $db->getSqlDump($options);
}

function get_table_contents($table = '', $options = array()) {
    if (!$table) {
        echo "<code><b>get_table_contents('table_name', \$options_array)</b><br />options may be: <br />";
        echo "<b>'select'</b> default '*'<br />";
        echo "<b>'where'</b> where section with 'WHERE' word, default empty, example 'WHERE id>10'<br />";
        echo "<b>'skip'</b> fields to drop from resulting query, default array(), example array('insert_date','meta_info')<br />";
        echo "<b>'fields'</b> fields to require in resulting query, if not empty others will be omitted, default array(), example array('id','name')<br />";
        echo "<b>'change'</b> change data array, default array(), example array('office_id'=>'33', 'name'=>'Alternative Name')<br />";
        echo "<b>'splitsize'</b> split resulting query by size, default 0<br />";
        echo "<b>'ignore'</b> use INSERT IGNORE, default false<br />";
        echo "<b>'update'</b> use INSERT .. ON DUPLICATE KEY UPDATE $1, default ''<br />";
        echo "<b>'notextarea'</b> do not put query to textarea, default null<br />";
        echo "<b>'file'</b> save query as file, default null<br />";
        echo "<br />example: get_table_contents('investbag_users',array('where'=>\"WHERE currency='USD'\",'skip'=>array('is_dealer'),'splitsize'=>900000));</code>";
        return false;
    }

    global $db;
    $db->getTableContents($table, $options);
}

