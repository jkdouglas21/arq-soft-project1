$( document ).ready(function() {

    if (sessionStorage.getItem('item_registered_success') != null) {
        $(".main_dialog").html(sessionStorage.getItem('item_registered_success')).fadeIn().show();    
        sessionStorage.removeItem('item_registered_success');
    }

    $('.nav .nav-item a').on("click", function (e) {
        e.preventDefault();

        if (history.state == null) {
            history.pushState({id: "index"}, "Home", "http://localhost/arq-sis-projeto01/");
        }

        var $this = $(this),
        href = $this.attr("href");        
        load_page(href);

    });

    window.onpopstate = function(e){
        if(e.state){
            load_page(document.location.href);
        }
    };

});

function load_page(page) {
    $.ajax({
        url: page,
        data: [],
        dataType: 'html',
        async: true
      }).done(function (resp) {
            //handler url
            location.href = "#" + page;
            var pag = page.split('=');
            var stateObj = { id: pag[1] }; 
            history.replaceState(stateObj, "Pilot | " + pag[1], page);
            //load
            $("body").html("");
            $("body").html(resp);
      }).fail(function (resp) {
          console.log("Erro no carregamento da página");
    });
}
