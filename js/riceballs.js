$(function(){
  // Insert a clickable icon list after the textbox
  $('textarea#Form_Body').livequery(function() {
	// If we're not displaying a regular comment stop 
	if ($(this).parent().parent().parent().parent().parent().attr('id') != 'Content') return;

	// Pick up the emoticons from the def list
	var emoticons = gdn.definition('RiceBalls', false);

	if (emoticons) {
		emoticons = eval("("+$.base64Decode(emoticons)+")");
	}

	var buts = '';
	var last = '';
    for (e in emoticons) {
		// no duplicates
		if (last != emoticons[e]) {
		  last = emoticons[e];
		  buts += '<a class="RiceBallBox RiceBall RiceBall'+emoticons[e]+'" title="'+emoticons[e]+'"><span>'+e+'</span></a>';
		}
    }
    $(this).before("<div class=\"RiceBallsWrapper\">\
      <a class=\"RiceBallsDropdown\"><span>RiceBalls</span></a> \
      <div class=\"RiceBallContainer Hidden\">"+buts+"</div> \
    </div>");
    
    $('.RiceBallsDropdown').live('click', function() {
      if ($(this).hasClass('RiceBallsDropdownActive'))
        $(this).removeClass('RiceBallsDropdownActive');
      else
        $(this).addClass('RiceBallsDropdownActive');

      $(this).next().toggle();
      return false;
    });
    
    // Hide emotify options when previewing
    $('form#Form_Comment').bind("PreviewLoaded", function(e, frm) {
      frm.find('.RiceBallsDropdown').removeClass('RiceBallsDropdownActive');
      frm.find('.RiceBallsDropdown').hide();
      frm.find('.RiceBallContainer').hide();
    });
    
    // Reveal emotify dropdowner when write button clicked
    $('form#Form_Comment').bind('WriteButtonClick', function(e, frm) {
      frm.find('.RiceBallsDropdown').show();
    });
    
    // Hide emoticon box when textarea is focused
    $('textarea#Form_Body').live('focus', function() {
      var frm = $(this).parents('form');
      frm.find('.RiceBallsDropdown').removeClass('RiceBallsDropdownActive');
      frm.find('.RiceBallContainer').hide();
    });

    // Put the clicked emoticon into the textarea
    $('.RiceBallBox').live('click', function() {
      var emoticon = $(this).find('span').text();
      var textbox = $(this).parents('form').find('textarea#Form_Body');
      var txt = $(textbox).val();
      if (txt != '')
        txt += ' ';
      $(textbox).val(txt + emoticon + ' ');
      var container = $(this).parents('.RiceBallContainer');
      $(container).hide();
      $(container).prev().removeClass('RiceBallsDropdownActive');
      
      // If cleditor is running, update it's contents
      var ed = $(textbox).get(0).editor;
      if (ed) {
        // Update the frame to match the contents of textarea
        ed.updateFrame();
        // Run emotify on the frame contents
        var Frame = $(ed.$frame).get(0);
        var FrameBody = null;
        var FrameDocument = null;
        
        // DOM
        if (Frame.contentDocument) {
           FrameDocument = Frame.contentDocument;
           FrameBody = FrameDocument.body;
        // IE
        } else if (Frame.contentWindow) {
           FrameDocument = Frame.contentWindow.document;
           FrameBody = FrameDocument.body;
        }
        $(FrameBody).html(emotify($(FrameBody).html()));
        var webRoot = gdn.definition('WebRoot', '');
        var ss = document.createElement("link");
        ss.type = "text/css";
        ss.rel = "stylesheet";
        ss.href = gdn.combinePaths(webRoot, 'plugins/RiceBalls/riceballs.css');
        if (document.all)
           FrameDocument.createStyleSheet(ss.href);
        else
           FrameDocument.getElementsByTagName("head")[0].appendChild(ss);
      }

      return false;
    });
  });
  
});
