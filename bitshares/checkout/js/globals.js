
    var globalNeedScan = true;
    var globalPaid = false;
    var globalPaymentTimer = null;
    var globalScanInProgress = false;
    var globalRedirectCountdownTimer = null;
    var globalAmountReceived = 0;
    var globalTotal = 0;
    var globalAsset = "";
    var PaymentScanEnum = {
        FULLSCAN : 0,
        QUICKSCAN : 1
    }
    
    $.noty.defaults.layout = "topRight";
    $.noty.defaults.theme = "relax";
    $.noty.defaults.timeout = 10000;
    $.noty.defaults.animation.open = "animated flipInX";
    $.noty.defaults.animation.close = "animated flipOutX";
    $.noty.defaults.animation.easing = "swing";
    
    var globalLoadingDialog = new BootstrapDialog({
        title: 'Loading',
        message: 'Please wait a moment...',
        autodestroy: false,
        closable: false,                
        buttons: []    
    });
    globalLoadingDialog.open();
    $( document ).ready(function() {
        globalLoadingDialog.close();    
    });
    var globalRedirectDialog = new BootstrapDialog({
        title: 'Payment Complete',
        message: $('<div></div>').load('template/success.html'),
        autodestroy: false,
        closable: false,  
        closeByBackdrop: false,              
        buttons: [
            {         
            label: 'Cancel',
            cssClass: 'btn-primary',
            action: function(dialogItself){
                if(globalRedirectCountdownTimer)
                {
                    clearInterval(globalRedirectCountdownTimer);
                    globalRedirectCountdownTimer = null;
                } 
           
                dialogItself.close();
            }
        }]    
    });                
    var globalPaymentDialog = new BootstrapDialog({
        title: 'Payment Status',
        message: $('<div></div>').load('template/paymentstatus.html'),
        autodestroy: false,
        closable: true,
        closeByBackdrop: false,       
        onshown: function(dialogRef){
            if(($("#paymentTotalReceived").text()).length <= 0)
            {
                var amount = parseFloat(globalAmountReceived).toFixed(2);
                $("#paymentTotalReceived").text(amount+ " "+ globalAsset);
                $('#pay').click(function() {
                    btsPayClick();
                }); 
                $('#scan').click(function() {
                    btsScanClick();
                }); 
            }
            if(($("#paymentBalance").text()).length <= 0)
            {
                var total = parseFloat(globalTotal).toFixed(2);
                $("#paymentBalance").text(total + " " + globalAsset);
            }
            if(globalNeedScan)
            {
       
                btsStartPaymentTracker($('#btsForm').serialize(), PaymentScanEnum.QUICKSCAN);
            }                   
        },        
        buttons: [
            {
            cssClass: 'btn-info',
            label: 'Export CSV',
            icon: 'fa fa-file-excel-o ',
            action: function(dialogItself){
                btsExportPaymentTableToCSV();
            }},
            {         
            label: 'Close',
            cssClass: 'btn-primary',
            action: function(dialogItself){
                dialogItself.close();
            }
        }]    
    }); 

       
    




    


