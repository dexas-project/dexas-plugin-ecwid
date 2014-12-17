(function($) {
    var payClicked = false;
    var paid = false;
    var paymentTimer = null;
    $.noty.defaults.layout = "topRight";
    $.noty.defaults.theme = "relax";
    $.noty.defaults.timeout = 10000;
    $.noty.defaults.animation.open = "animated flipInX";
    $.noty.defaults.animation.close = "animated flipOutX";
    $.noty.defaults.animation.easing = "swing";
    function saveAs(uri, filename) {
        var link = document.createElement('a');
        if (typeof link.download === 'string') {
            document.body.appendChild(link); // Firefox requires the link to be in the body
            link.download = filename;
            link.href = uri;
            link.click();
            document.body.removeChild(link); // remove the link when done
        } else {
            location.replace(uri);
        }
    }
    function startPaymentTracker(serialized)
    {
        if(paymentTimer)
            clearInterval(paymentTimer);
        paymentTimer = setInterval(function() {
            $.ajax({
                url: "integration/bitsharescheckout_verifysingleorder.php",
                type: 'post',
                dataType: 'json',
                data: serialized,
                beforeSend:function(){
              
                },
                complete:function(){
              
                },   
                error:function(jqXHR, textStatus, errorThrown){
                    var res = textStatus;
                    if(jqXHR.responseText !== "")
                    {
                        res = jqXHR.responseText;
                    }
                    var n = noty({
                        text: res,
                        type: 'error'
                    });
                    clearInterval(paymentTimer);               
                },                              
                success: function(response, textStatus, jqXHR) {
                    var textresponse = "Payment recieved...";
                     
                    if(response.error)
                    {
                       var n = noty({
                            text: response.error,
                            type: 'error'
                        });                    
                    }
                    else 
                    {
                                  
                    }
                                                      
                }
            });
        }, 10000);                    
    }
    function exportTableToCSV($table, filename) {

        var $rows = $table.find('tr:has(td)'),

            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character

            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',

            // Grab text from table into CSV formatted string
            csv = '"' + $rows.map(function (i, row) {
                var $row = $(row),
                    $cols = $row.find('td');

                return $cols.map(function (j, col) {
                    var $col = $(col),
                        text = $col.text();

                    return text.replace('"', '""'); // escape double quotes

                }).get().join(tmpColDelim);

            }).get().join(tmpRowDelim)
                .split(tmpRowDelim).join(rowDelim)
                .split(tmpColDelim).join(colDelim) + '"',

            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
            saveAs(csvData, filename);
            
        
    }    
    var showPaymentStatus = function()
    {
            BootstrapDialog.show({
                title: 'Payment Status',
                message: $('<div></div>').load('template/paymentstatus.html'),
                autodestroy: false,
                closable: true,
                closeByBackdrop: false,
                buttons: [
                    {
                    cssClass: 'btn-info',
                    label: 'Export CSV',
                    icon: 'fa fa-file-excel-o ',
                    action: function(dialogItself){
                        exportTableToCSV.apply(this, [$('#paymentStatusTable>table'), 'export.csv']);
                    }},
                    {         
                    label: 'Close',
                    cssClass: 'btn-primary',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                }]    
            });    
    }
    var showPaymentFailedStatus = function()
    {
            BootstrapDialog.show({
                title: 'Payment Status',
                message: $('<div></div>').load('template/paymentfailedstatus.html'),
                autodestroy: false,
                closable: true,               
                buttons: [
                    {         
                    label: 'Close',
                    cssClass: 'btn-primary',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                }]    
            });    
    }
    
    var showNoStatus = function()
    {
            BootstrapDialog.show({
                title: 'Payment Status',
                message: $('<div></div>').load('template/nostatus.html'),
                autodestroy: false,
                closable: true,                
                buttons: [
                    {         
                    label: 'Close',
                    cssClass: 'btn-primary',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                }]    
            });    
    }
    var updatePaymentStatusToSuccess = function()
    {
               
    }    
    
    var btsCreateEHASHJS = function(account,orderId, price, asset, salt)
    {
        var mystring = account+orderId+price+asset+salt;
        mystring = md5(mystring);
        return mystring.substring(0, 12);    
    }
    var btsCreateMemoJS = function(ehash)
    {
      return "E-HASH:"+ehash;
    }
    var updateOnChange = function()
    {
        var account = $("#accountName").val();
        var orderid = $("#order_id").val();
        var total = $("#total").val();
        var asset = $("#asset").val();
        var salt = $("#hashSalt").val();
        var hash = btsCreateEHASHJS(account, orderid, total, asset, salt);
        $("#memo").val(btsCreateMemoJS(hash));
    }
    $("input[type='text'], input[type='number']" ).change(function() {
      updateOnChange();
    });
    $('#payment').click(function (e) { 
        e.preventDefault();
        if($('#paymentStatus').hasClass('fa-times'))
        {
            showPaymentFailedStatus();
        }
        else if($('#paymentStatus').hasClass('fa-refresh'))
        {
            showPaymentStatus();
        }
        else if(!payClicked)
        {
            showNoStatus();
        } 
        else if(payClicked && $('#paymentStatus').hasClass('fa-check'))
        {
            showPaymentStatus();
            updatePaymentStatusToSuccess();
        }
    });    
    $('#return').click(function (e) { 
        e.preventDefault(); 
        // temp enable disabled controls so serialize can return disabled data
        var myform = $('#btsForm');

         // Find disabled inputs, and remove the "disabled" attribute
        var disabled = myform.find(':input:disabled').removeAttr('disabled');

         // serialize the form
        var serialized = myform.serialize();
        var myurl = "integration/bitsharescheckout_cancel.php";
        if(paid)
        {
            myurl = "integration/bitsharescheckout_success.php";
        }
         // re-disabled the set of inputs that you previously enabled
        disabled.attr('disabled','disabled');         
        $.ajax({
                url: myurl,
                type: 'post',
                dataType: 'json',
                data: serialized,
                beforeSend:function(){
              
                },
                complete:function(){
              
                },   
                error:function(jqXHR, textStatus, errorThrown){
                    var res = textStatus;
                    if(jqXHR.responseText !== "")
                    {
                        res = jqXHR.responseText;
                    }
                    var n = noty({
                        text: res,
                        type: 'error'
                    });
				    $('#returnIcon').addClass('fail');                                    
                },                              
                success: function(response, textStatus, jqXHR) {
                    var textresponse = "Returning to checkout...";
                     
                  //  $('#returnIcon').removeClass('fail').addClass('success'); 
                    if(response.error && !response.url)
                    {
                       var n = noty({
                            text: response.error,
                            type: 'error'
                        });                    
                    }
                    else if(response.url)
                    {
                       textresponse += "If you are not redirected click <a href='"+response.url+"'>here</a>";
                       var n = noty({
                            text: textresponse,
                            type: 'success',
                        });
                        window.location.href =  response.url;                 
                    }
                                                      
                }
            });   
		
	});	
   //hang on event of form with id=myform
    $("#btsForm").submit(function(e) {

        //prevent Default functionality
        e.preventDefault();
        // temp enable disabled controls so serialize can return disabled data
        var myform = $('#btsForm');

         // Find disabled inputs, and remove the "disabled" attribute
        var disabled = myform.find(':input:disabled').removeAttr('disabled');

         // serialize the form
        var serialized = myform.serialize();

         // re-disabled the set of inputs that you previously enabled
        disabled.attr('disabled','disabled');      
        //get the action-url of the form
        var actionurl = e.currentTarget.action;

        //do your own request an handle the results
         $.ajax({
                url: actionurl,
                type: 'post',
                dataType: 'json',
                data: serialized,
                beforeSend:function(){ 
                   $('#paymentStatus').removeClass('fa-times fa-check fa-question').addClass('fa-refresh fa-spin');  
                },
                complete:function(){
                   //$('#paymentStatus').removeClass('fa-refresh fa-spin'); 
                },   
                error:function(jqXHR, textStatus, errorThrown){
                    var res = textStatus;
                    if(jqXHR.responseText !== "")
                    {
                        res = jqXHR.responseText;
                    }
                    var n = noty({
                        text: res,
                        type: 'error'
                    });
                    $('#myForm .fa-robo').removeClass('success').addClass('fail');
				    $('#myForm').addClass('fail');
				    
				    $('#paymentStatus').removeClass('fa-check fa-question fa-refresh fa-spin').addClass('fa-times');                 
                },                              
                success: function(response, textStatus, jqXHR) {
                    var textresponse = "Payment processing..."
 

                    if(response.error && !response.url)
                    {
                       var n = noty({
                            text: response.error,
                            type: 'error'
                        });                    
                    }
                    else if(response.url)
                    {
                       var n = noty({
                            text: textresponse,
                            type: 'success',
                        });
                        window.location.href =  response.url;                 
                    }
                    //$('#myForm .fa-robo').removeClass('fail').addClass('success');
				    //$('#myForm').removeClass('fail').removeClass('animated');
				    //$('#paymentStatus').removeClass('fa-times').addClass('fa-check'); 
				    payClicked = true;
				    startPaymentTracker(serialized);
				    showPaymentStatus();                                            
                }
            });

    });	


})(jQuery);



    


