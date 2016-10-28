(function($){

    function adaptTotalRow(){
        if($(".creditors").offset().y ==$(".debitors").offset().y){

            var tochange = $(".creditors");
            if(tochange.height()>$('.debitors').height()){
                //fix height of creditors
                tochange.height(tochange.height());

                //now change the tochange
                tochange = $('.creditors');
            }else{
                //fix debitors height
                $('.debitors').height($('.debitors').height());
                tochange.height($('.debitors').height());
            }
            tochange.find(".total-row").css({
                position:"absolute",
                width: "100%",
                bottom:0
            });

            /*
             .creditors .total-row {
             bottom: 0;
             position: absolute;
             width: 100%;
             }
             */
        }
    }
    $(adaptTotalRow);
    $(window).resize(adaptTotalRow);

})(jQuery)