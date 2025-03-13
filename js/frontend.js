(function($) {

    $(document).ready( function() {
        let file_frame;
        
        $( '#product_media' ).on( 'click', function( event ) {
            event.preventDefault();
            const $parent = $(this).parent();
            const mediaIdsInput = $parent.find('input[name="media_ids"]');
    
            if ( file_frame ) {
                file_frame.open();
                return;
            } 
    
            file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'uploader_title' ),
                button: {
                    text: $( this ).data( 'uploader_button_text' ),
                },
                multiple: true,
                library: {
                    type: 'image' // Restrict to images only
                },
            });
    
            file_frame.on( 'select', function() {
                const attachment = file_frame.state().get('selection').toArray();
                const newIds = [];

                $parent.find('img').remove();
                
                attachment.forEach((item) => {
                    const {id, url} = item.toJSON();
                    newIds.push(id);
                    const newImage = document.createElement('img');
                    newImage.src = url;
                    $parent.append(newImage);
                });

                mediaIdsInput.val( newIds.join(',') );
            });
    
            file_frame.open();
        });
    });
    
    })(jQuery);