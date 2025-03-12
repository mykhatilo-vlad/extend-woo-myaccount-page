(function($) {

    $(document).ready( function() {
        let file_frame;
        
        $( '#product_media' ).on( 'click', function( event ) {
            event.preventDefault();
            const $parent = $(this).parent();
            const mediaIdsInput = $parent.find('input[name="media_ids"]');
    
            // if the file_frame has already been created, just reuse it
            if ( file_frame ) {
                file_frame.open();
                return;
            } 

            const selection = new wp.media.model.Selection();
            const selectedids = mediaIdsInput.val();
            if(selectedids) {
                selectedids.split(',').forEach( (id) => {
                    const attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add(attachment);
                });
            }
            
            console.log(selection)
    
            file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'uploader_title' ),
                button: {
                    text: $( this ).data( 'uploader_button_text' ),
                },
                multiple: true,
                library: {
                    type: 'image' // Restrict to images only
                },
                selection,
            });
    
            file_frame.on( 'select', function() {
                const attachment = file_frame.state().get('selection').toArray();
                const newIds = [];
                attachment.forEach((item) => {
                    const {id, url} = item.toJSON();
                    newIds.push(id);
                    const newImage = document.createElement('img');
                    newImage.src = url;
                    $parent.append(newImage);
                });

                mediaIdsInput.val( mediaIdsInput.val() ? [mediaIdsInput.val(), ...newIds].join(',') : newIds.join(',') );
            });
    
            file_frame.open();
        });
    });
    
    })(jQuery);