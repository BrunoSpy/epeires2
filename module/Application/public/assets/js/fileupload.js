var fileupload = function(url) {

    var maxSize = 1048576*8; //fallback to 8Mo

    //ajout formulaire fichier
    $(document).on('click', '#addfile', function(){
        $("#file-upload-form").load(url+'file/form');
        $.getJSON(url+'file/getmaxsize', function(data){
            maxSize = data;
        });
    });

    /**
     * Error Message functions
     */
    function hideErrors() {
        $('#file-controls').removeClass('error');
        $('#file-errors').hide();
    }

    function showErrors(message) {
        $('#file-controls').addClass('error');
        $('#file-errors').show().html(message);
    }

    /**
     * Progress Bar functions
     */
    var progressInterval;
    var jqxhrequest;


    function showProgress(amount, message) {
        $('#progress').show();
        $('#progress .progress-bar').width(amount + '%');
        $('#progress > p').html(message);
        if (amount < 100) {
            $('#progress .progress')
                    .addClass('active')
                    .addClass('progress-info')
                    .removeClass('progress-success');
        } else {
            $('#progress .progress')
                    .removeClass('active')
                    .removeClass('progress-info')
                    .addClass('progress-success');
            
        }
    }

    function getProgress() {
        $.getJSON(url + 'file/sessionprogress?id=' + $('#progress_key').val(), function(data) {
            //console.log(data);
            if (data.status && !data.status.done) {
                var value = Math.floor((data.status.current / data.status.total) * 100);
                showProgress(value, 'Uploading...');
            } else {
                showProgress(100, 'Complete!');
                clearInterval(progressInterval);
            }
        });
    }

    function startProgress() {
        showProgress(0, 'Starting upload...');
        progressInterval = setInterval(getProgress, 90);
    }

    $(document).on('change', 'input[name=file]', function(event) {
        if ($.trim($('#file-upload-form input[name=name]').val()).length == 0) {
            $('#file-upload-form input[name=name]').val($(this).val());
        }
        //disable url
        $("#file-upload-form input[name=url]").prop('disabled', true);
        
        //check fileSize
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            //get the file size and file type from file input field
            var fsize = $('#file')[0].files[0].size;

            if (fsize > maxSize) //Max  = PHP Limit
            {
                var strSize = Math.trunc(maxSize / 1048576);
                showErrors('La taille du fichier dépasse la limite autorisée ('+strSize+'Mo).');
                $("#action-buttons button.btn").addClass('disabled').attr('disabled', 'disabled');
            } else {
                hideErrors();
                $("#action-buttons button.btn").removeClass('disabled').removeAttr('disabled');
            }
        }
        
    });

    $(document).on('change', '#file-upload-form input[name=url]', function(){
        $('#file-upload-form input[name=name]').val($(this).val().replace(/^.*[\\\/]/, ''));
    });

    $(document).on('click', '#cancel-form-upload', function(){
        if(typeof(jqxhrequest) != 'undefined') {
            jqxhrequest.abort();
        }
    });

    $(document).on('submit', '#file-form', function(e) {
        e.preventDefault();

        hideErrors();

        if ($('#file').val() == '' && $.trim($('input[name=url]').val()).length == 0) {
            showErrors('No file(s) selected');
            return;
        }

        //$.fn.ajaxSubmit.debug = true;
        $(this).ajaxSubmit({
            target: '#output',
            beforeSend: function(jqXHR, settings) {
                $('#output').empty();
                showProgress(0, 'Téléchargement...');
                jqxhrequest = jqXHR;
            },
            uploadProgress: function(event, position, total, percentComplete) {
                showProgress(percentComplete, (percentComplete < 100 ? 'Téléchargement...' : 'Envoi terminé !'));
            },
            success: function(response, statusText, xhr, $form) {
                if (response.status) {
                    if (response.formMessages['error']) {
                        //something went wrong
                        //do nothing
                    } else {
                        //everything ok
                        //close modal and add file to event
                        $('#add-file').modal('hide');
                        formAddFile(response.fileId, response.formData);
                    }
                    displayMessages(response.formMessages);
                } else {
                    displayMessages(response.formMessages);
                }
            },
            error: function(a, b, c) {
                console.log(a, b, c);
            }
        });
 //       startProgress();
    });

}
