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
        if (!($("#replies")[0].firstChild.id == "replyeditor")) {
            ($("#replies")[0]).prepend($("#replyeditor")[0]);
        }
        
        $("#replyeditor")[0].style.display = "flex";
    });
    cancelForm.addEventListener("click", () => {
        replyform.style.display = "none";

    })
    let replyToButtons = document.querySelectorAll('input[type="button"][value="Reply"]');
    replyToButtons.forEach(button => {
        button.addEventListener("click", function() {
            console.log("test");
            let reply = (button.parentElement).parentElement;
            reply.after(replyform);
            replyform.style.display = "flex";
        });
    });

});