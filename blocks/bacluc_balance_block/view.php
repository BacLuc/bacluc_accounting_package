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
            <div class="col-xs-12 col-md-6 balancerow debitors">
                <div class="row">
                    <div class="col-xs-12">

                        <h3 class="balance-title"><?php echo t("Debit")?></h3>
                    </div>

                </div>
                <?php

                $total = 0;
                if(count($debitors)>0){
                    foreach($debitors as $name => $amount){
                        $total += $amount;
                        $amount = number_format($amount,2);
                        print(" 
                        <div class='row'>
                            <div class='col-xs-6 '>
                                <span class='accountname'>$name</span>
                            </div>
                            <div class='col-xs-6 '>
                                <span class='accountvalue number'>$amount</span>
                            </div>
                            
                        </div>
                        ");


                    }
                }

                ?>

                <div class="row total-row">
                    <div class='col-xs-6 total-name-cell'>
                        <span class='total total-name'><?php echo t("Total debit") ?></span>
                    </div>
                    <div class='col-xs-6 total-amount-cell'>
                        <span class='total total-amount number'><?php echo number_format($total,2); ?></span>
                    </div>


                </div>


            </div>




            <div class="col-xs-12 col-md-6 balancerow creditors">
                <div class="row">
                    <div class="col-xs-12">

                        <h3 class="balance-title"><?php echo t("Credit")?></h3>
                    </div>

                </div>
                <?php

                $total = 0;
                if(count($creditors)>0){
                    foreach($creditors as $name => $amount){
                        $total += $amount;
                        $amount = number_format($amount,2);
                        print(" 
                        <div class='row'>
                            <div class='col-xs-6 '>
                                <span class='accountname'>$name</span>
                            </div>
                            <div class='col-xs-6 '>
                                <span class='accountvalue number'>$amount</span>
                            </div>
                            
                        </div>
                        ");

                    }
                }

                ?>

                <div class="row total-row">
                    <div class='col-xs-6 total-name-cell'>
                        <span class='total total-name'><?php echo t("Total credit") ?></span>
                    </div>
                    <div class='col-xs-6 total-amount-cell'>
                        <span class='total total-amount number'><?php echo number_format($total,2); ?></span>
                    </div>


                </div>


            </div>

        </div>



    </div>

</div>