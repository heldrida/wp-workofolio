(function($){

	console.log('hwc-works.js');

	$(document).ready(function(){


		$(function(){

			$('.greyBar').delegate('.hwc-work-move', 'click', function(e){

				e.preventDefault();

				var direction = $( this ).attr('class').indexOf('next') > -1 ? 1 : -1;

				var goToEl = parseInt( $( '.hwc-work-item' ).index( $( this ).parents('.hwc-work-item') ) ) + direction;

				var offset = $('.hwc-work-item').eq( goToEl ).offset();

				offset.top = ( offset.top - $('.greyBar').height() ); // offset to view the bar

				$('html, body').animate({scrollTop: offset.top }, 'slow');

			});
			
		});

		$(function(){

			var postForm = $('#post'); 
			postForm.attr('enctype', 'multipart/form-data');
			
			/*
			$('#post-body').delegate('.hwc-wrk-add-more', 'click', function(){
					
					var parent = $(this).parents('.inside');
					var node = $('.hwc-wrk-img-uploader').clone().wrap('<div>').parent().html();
					parent.append( node );

			});
			*/

			var hwc_wrk_img_uploader = null;
			var hwc_work_video_add = false;

			$('#post-body').delegate('.hwc-add-image', 'click', function(e){
					
					e.preventDefault();

					/*
					hwc_wrk_img_uploader = $( this ).parents('.hwc-wrk-img-uploader');
					formfield = hwc_wrk_img_uploader.find('.hwc-add-image').attr('name');
					*/

					tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');

				return false;

			});

			/* s: under development */
			$('#post-body').delegate('.hwc-work-video-add', 'click', function(e){
					
					e.preventDefault();

					/*
					hwc_wrk_img_uploader = $( this ).parents('.hwc-wrk-img-uploader');
					formfield = hwc_wrk_img_uploader.find('.hwc-add-image').attr('name');
					*/

					hwc_work_video_add = $( this );

					tb_show('', 'media-upload.php?type=video&amp;TB_iframe=true');

				return false;

			});
			/* e: under development */		

			window.send_to_editor = function(html) {
				
				imgurl = $('img',html).attr('src');

				if( hwc_work_video_add != false && hwc_work_video_add != undefined && hwc_work_video_add.length > 0 ){

						var href = $( html ).attr('href');

						hwc_work_video_add.val( null );
						hwc_work_video_add.val( href );
						tb_remove();
						hwc_work_video_add = null;

					return;	
				};

				/*
				hwc_wrk_img_uploader.find('.hwc-image-file').val(imgurl);
				hwc_wrk_img_uploader.find('.image').html('<img src="' + imgurl + '" />');
				*/
				var i = parseInt( $('#hwc-works-list li').length ) + 1;
				var node = '<li>' +
								'<div class="hwc-work-remove">remove</div>' +
									'<div class="hwc-work-editor-wrp">' +
										'<div class="title-editor">' +
											'<input type="text" name="hwc-work-images[' + i + '][title]" value="Your image title..." />' +
										'</div>'	+	
										'<div class="url-editor">' +
											'<input type="text" name="hwc-work-images[' + i + '][link]" value="http://yourlinkhere.com" />' +
										'</div>' +
									'</div>' + 						
								'<img src="' + imgurl + '" alt="" />' +
								'<input type="hidden" name="hwc-work-images[' + i + '][image]" class="hwc-image-file" value="' + imgurl + '" />' +
							'</li>';

				var parent = $('#hwc-works-list');
				parent.append( node );

				setTimeout(function(){

					$('.no-img-yet').parents('li').fadeOut('slow');
					
				}, 2500);

				tb_remove();

			};

			$( "#hwc-works-list" ).sortable({
			   update: function(e, el) {
			   		
			   		/*
			   		var index = $(el.item).index() + 1;
			   		var curEl = $(el.item).find('.cpt-o-pos');

			   		// set order val
			   		console.log(index);
			   		curEl.val( index );
			   		console.log( curEl.val() );
			   		*/
			   }
			});
			//$( "#hwc-works-list" ).disableSelection();

			/*
			$('.hwc-work-remove').on('click', function(){
				
				var r = confirm("Are you sure you want to remove this image ?")

				if( r == true ){

					$( this ).parents('li').fadeOut();
					
				};

			});
			*/
			$('#hwc-works-list').delegate('.hwc-work-remove', 'click', function(){
				
				var r = confirm("Are you sure you want to remove this image ?");

				if( r == true ){

					$( this ).parents('li').fadeOut();
					$( this ).parents('li').remove();
				};

			});

			$('.hwc-work-editor-wrp').on('click', function(){
				console.log('Clicked inp!');
			});

		});

	});


})( jQuery );