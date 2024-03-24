/**
*@ Autor: Dark Neo
*@ Fecha: 2017-22-12
*@ Version: 2.9.9
*@ Contacto: neogeoman@gmail.com
*/
$(function(){
	$('.avatarep_bg').avatarep_bg();
	var NavaT = 0;						
	var myTimer;
	if (typeof lpaname === 'undefined')
		return false;
	$('a[class^="'+lpaname+'"]').on('click', function (e) {
		e.preventDefault();	
		return false;
	});
	$('a[class^="'+lpaname+'"]').on('mouseover', function(){
	var Nava = $(this).attr('class');
	var Navan = lpaname.length;
	Nava = Nava.substr(Navan);
	var ID_href = $(this).attr("href");
	var Data = "id=" + ID_href;
	var lpamyid = Nava;
	$(this).append('<div id="'+lpaname+'mod'+lpamyid+'" class="modal_avatar"></div>');	
	console.log(NavaT);
		myTimer = setTimeout( function()
		{			
			$.ajax({
				url:ID_href,
				data:Data,
				type:"post",
				dataType:"json",
				beforeSend:function()
				{
					$("div#"+lpaname+"mod"+lpamyid).css({
						"display": "inline-block",
						"width": 320														
					});						
					$("div#"+lpaname+"mod"+lpamyid).fadeIn("fast");										
					$("div#"+lpaname+"mod"+lpamyid).html("<center><img src='images/spinner_big.gif' alt='Retrieving Data'><br>Loading...<br></center>");
				},									
				success:function(res){	
					NavaT = lpamyid;
					$("div#"+lpaname+"mod"+lpamyid).html(res);
				}
			});	
		return false;
		}, lpatimer);				
	});
	$('a[class^="'+lpaname+'"]').on("mouseout", function(){
		var Nava = $(this).attr('class');
		var Navan = lpaname.length;
		Nava = Nava.substr(Navan);
		var lpamyid = Nava;		
		if(myTimer)
		clearTimeout(myTimer);				
		$("div#"+lpaname+"mod"+lpamyid).fadeOut("fast").remove();
		$(this).stop();
	});
});
(function ($) {

    var unicode_charAt = function(string, index) {
        var first = string.charCodeAt(index);
        var second;
        if (first >= 0xD800 && first <= 0xDBFF && string.length > index + 1) {
            second = string.charCodeAt(index + 1);
            if (second >= 0xDC00 && second <= 0xDFFF) {
                return string.substring(index, index + 2);
            }
        }
        return string[index];
    };

    var unicode_slice = function(string, start, end) {
        var accumulator = "";
        var character;
        var stringIndex = 0;
        var unicodeIndex = 0;
        var length = string.length;

        while (stringIndex < length) {
            character = unicode_charAt(string, stringIndex);
            if (unicodeIndex >= start && unicodeIndex < end) {
                accumulator += character;
            }
            stringIndex += character.length;
            unicodeIndex += 1;
        }
        return accumulator;
    };

    $.fn.avatarep_bg = function (options) {

        // Defining Colors
        var colors = ["#1abc9c", "#16a085", "#f1c40f", "#f39c12", "#2ecc71", "#27ae60", "#e67e22", "#d35400", "#3498db", "#2980b9", "#e74c3c", "#c0392b", "#9b59b6", "#8e44ad", "#bdc3c7", "#34495e", "#2c3e50", "#95a5a6", "#7f8c8d", "#ec87bf", "#d870ad", "#f69785", "#9ba37e", "#b49255", "#b49255", "#a94136"];
        var finalColor;

        return this.each(function () {

            var e = $(this);
            var settings = $.extend({
                // Default settings
                name: 'Name',
                color: null,
                seed: 0,
                charCount: 1,
                textColor: '#ffffff',
                height: 44,
                width: 44,
                fontSize: 30,
                fontWeight: 400,
                fontFamily: 'HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica, Arial,Lucida Grande, sans-serif'
            }, options);

            // overriding from data attributes
            settings = $.extend(settings, e.data());

            // making the text object
            var c = unicode_slice(settings.name, 0, settings.charCount).toUpperCase();
            var cobj = $('<text text-anchor="middle"></text>').attr({
                'y': '50%',
                'x': '50%',
                'dy' : '0.35em',
                'pointer-events':'auto',
                'fill': settings.textColor,
                'font-family': settings.fontFamily
            }).html(c).css({
                'font-weight': settings.fontWeight,
                'font-size': settings.fontSize+'px',
            });

            if(settings.color == null){
                var colorIndex = Math.floor((c.charCodeAt(0) + settings.seed) % colors.length);
                finalColor = colors[colorIndex]
            }else{
                finalColor = settings.color
            }

            var svg = $('<svg></svg>').attr({
                'xmlns': 'http://www.w3.org/2000/svg',
                'pointer-events':'none',
                'width': settings.width,
                'height': settings.height
            }).css({
                'background-color': finalColor,
                'width': settings.width+'px',
                'height': settings.height+'px'
            });

            svg.append(cobj);
           // svg.append(group);
            var svgHtml = window.btoa(unescape(encodeURIComponent($('<div>').append(svg.clone()).html())));

            e.attr("src", 'data:image/svg+xml;base64,' + svgHtml);

        })
    };

}(jQuery));