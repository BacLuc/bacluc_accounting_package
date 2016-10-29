(function($){

    function adaptTotalRow(){
        if($(".bacluc-balance-block .creditors").offset().top ==$(".bacluc-balance-block .debitors").offset().top){

            var tochange = $(".bacluc-balance-block .creditors");
            if(tochange.height()>$('.bacluc-balance-block .debitors').height()){
                //fix height of creditors
                tochange.height(tochange.height());

                //now change the tochange
                tochange = $('.bacluc-balance-block .creditors');
            }else{
                //fix debitors height
                $('.bacluc-balance-block .debitors').height($('.bacluc-balance-block .debitors').height());
                tochange.height($('.bacluc-balance-block .debitors').height());
            }
            tochange.find(".total-row").addClass("absolute-total");

            /*
             .creditors .total-row {
             bottom: 0;
             position: absolute;
             width: 100%;
             }
             */
        }else{
            $('.bacluc-balance-block .creditors,.bacluc-balance-block .debitors')
                .removeAttr("style")
                .find(".total-row").removeClass("absolute-total");
        }
    }
    $(adaptTotalRow);
    $(window).resize(adaptTotalRow);

})(jQuery)