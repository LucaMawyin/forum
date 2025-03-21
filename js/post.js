window.addEventListener("load", () => {
    
    $("#reply-tab").on("click", function (){
        $("#replies").toggle();
        if ($("#replies").is(":visible")){
            console.log($("#replies").html);
            $("#reply-tab span").html("&#x25B2;");
            
        }
        else {
            $("#reply-tab span").html("&#x25BC;");
        }
        
    });
    

});