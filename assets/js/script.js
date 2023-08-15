;(function($){
    $(document).ready(function(){
        
        //event for form sbumti button
        $('#epc-submit-button').on('click', function() {

            let postTitle   = $('#epc-post-title').val();
            let postContent = $('#epc-content').val();
            let authorName  = $('#epc-author-name').val();
            let authorEmail = $('#epc-author-email').val();
            let nonceValue  = $('#_wpnonce').val();

            //Post request 
            $.post(urls.ajaxUrl, {
                action: "EPC_ajax_call",
                postTitle: postTitle,
                postContent: postContent,
                authorName: authorName,
                authorEmail: authorEmail,
                nonceS: nonceValue,

            }, function(data){
                if(data){
                    $('#show-massage').text(data);

                    //clear form
                    $('#epc-post-title').val('');
                    $('#epc-content').val('');
                    $('#epc-author-name').val('');
                    $('#epc-author-email').val('');
                }
            });


            
            return false;
        });

    });
})(jQuery);