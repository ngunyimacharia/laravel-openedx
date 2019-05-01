$('.logout-edx').click(function (e) {
    e.preventDefault();

    // Get lms logout url
    const edx_logout = $(this).data("logout-url");

    // create and append div
    const div = document.createElement("div");
    div.style.display = "none";
    document.body.appendChild(div);

    //create, load and unload iframe
    const ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", edx_logout);
    ifrm.setAttribute("sandbox", "");
    ifrm.style.width = "0px";
    ifrm.style.height = "0px";
    div.appendChild(ifrm);
    ifrm.onload = function () {
        div.outerHTML = "";
        delete div;
        $(location).attr('href', edx_logout);
    };
});