<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login, registration forms">
    <meta name="author" content="Seong Lee">

    <title>Bitshares Checkout</title>

    <!-- Stylesheets -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/bootstrap-dialog.min.css" rel="stylesheet">
		<link href="css/animate.css" rel="stylesheet">
	
		<link href="css/bitsharescheckout.css" rel="stylesheet">
	
		<!-- Font Awesome CDN -->
		<link href="css/font-awesome.min.css" rel="stylesheet">
		
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
<section id="signin_main" class="bitshares signin-main">
	<div class="section-content">
	  <div class="wrap">
		  <div class="container">	  
				<div class="form-wrap">
					<div class="row">
					  <div class="title" data-animation="fadeInDown" data-animation-delay=".8s">
						  <div class="bitshareslogo"></div>
						  <h5>Transparent. Secure. Sound.</h5>
					  </div>
						<div id="myForm" data-animation="bounceIn">
							<div class="form-header">
							  <i class="fa fa-robo"></i>
							  <div><a href="bts:opencartdemo"><?php echo $_REQUEST['accountName']; ?></a></div>
						  </div>
						  <form name="btsForm" id="btsForm" action="integration/bitsharescheckout_pay.php">
						  <input name="trx_id" id="trx_id" type="hidden" value=""></input>
						  <div class="form-main">
							  <div class="form-group">
							  	<input type="hidden" id="accountName" name="accountName" value="<?php echo $_REQUEST['accountName']; ?>">
							  	<div class="row">
									<div class="col-xs-6">
										<h5>Amount</h5>
										<input type="number" step="any" min="0" id="total" name="total" class="form-control" required="required" value="<?php echo $_REQUEST['total']; ?>">
									</div>
									<div class="col-xs-6">
										<h5>Asset</h5>
										<input type="text" id="asset" name="asset" class="form-control" required="required" value="<?php echo $_REQUEST['asset']; ?>">
									</div>
								</div>
							  	<div class="row">
									<div class="col-xs-6">
										<h5>Order ID</h5>
										<input type="number" id="order_id" name="order_id" class="form-control" required="required" value="<?php echo $_REQUEST['order_id']; ?>">
									</div>
									<div class="col-xs-6">
										<h5>Memo</h5>
										<input name="hashSalt" id="hashSalt" type="hidden" value="<?php echo $_REQUEST['hashSalt']; ?>"></input>
										<input name="metadata1" id="metadata1" type="hidden" value="<?php echo $_REQUEST['metadata1']; ?>"></input>
                                        <input name="memo" id="memo" type="text" disabled title="You cannot edit the memo, it is auto-generated" class="form-control" required value="<?php echo $_REQUEST['memo']; ?>"></input>
									</div>
								</div>	  			
							    
							  </div>
						    <button id="pay" type="submit" class="btn btn-block signin">Pay</button>
						    </div>
						    </form>
							<div class="form-footer">
								<div class="row">
									<div class="col-xs-7">
									    <form action="" method="POST">
										    <i id="returnIcon" class="fa fa-shopping-cart"></i>
										    <a href="#" id="return" name="return">Cancel and return to checkout</a>
									    </form>
									</div>
									<div class="col-xs-5">
										<i id="paymentStatus" class="fa fa-question"></i>
										<a href="#" name="payment" id="payment">Payment Status</a>
									</div>
								</div>
							</div>		
					  </div>
					</div>
				</div>
		  </div>
	  </div>
	</div>
</section>
	  
    <!-- js library -->
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/jquery.localize.min.js"></script>
		<script type="text/javascript" src="js/jquery.noty.packaged.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/dialog/bootstrap-dialog.min.js"></script>
		<script type="text/javascript" src="js/waypoints.min.js"></script>
		<script type="text/javascript" src="js/md5.js"></script>
		<script type="text/javascript" src="js/bitsharescheckout.js"></script>
		<script type="text/javascript">
			(function($) {
				
				// get full window size
				$(window).on('load resize', function(){
				    var w = $(window).width();
				    var h = $(window).height();

				    $('section').height(h);
				});		
				// set focus on input
				var firstInput = $('section').find('input[type=text], input[type=number], input[type=email]').filter(':visible:first');
        
				if (firstInput != null) {
            firstInput.focus();
        }
				
				$('section').waypoint(function (direction) {
					var target = $(this).find('input[type=text], input[type=number], input[type=email]').filter(':visible:first');
					target.focus();
				}, {
	          offset: 300
	      }).waypoint(function (direction) {
					var target = $(this).find('input[type=text], input[type=number], input[type=email]').filter(':visible:first');
			  	target.focus();
	      }, {
	          offset: -400
	      });
				
				
				// animation handler
				$('[data-animation-delay]').each(function () {
	          var animationDelay = $(this).data("animation-delay");
	          $(this).css({
	              "-webkit-animation-delay": animationDelay,
	              "-moz-animation-delay": animationDelay,
	              "-o-animation-delay": animationDelay,
	              "-ms-animation-delay": animationDelay,
	              "animation-delay": animationDelay
	          });
	      });
				
	      $('[data-animation]').waypoint(function (direction) {
	          if (direction == "down") {
	              $(this).addClass("animated " + $(this).data("animation"));
	          }
	      }, {
	          offset: '90%'
	      }).waypoint(function (direction) {
	          if (direction == "up") {
	              $(this).removeClass("animated " + $(this).data("animation"));
	          }
	      }, {
	          offset: '100%'
	      });
			
			})(jQuery);
		</script>
  </body>
</html>
