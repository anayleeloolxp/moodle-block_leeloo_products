require(["jquery"], function ($) {
    $(".leeloo_pricut_buy").on("click", function (e) {
        e.preventDefault();
        var modal = $(this).attr("data-target");
        console.log(modal);
        $(modal + " .modal-body").html(
            '<iframe class="leeloo_frame" src="' +
                $(this).attr("href") +
                '"></iframe>'
        );
    });

    $(".leelooProdcutModal").on("hidden.bs.modal", function () {
        location.reload();
    });
});
