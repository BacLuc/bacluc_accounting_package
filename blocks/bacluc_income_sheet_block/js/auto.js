(function($){

    function adaptTotalRow(){
        if($(".bacluc-income-sheet-block .creditors").offset().top ==$(".bacluc-income-sheet-block .debitors").offset().top){

            var tochange = $(".bacluc-income-sheet-block .creditors");
            if(tochange.height()>$('.bacluc-income-sheet-block .debitors').height()){
                //fix height of creditors
                tochange.height(tochange.height());

                //now change the tochange
                tochange = $('.bacluc-income-sheet-block .creditors');
            }else{
                //fix debitors height
                $('.bacluc-income-sheet-block .debitors').height($('.bacluc-income-sheet-block .debitors').height());
                tochange.height($('.bacluc-income-sheet-block .debitors').height());
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
            $('.bacluc-income-sheet-block .creditors,.bacluc-income-sheet-block .debitors')
                .removeAttr("style")
                .find(".total-row").removeClass("absolute-total");
        }
    }
    $(adaptTotalRow);
    $(window).resize(adaptTotalRow);

})(jQuery)