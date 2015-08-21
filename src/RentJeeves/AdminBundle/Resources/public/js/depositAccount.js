$( document ).ready(function() {
    var proxied = window.XMLHttpRequest.prototype.send;
    window.XMLHttpRequest.prototype.send = function() {
        $(".tab-content").showOverlay();
        //Here is where you can add any code to process the request.
        //If you want to pass the Ajax request object, pass the 'pointer' below
        var pointer = this;
        var intervalId = window.setInterval(
            function() {
                if (pointer.readyState != 4) {
                    return;
                }
                $(".tab-content").hideOverlay();
                //Here is where you can add any code to process the response.
                //If you want to pass the Ajax request object, pass the 'pointer' below
                clearInterval(intervalId);
            },
            1
        );//I found a delay of 1 to be sufficient, modify it as you need.

        return proxied.apply(this, [].slice.call(arguments));
    };
});