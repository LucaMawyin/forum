window.addEventListener("load", () => {
    
    $("#reply-tab").on("click", function (){
        $("#replies").toggle();
        if ($("#replies").is(":visible")){
            $("#reply-tab span").html("&#x25B2;");
            
        }
        else {
            $("#reply-tab span").html("&#x25BC;");
        }
        
    });

    $("#addreply").on("click", (e) => {
        e.stopPropagation();
        $("#replies").show();
        $("#reply-tab span").html("&#x25B2;");


    });
    

});