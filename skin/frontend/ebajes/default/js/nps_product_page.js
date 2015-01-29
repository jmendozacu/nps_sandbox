jQuery(document).ready(function($){

	//OUTPUT THE PAGE AND DOCUMENT INFORMATION TO THE CONSOLE
	console.log( $( window ) );
	console.log( $(document) );

	/* ================== FIX PRODUCT PAGE CONTENT BOXES TO NORMALIZE HEIGHT AND / OR WIDTH ================== */
	function fixPrdContentBoxDimensions(windowWidth) {
		
		//VERIFY THAT THE WINDOW WIDTH IS NOT A PORTRAIT MOBILE DEVICE
		if( windowWidth > 479 ){

			//COLLECT THE PRODUCT ADD TO CART BOX HEIGHT
			boxHeight = $(".product-options-bottom").height() + parseInt($(".product-options-bottom").css('padding-top')) + parseInt($(".product-options-bottom").css('padding-bottom')) + 2;

			//MAKE SURE THAT IT IS LARGER THAN THE CURRENT SIZE OF THE OPTIONS WRAPPER
			if( boxHeight > $("#product-options-wrapper").outerHeight() ){
				$("#product-options-wrapper").css({
					"height":boxHeight+"px",
				});	
			}else{ //IF THE ADD TO CART CONTAINER IS NOT LARGER THAN TGE PRODUCT OPTIONS
				$("#product-options-wrapper").css({
					"min-height":boxHeight+"px",
					"height":"auto",
				});	
			} 
			
			//EXTEND THE "MORE VIEWS" CONTAINER TO THE END OF THE PAGE
			//$("#product-more-views > ul").css("width",windowWidth-100);
		}
	}

	//GET PAGE WIDTH AND HEIGHT FOR USE
	var wWidth = $( window ).width();
	var wHeight = $( window ).height();

	//CONTENT BOX DIMENSION FIX AND NORMALIZE HEIGHT
	fixPrdContentBoxDimensions(wWidth);

	//CHANGE PRODUCT QUANTITY TO 1 IF CURRENT QUANTITY IS 0
	if( $("#qty").val() == 0 ){
		$("#qty").val("1");
	}

	//ON CHANGE OF INVENTORY CONTROLLER OPTION
	$(".inventory-controller select").change(function(){
		var str = $(this).find(":selected").text();
		var current = $("#prd-page-availability").html();
		
		if( str.search("OUT OF STOCK") > 0 ){
			//$(".product-image.product-image-zoom").css("box-shadow","0px 0px 40px red");
			if( $(".product-image.product-image-zoom").hasClass("scream-in-stock") ){
				$(".product-image.product-image-zoom").removeClass("scream-in-stock");
			}
			if( !$(".product-image.product-image-zoom").hasClass("scream-out-of-stock") ){
				$(".product-image.product-image-zoom").addClass("scream-out-of-stock");
			}
			if( current.toLowerCase() == "in stock"){
				$("#prd-page-availability").parent("p.availability").removeClass("in-stock");
				$("#prd-page-availability").parent("p.availability").addClass("out-of-stock");
				$("#prd-page-availability").empty();
				$("#prd-page-availability").append("OUT OF STOCK");
			}
		} else {
			if( $(".product-image.product-image-zoom").hasClass("scream-out-of-stock") ){
				$(".product-image.product-image-zoom").removeClass("scream-out-of-stock");
			}
			if( !$(".product-image.product-image-zoom").hasClass("scream-in-stock") ){
				$(".product-image.product-image-zoom").addClass("scream-in-stock");
			}
			if( current.toLowerCase() == "out of stock"){
				$("#prd-page-availability").parent("p.availability").addClass("in-stock");
				$("#prd-page-availability").parent("p.availability").removeClass("out-of-stock");
				$("#prd-page-availability").empty();
				$("#prd-page-availability").append("IN STOCK");
			}
		}
	});

	//ON A WINDOW RESOLUTION CHANGE
	$(window).resize(function(){

		//RECOLLECT THE WINDOW DIMENSIONS
		var wWidth = $( window ).width();
		var wHeight = $( window ).height();
		
		//RE-NORMALIZE PRODUCT PAGE CONTENT BLOCKS
		fixPrdContentBoxDimensions(wWidth);
	});
});

