window.addEventListener("load", () => {
    let cancelForm = document.querySelector(".btn-cancel");
    let addReplyBtn = document.getElementById("addreply");
    let replyform = document.getElementById("replyeditor");
    console.log(cancelForm);
    $("#reply-tab").on("click", function (){
        $("#replies").toggle();
        if ($("#replies").is(":visible")){
            $("#reply-tab span").html("&#x25B2;");

        }
        else {
            $("#reply-tab span").html("&#x25BC;");
            $("#replyeditor")[0].style.display = "none";
        }
        
    });

    $("#addreply").on("click", (e) => {
        e.stopPropagation();
        $("#replies").show();
        $("#reply-tab span").html("&#x25B2;");
        $("#replyeditor")[0].style.display = "flex";
    });
    cancelForm.addEventListener("click", () => {
        replyform.style.display = "none";

    })
});