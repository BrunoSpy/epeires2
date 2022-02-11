window.open = function (open) {

    return function (url, name, features) {
        let options = {
            PDFJS_URL: "../components/pdfjs/web/viewer.html"
        };
        //intercept pdf
        return PDFObject.embed(url, '#pdfviewer', options);
    };
}(window.open);
