     
    function btsShowSuccess()
    {
        globalRedirectDialog.open();  
        var countdown = 10;
        if(globalRedirectCountdownTimer)
            clearInterval(globalRedirectCountdownTimer);
        globalRedirectCountdownTimer = setInterval(function() {
            countdown--;
            $('#redirectCountdown').text("You will be automatically redirected back to the merchant site within " + countdown + " seconds...");
            if(countdown <= 0)
            { 
                clearInterval(globalRedirectCountdownTimer);       
                ajaxSuccess("integration/bitsharescheckout_success.php", $('#btsForm').serialize());
            }
            
        }, 1000); 
    }   
    function btsShowPaymentComplete()
    {
        globalPaid = true;
        btsShowSuccess();
    }
   
    function btsExportPaymentTableToCSV() {
        window.location.href = '../exportToCSV.php?memo='+$('#memo').val();
    }    
    var btsShowPaymentStatus = function()
    {
        globalPaymentDialog.options.title = 'Payment Status - ' + $("#memo").val();
        globalPaymentDialog.open();
        
    }
    
    var btsUpdateOnChange = function()
    {
        if(globalScanInProgress)
        {
            BootstrapDialog.warning('You have cancelled the current payment scan!');            
        }    
        btsUpdateUIScanClear();    
    }    

    $("input[type='text'], input[type='number']" ).change(function(e) {
      btsUpdateOnChange();
    });     
        
    var btsPayClick = function() {
                
        if(globalPaid)
        {
            BootstrapDialog.danger('This order has already been paid for!');
        }    
        else
        {
            ajaxPay($('#btsForm').serialize());
        }    

    }  
      
    var btsScanClick = function () {
        if(globalScanInProgress)
        {
            if(globalPaymentTimer)
                clearInterval(globalPaymentTimer);
            btsUpdateUIScanCancelled();            
        }
        else
        {
            
            btsStartPaymentTracker($('#btsForm').serialize(), PaymentScanEnum.FULLSCAN);
        }
    }
    	
    $('#btsForm').submit(function(e) {
        if (e.preventDefault) { e.preventDefault(); } else { e.returnValue = false; } 
             
        ajaxLookup($('#btsForm').serialize());

    });	    
    $('#return').click(function (e) { 
        if (e.preventDefault) { e.preventDefault(); } else { e.returnValue = false; }       
      
        if(globalPaid)
        {
            ajaxSuccess("integration/bitsharescheckout_success.php", $('#btsForm').serialize());
        }
        else
        {
            BootstrapDialog.confirm('This will cancel your order. Are you sure?', function(result){
                if(result) {
                    ajaxCancel("integration/bitsharescheckout_cancel.php", $('#btsForm').serialize());
                }else {
                    
                }
            });            
        }
          
		
	});        
    function btsStartPaymentTracker(serializedData, scanMode)
    {
        if(globalScanInProgress)
            return;
        if(scanMode == PaymentScanEnum.QUICKSCAN)
        {
            var progressToUpdate = 100;
            ajaxScanChain(serializedData, progressToUpdate, scanMode);
            
        }
        else if(scanMode == PaymentScanEnum.FULLSCAN)
        {
            var count = 0;
            btsUpdateUIFullScan();
                
            if(globalPaymentTimer)
                clearInterval(globalPaymentTimer);
            globalPaymentTimer = setInterval(function() {
                count++;
                var progressToUpdate = 20+parseInt(80 * parseFloat(count / 18));
                ajaxScanChain(serializedData, progressToUpdate);
                // run for about 3 minutes
                if(count >= 18)
                {
                    clearInterval(globalPaymentTimer);
                    btsUpdateUIScanComplete();
                }    
            }, 10000); 
        }                   
    }



    


