//
// load.js
// Loads the gif-building script
// We will use SlideShare's JQuery (hoping they will never give up using it)
//

var SLIDEMEME_PATH = "http://slide.memethis.com/";

function slideMeme() {
    if (location.href.indexOf("http://www.slideshare.net/")!=0) {
        alert("This only works with SlideShare");
        return false;
    }
    if (!$("#svPlayerId").length) {
        alert("You need to be on a loaded presentation page to use this");
        return false;
    }
    
    $('<link type="text/css" href="' + SLIDEMEME_PATH + 'js/loading_style.css" rel="stylesheet"></link><div id="darkBackgroundLayer" class="darkenBackground"><img src="'+SLIDEMEME_PATH+'js/ajax-loader.gif"/></div>').appendTo('body');
    
    $.ajax({
        url: 'http://img.slide.memethis.com/slidememe.php?callback=?',
        data: {
            url: location.href
        },
        dataType: "jsonp",
        //timeout: 15000,
        success: function (data, status) {
        var aspas = '%22';
        $("#darkBackgroundLayer").addClass("previewBackground").removeClass("darkenBackground").html('<a href="http://meme.yahoo.com/dashboard/?photo='+data.gif+'&caption=<a href='+aspas+data.url+aspas+'>'+data.title+'</a>">preview - click to meme<br/><img src="'+data.gif+'" /></a>');
        },
        error: function (xOptions, textStatus) {
            $("#darkBackgroundLayer").hide();            
            alert("Error! :-(");
        }
    });

}
slideMeme();

