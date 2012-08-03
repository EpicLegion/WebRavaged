function rconRefresh()
{
    $.getJSON(BASE_URL+'index.php/dashboard/ajaxindex/?'+Math.random(), function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#rightblock').html(data.content);
        }
        else
        {
            $('#rightblock').prepend('<div class="message error">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
    });
}

function rconApplySorting()
{
    $.post('index.php/dashboard/maps_rotation', $('#maplist').sortable('serialize'), function(data){
    	if(data != 'Done')
		{
    		alert(data);
		}
    });
}

function rconPlayerDetails(guid)
{
    $.getJSON(BASE_URL+'index.php/dashboard/logs/'+guid, function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#detail-ip-addresses').html(data.ip);
            $('#detail-names').html(data.names);
            $('.detail-window').css('top', (100+$(window).scrollTop()));
            $('.detail-window').show('slow');
        }
        else
        {
            $('#rightblock').prepend('<div class="message">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
    });
}

function rconMessage()
{
    $('#msg-submit').attr('disabled', 'disabled');
    
    $.post(BASE_URL+'index.php/dashboard/message', { message: $('#msg-message').val(), target: $('#msg-target').val() }, function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#rightblock').prepend('<div class="message">'+BLACKOPS_LANGUAGE_VARS['msg_sent']+'.</div>');
        }
        else
        {
            $('#rightblock').prepend('<div class="message">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
        
        $('#msg-submit').attr('disabled', '');
    }, 'json');
}

function rconKick(id)
{
    $.post(BASE_URL+'index.php/dashboard/kick/'+id, { reason: $('#kick-reason').val() }, function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#rightblock').prepend('<div class="message">'+BLACKOPS_LANGUAGE_VARS['kicked']+'.</div>');
        }
        else
        {
            $('#rightblock').prepend('<div class="message">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
    }, 'json');
}

function rconBan(id)
{
    $.post(BASE_URL+'index.php/dashboard/ban/'+id, { reason: $('#kick-reason').val() }, function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#rightblock').prepend('<div class="message">'+BLACKOPS_LANGUAGE_VARS['banned']+'.</div>');
        }
        else
        {
            $('#rightblock').prepend('<div class="message">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
    }, 'json');
}

function rconTempBan(id)
{
    $.post(BASE_URL+'index.php/dashboard/tempban/'+id, { reason: $('#kick-reason').val() }, function(data) {
        // Error?
        if(data.error == 'None')
        {
            $('#rightblock').prepend('<div class="message">'+BLACKOPS_LANGUAGE_VARS['banned']+'.</div>');
        }
        else
        {
            $('#rightblock').prepend('<div class="message">'+data.error+'. '+BLACKOPS_LANGUAGE_VARS['try_again']+'.</div>');
        }
    }, 'json');
}

function rconPermFields(dropdown)
{
    // Value
    dropdown = $(dropdown).val();
    
    // Empty?
    if(dropdown == '0')
    {
        return;
    }
    
    // Hide
    $('#permission-fields').hide('slow');
    
    // AJAX
    $.get(BASE_URL+'index.php/servers/permissions_fields/'+dropdown, function(data) {
        // Set
        $('#permission-fields').html(data);
        
        // Show
        $('#permission-fields').show('slow');
    });
}

function rconPermFieldsGame(dropdown)
{
    // Value
    dropdown = $(dropdown).val();
    
    // Empty?
    if(dropdown == '0')
    {
        return;
    }
    
    // Hide
    $('#permission-fields').hide('slow');
    
    // AJAX
    $.get(BASE_URL+'index.php/servers/permissions_fields_game/'+dropdown, function(data) {
        // Set
        $('#permission-fields').html(data);
        
        // Show
        $('#permission-fields').show('slow');
    });
}

function rconTemplates(dropdown)
{
    // Value
    dropdown = $(dropdown).val();
    
    // Empty?
    if(dropdown == '0')
    {
        return;
    }
    
    // AJAX
    $.get(BASE_URL+'index.php/servers/templates_ajax/'+dropdown, function(data) {
        // Set
    	$('#templates-container').html(data);
    });
}

$(document).ready(function(){
    $('.leftmenu li').click(function(){
        var anchor = $(this).find('a');
        
        if(anchor && anchor != 'undefined')
        {
            var href = $(anchor).attr('href');
            
            if(href && href != 'undefined')
            {
                window.location.href = href;
            }
        }
    });
});