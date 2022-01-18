window.open = function (open) {
    return function (url, name, features) {
        //intercept pdf
        return PDFObject.embed(url, '#pdfviewer');
    };
}(window.open);
