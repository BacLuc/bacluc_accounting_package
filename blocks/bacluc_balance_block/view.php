<?php
use Concrete\Package\BasicTablePackage\Src\FieldTypes\DateField;
/**
 * @var \Concrete\Package\BaclucAccountingPackage\Block\BaclucBalanceBlock\Controller $controller
 */
?>

<div class="row bacluc-balance-block" id="<?php echo $htmlid;?>" >
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-6 col-sm-4 col-md-2 ">
                <?php echo t("Balance until Date:");?>

            </div>
            <div class="col-xs-6 col-sm-4 col-md-2 ">
                <?php echo $dateField->getTableView();?>

            </div>
        </div>
        <form action="<?php echo $this->action('set_date') ;?>" method="POST">
        <div class="row">

            <div class="col-xs-6 col-sm-4 col-md-2 ">
                <?php echo $dateField->getLabel();?>

            </div>

            <div class="col-xs-6 col-sm-4 col-md-2 ">
                <?php echo $dateField->getInputHtml($form, true);?>

            </div>
            <div class="col-xs-6 col-sm-4 col-md-2 ">
                <?php echo $form->submit('submit', t("set Year"));?>

            </div>

        </div>
         </form>

        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">

                        <h3 class="balance-title"><?php echo t("Debitors")?></h3>
                    </div>

                </div>
                <?php

                $total = 0;
                if(count($debitors)>0){
                    foreach($debitors as $name => $amount){
                        print(" 
                        <div class='row'>
                            <div class='col-xs-12 col-sm-6 '>
                                <span class='accountname'>$name</span>
                            </div>
                            <div class='col-xs-12 col-sm-6 '>
                                <span class='accountvalue'>$amount</span>
                            </div>
                            
                        </div>
                        ");

                        $total += $amount;
                    }
                }

                ?>

                <div class="row total-row">
                    <div class='col-xs-12 col-sm-6 total-name-cell'>
                        <span class='total total-name'><?php echo t("Total Debitors") ?></span>
                    </div>
                    <div class='col-xs-12 col-sm-6 total-amount-cell'>
                        <span class='total total-amount'><?php echo $total ?></span>
                    </div>


                </div>


            </div>

        </div>



    </div>

</div>