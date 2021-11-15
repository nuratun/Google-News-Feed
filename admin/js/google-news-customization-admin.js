(function( $ ) {
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 $( window ).load( function() {
		 // To select all the news rows
		 $( '.gn-row-checkbox-all' ).click( function() {
	     var checked = $( this ).prop( 'checked' );
	     $( '.google-news-row-checkbox' ).find( 'input:checkbox' ).prop( 'checked', checked );
	   });

		 // Where the function resides
		 var google_news_url = "/wp-admin/admin-ajax.php";

		 // Check which rows are checked and send them all for deletion
		 $( '.publishAllNewsButton' ).on( 'click', function( e ) {
			 e.preventDefault();
			 // To hold all the row id's
			 var row_ids = [];

			 // Check which rows are checked
			 $( 'input[type=checkbox]:checked' ).each( function( index ) {
				 // Add the row id's that are checked into an array
				 row_ids.push( this.id );
			 });

			 $.ajax({
				 url: ajaxcall.ajax_url,
				 type: 'post',
				 //dataType: 'JSON',
				 data: {
					 'action': 'publish_news_row',
					 'google_news_id': row_ids
				 },
				 success:function( data ) {
					 setTimeout( function() {
						 location.reload();
					 }, 2000);
				 },
				 error: function( err ) {
					 console.log( err );
				 }
			 });
		 });

		 // Check which rows are checked and send them all for deletion
		 $( '.deleteAllNewsButton' ).on( 'click', function( e ) {
			 e.preventDefault();
			 // To hold all the row id's
			 var row_ids = [];

			 // Check which rows are checked
			 $( 'input[type=checkbox]:checked' ).each( function( index ) {
				 // Add the row id's that are checked into an array
				 row_ids.push( this.id );
			 });

			 $.ajax({
				 url: ajaxcall.ajax_url,
				 type: 'post',
				 //dataType: 'JSON',
				 data: {
					 'action': 'delete_news_row',
					 'google_news_id': row_ids
				 },
				 success:function( data ) {
					 setTimeout( function() {
						 location.reload();
					 }, 2000);
				 },
				 error: function( err ) {
					 console.log( err );
				 }
			 });
		 });

		 // Publish an individual news row
		 $( ".publishNewsButton" ).on( 'click', function( e ) {
			 e.preventDefault();
			 // Pass the button id to the function
			 var button_id = this.id;

			 $.ajax({
				 url: ajaxcall.ajax_url,
				 type: 'post',
				 //dataType: 'JSON',
				 data: {
					 'action': 'publish_news_row',
					 'google_news_id' : button_id
				 },
				 success:function( data ) {
					 setTimeout( function() {
						 location.reload();
					 }, 2000);
				 },
				 error: function( err ) {
					 console.log( err );
				 }
			 });
		 } );

			 // Delete an individual news row
			 $( ".deleteNewsButton" ).on( 'click', function( e ) {
				 e.preventDefault();
				 // Pass the button id to the function
				 var button_id = this.id;

				 $.ajax({
					 url: ajaxcall.ajax_url,
					 type: 'POST',
					 // dataType: 'JSON',
					 data: {
						 'action': 'delete_news_row',
						 'google_news_id': button_id
					 },
					 success: function( data ) {
						 setTimeout( function() {
							 location.reload();
						 }, 2000 );
					 },
					 error: function( err ) {
						 console.log( err );
					 }
				 });
			 }
		 );
	 });
})( jQuery );
